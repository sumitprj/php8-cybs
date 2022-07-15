define(
    [
        'jquery',
        'uiComponent',
        'CyberSource_VisaCheckout/js/action/save-vc-tokens',
        'visaSdk',
        'mage/url',
        'jquery/ui',
        'mage/translate',
        'mage/mage'
    ],
    function ($,
              Component,
              saveTokensAction,
              visaSdk,
              url) {
        'use strict';        

        return Component.extend({
            additionalData: {},
            defaults: {
                active: false,                
                apiKey: '',
                shortcutContainerClass: '',
                isCatalogProduct: false,
                reviewUrl: ''
            },
            initialize: function () {
                this._super();
                
                var block = $(this.shortcutContainerClass);
                if (block) {
                    block.children('.v-button').on('click', this.vcButtonClickHandler.bind(this));
                }

                this.initVisaCheckout();

            },
            vcButtonClickHandler: function (event) {
                this.validateForm(event);
            },
            validateForm: function (event) {

                if (!this.isCatalogProduct) {
                    return;
                }

                var $form = $(this.shortcutContainerClass).closest('form');

                if (!$form.valid()) {
                    event.stopImmediatePropagation();
                }

            },
            getApiKey: function () {
                return this.apiKey;
            },
            initVisaCheckout: function () {
                var that = this;

                V.init({
                    apikey: this.getApiKey()
                });

                V.on("payment.success", function (payment) {
                    saveTokensAction(payment.callid).done(function (data){
                        if (data.login_url) {
                            window.location = data.login_url;
                            return;
                        }

                        window.location = that.reviewUrl;
                    });
                });

                V.on("payment.error", function (payment, error) {
                    console.log(JSON.stringify(error));
                });
            }
        });
    }
);
