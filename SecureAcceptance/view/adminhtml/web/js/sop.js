define([
    'jquery',
    'CyberSource_SecureAcceptance/js/cc-inputs-enabler',
    'CyberSource_SecureAcceptance/js/vault/enabler',
    'transparent'
], function ($, inputsEnabler, vaultEnabler) {
    'use strict';

    $.widget('cybersource.sop', $.mage.transparent, {

        _orderSave: function () {
            inputsEnabler(this.options.gateway, true);
            var postData = {
                'form_key': FORM_KEY,
                'cc_type': this.ccType(),
                'vault_enabled': vaultEnabler(this.element, this.options.gateway),
                'order_data': $(this.options.editFormSelector).serialize()
            };
            inputsEnabler(this.options.gateway, false);
            $.ajax({
                url: this.options.orderSaveUrl,
                type: 'post',
                context: this,
                data: postData,
                dataType: 'json',

                success: function (response) {
                    if (response.success && response[this.options.gateway]) {
                        this._postPaymentToGateway(response);
                        return;
                    }
                    $('body').trigger('processStop');
                    this._processErrors(response);
                }.bind(this)
            });
        }
    });

    return $.cybersource.sop;
});
