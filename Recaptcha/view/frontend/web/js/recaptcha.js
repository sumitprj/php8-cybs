define(
    [
        'MSP_ReCaptcha/js/reCaptcha',
        'jquery',
        'underscore',
        'MSP_ReCaptcha/js/registry',
        'mage/translate'
    ],
    function (Component, $, _, registry, $t) {
        'use strict';

        return Component.extend(
            {
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
                updateRegistry: function(id, widgetId, field) {
                    registry.ids.push(id);
                    registry.captchaList.push(widgetId);
                    registry.tokenFields.push(field);
                }
            }
        );
    }
);
