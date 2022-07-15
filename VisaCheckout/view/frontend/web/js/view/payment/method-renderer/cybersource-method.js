define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'visaSdk',
        'CyberSource_VisaCheckout/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/action/set-billing-address',
        'jquery/ui',
        'mage/translate'
    ],
    function (
        $,
        Component,
        visaSdk,
        setPaymentMethodAction,
        additionalValidators,
        quote,
        customerData,
        setBillingAddress
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                active: false,
                template: 'CyberSource_VisaCheckout/payment/cybersource-form',
                code: 'chcybersourcevisa',
                grandTotalAmount: null,
                currencyCode: null,
                imports: {
                    onActiveChange: 'active'
                }
            },
            initObservable: function () {
                var self = this;
                this._super()
                    .observe(['active']);
                this.grandTotalAmount = quote.totals()['base_grand_total'];
                this.currencyCode = quote.totals()['base_currency_code'];

                quote.totals.subscribe(function () {
                    if (self.grandTotalAmount !== quote.totals()['base_grand_total']) {
                        self.grandTotalAmount = quote.totals()['base_grand_total'];
                    }

                    if (self.currencyCode !== quote.totals()['base_currency_code']) {
                        self.currencyCode = quote.totals()['base_currency_code'];
                    }
                });

                return this;
            },
            onActiveChange: function (isActive) {
                if (!isActive) {
                    return;
                }

                jQuery('.v-button').click(this.vcoButtonClickHandler.bind(this));
                this.initVisaCheckout(this.currencyCode, this.grandTotalAmount);
            },
            getCode: function () {
                return this.code;
            },
            isActive: function () {
                var active = this.getCode() === this.isChecked();

                this.active(active);

                return active;
            },
            getTitle: function () {
                return window.checkoutConfig.payment['chcybersourcevisa'].title;
            },
            getApiKey: function () {
                return window.checkoutConfig.payment['chcybersourcevisa'].api_key;
            },
            getPlaceOrderUrl: function () {
                return window.checkoutConfig.payment['chcybersourcevisa'].placeOrderUrl;
            },
            getData: function () {
                var data = {
                    'method': this.item.method
                };

                data['additional_data'] = _.extend(data['additional_data'], this.additionalData);

                return data;
            },
            getButtonUrl: function () {
                return window.checkoutConfig.payment['chcybersourcevisa'].buttonUrl;
            },

            placeOrder: function (callId) {
                setBillingAddress();
                if (additionalValidators.validate()) {
                    this.selectPaymentMethod();

                    var agreements = $(".payment-method-chcybersourcevisa .checkout-agreements-block").find('input');
                    var form = $(document.createElement('form'));
                    $(form).attr("action", this.getPlaceOrderUrl());
                    $(form).attr("method", "POST");
                    $(form).append($('<input type="hidden"/>').attr('name', 'form_key').attr('value', $.cookie('form_key')));
                    $(form).append($('<input type="hidden"/>').attr('name', 'callId').attr('value', callId));
                    $(form).append($('<input type="hidden"/>').attr('name', 'quoteId').attr('value', quote.getQuoteId()));
                    agreements.each(function () {
                        $(form).append('<input type="hidden" name="' + $(this).attr('name') + '" value="' + $(this).val() + '"/>');
                    });
                    $("body").append(form);
                    $(form).submit();

                    return false;
                }
            },
            vcoButtonClickHandler: function(event) {
                if (!this.validate() || !additionalValidators.validate()) {
                    event.stopImmediatePropagation();
                }
            },
            initVisaCheckout: function (currencyCode, totalAmount) {
                var self = this;

                V.init({
                    apikey: self.getApiKey(),
                    paymentRequest:{
                        currencyCode: currencyCode,
                        subtotal: totalAmount
                    }
                });
                V.on("payment.success", function (payment) {
                    var callId = payment.callid;
                    self.placeOrder(callId);
                });

                V.on("payment.error", function (payment, error) {
                    self.messageContainer.addErrorMessage(error.message);
                });
            }
        });
    }
);
