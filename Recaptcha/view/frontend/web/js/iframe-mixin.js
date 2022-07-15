define([
    'jquery',
    'Magento_Checkout/js/model/payment/additional-validators'
], function ($, additionalValidators) {
    'use strict';

    return function (Component) {
        return Component.extend(
            {
                placeOrder: function () {
                    var isEnabled = window.checkoutConfig.msp_recaptcha.enabled.cybersource,
                        $form = $('#co-payment-form'),
                        _super = this._super.bind(this)
                    ;

                    if (!this.validateHandler() || !additionalValidators.validate() || !isEnabled) {
                        return _super();
                    }

                    $form
                        .off('cybersource:endRecaptcha')
                        .on('cybersource:endRecaptcha', function () {
                                // this._super.bind(this)();
                                _super();
                                $form.off('cybersource:endRecaptcha');
                            }.bind(this)
                        );

                    $form.trigger('cybersource:startRecaptcha');
                }
            }
        );
    };
});
