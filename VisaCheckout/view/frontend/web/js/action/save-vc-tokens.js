define(
    [
        'jquery',
        'mage/url',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function ($, urlBuilder, storage) {
        'use strict';

        return function (callId) {
            var serviceUrl,
                payload;
            
            serviceUrl = 'chcybersourcevisa/index/saveTokens';
            payload = {
                callId: callId
            };

            return $.ajax({
                url: urlBuilder.build(serviceUrl),
                type: 'POST',
                data: payload,
                dataType: 'json'
            });

        };
    }
);
