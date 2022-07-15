define([
    'jquery',
    'Magento_Payment/js/view/payment/iframe',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/action/set-payment-information',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Vault/js/view/payment/vault-enabler'
], function ($, Component, additionalValidators, setPaymentInformationAction, fullScreenLoader, VaultEnabler) {

    return Component.extend({
        defaults: {
            active: false,
            template: 'CyberSource_SecureAcceptance/payment/sa/redirect',
            code: 'chcybersource'
        },
        redirectAfterPlaceOrder: false,
        initialize: function () {
            this._super();
            this.vaultEnabler = new VaultEnabler();
            this.vaultEnabler.setPaymentCode(this.getVaultCode());
        },
        isActive: function () {
            return true;
        },
        getData: function () {
            var data = {
                'method': this.getCode(),
                'additional_data': {}
            };

            if (this.getAppendCardData()) {
                data.additional_data.ccType = this.creditCardType();
            }

            this.vaultEnabler.visitAdditionalData(data);

            return data;
        },
        isVaultEnabled: function () {
            return this.vaultEnabler.isVaultEnabled();
        },
        getVaultCode: function () {
            return window.checkoutConfig.payment[this.getCode()].vaultCode;
        },
        getCode: function () {
            return this.code;
        },
        context: function () {
            return this;
        },
        setPlaceOrderHandler: function (handler) {
            this.placeOrderHandler = handler;
        },
        setValidateHandler: function (handler) {
            this.validateHandler = handler;
        },
        getAppendCardData: function() {
            return !!window.checkoutConfig.payment[this.getCode()].silent_post;
        },
        placeOrder: function () {
            if (!this.validateHandler() || !additionalValidators.validate()) {
                return;
            }

            fullScreenLoader.startLoader();

            this.isPlaceOrderActionAllowed(false);

            this.getPlaceOrderDeferredObject()
                .then(this.placeOrderHandler)
                .then(this.initTimeoutHandler.bind(this))
                .always(
                    function () {
                        this.isPlaceOrderActionAllowed(true);
                    }.bind(this)
                )
            ;
        }
    });


});
