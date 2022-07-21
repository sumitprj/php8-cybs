define(
    [
        'ko',
        'uiComponent',
        'jquery',
        'underscore',
        'CyberSource_ReCaptcha/js/registry',
        'mage/translate'
    ],
    function (ko,Component, $, _, registry, $t) {
        'use strict';

        return Component.extend(
            {
                defaults: {
                   template: 'CyberSource_ReCaptcha/reCaptcha',
                    reCaptchaId: 'cybersource-recaptcha'
                },
                _isApiRegistered: undefined,
    
                initialize: function () {
                  this._super();
                  this._loadApi();
                },
    
                /**
                  * Loads recaptchaapi API and triggers event, when loaded
                  * @private
                  */
                _loadApi: function () {
                    console.log("-----pub recpatctha cybs _loadAPI------");
                    var element, scriptTag;
    
                    if (this._isApiRegistered !== undefined) {
                        if (this._isApiRegistered === true) {
                            $(window).trigger('recaptchaapiready');
                        }
    
                        return;
                    }
                    this._isApiRegistered = false;
    
                    // global function
                    window.globalOnRecaptchaOnLoadCallback = function() {
                        this._isApiRegistered = true;
                        $(window).trigger('recaptchaapiready');
                    }.bind(this);
    
                    element   = document.createElement('script');
                    scriptTag = document.getElementsByTagName('script')[0];
    
                    element.async = true;
                    element.src = 'https://www.google.com/recaptcha/api.js'
                        + '?onload=globalOnRecaptchaOnLoadCallback&render=explicit';
                       // + (this.settings.lang ? '&hl=' + this.settings.lang : '');
    
                    scriptTag.parentNode.insertBefore(element, scriptTag);
    
                },
    
                /**
                 * Return true if reCaptcha is visible
                 * @returns {Boolean}
                 */
                getIsVisible: function () {
                    return this.settings.enabled[this.zone];
                },
    
                /**
                 * Checking that reCaptcha is invisible type
                 * @returns {Boolean}
                 */
                getIsInvisibleRecaptcha: function () {
                    return this.settings.size === 'invisible';
                },
    
                /**
                 * Recaptcha callback
                 * @param {String} token
                 */
                reCaptchaCallback: function (token) {
                    if (this.settings.size === 'invisible') {
                        this.tokenField.value = token;
                        this.$parentForm.submit();
                    }
                },

                initCaptcha: function () {
                    var self = this,
                        $wrapper = $('#' + this.getReCaptchaId() + '-wrapper'),
                        widgetId;

                    if (this.captchaInitialized) {
                        return;
                    }

                    this.captchaInitialized = true;

                    this.$parentForm = $wrapper.parents('form');
                    $wrapper.find('.g-recaptcha').attr('id', this.getReCaptchaId());

                    widgetId = grecaptcha.render(this.getReCaptchaId(), {
                        'sitekey': this.settings.siteKey,
                        'callback': function (token) {
                            self.recaptchaEndCallBack(token);
                            self.validateReCaptcha(true);
                        },
                        'expired-callback': function () {
                            self.validateReCaptcha(false);
                        },
                        'error-callback': function () {
                            alert($t('An error occurred while initializing reCAPTCHA. Please verify your API keys and domain whitelisting settings.'));
                        },
                        'size': this.settings.size,
                        'badge': this.badge ? this.badge : this.settings.badge
                    });

                    this.$parentForm.on('cybersource:startRecaptcha', this.executeStartCallback.bind(this, widgetId));

                    this.tokenField = $('<input type="hidden" name="token" style="display: none" />')[0];
                    this.$parentForm.append(this.tokenField);

                    this.updateRegistry(this.getReCaptchaId(), widgetId, this.tokenField);
                },
                recaptchaEndCallBack: function (token) {
                    this.tokenField.value = token;
                    _.defer(function () {
                        this.$parentForm.trigger('cybersource:endRecaptcha')
                    }.bind(this));
                },
                executeStartCallback: function (widgetId, event) {
                    if (!this.tokenField.value && this.settings.size === 'invisible') {
                        grecaptcha.execute(widgetId);
                        event.preventDefault(event);
                        event.stopImmediatePropagation();
                        return;
                    }
                    _.defer(function () {
                        this.$parentForm.trigger('cybersource:endRecaptcha');
                    }.bind(this));
                },
            
            validateReCaptcha: function(state){
                if (this.settings.size !== 'invisible') {
                    return $(document).find('input[type=checkbox].required-captcha').prop( "checked", state );
                }
            },

            /**
             * Render reCaptcha
             */
            renderReCaptcha: function () {
                var me = this;

                if (this.getIsVisible()) {
                    if (window.grecaptcha && window.grecaptcha.render) { // Check if recaptcha is already loaded
                        me.initCaptcha();
                    } else { // Wait for recaptcha to be loaded
                        $(window).on('recaptchaapiready', function () {
                            me.initCaptcha();
                        });
                    }
                }
            },

            /**
             * Get reCaptcha ID
             * @returns {String}
             */
            getReCaptchaId: function () {
                return this.reCaptchaId;
            },
                updateRegistry: function(id, widgetId, field) {
                    console.log('id ----->' + id);
                    console.log('widgetId ----->' + widgetId);
                    console.log('field ----->' + field);
                    registry.ids.push(id);
                    registry.captchaList.push(widgetId);
                    registry.tokenFields.push(field);
                }
            }
        );
    }
);
