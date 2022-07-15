define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/full-screen-loader'
], function ($, wrapper, fullScreenLoader) {

    'use strict';

    return function (errorProcessor) {

        function getMethodCode(quote) {
            var code = (quote.paymentMethod() && quote.paymentMethod().method)
                ? quote.paymentMethod().method
                : 'chcybersource';

            return code.replace(/_(\d+)$/, '');
        }

        function placeOrder(additionalAttributes){
            require(['Magento_Checkout/js/action/place-order', 'Magento_Checkout/js/model/quote', 'Magento_Checkout/js/action/redirect-on-success'],
                function (placeOrderAction, quote, redirectOnSuccessAction) {
                    let attributes = {'method': getMethodCode(quote)};
                    if(additionalAttributes){
                        attributes = {...attributes, ...additionalAttributes};
                    }
                    placeOrderAction(attributes).done(
                        function () {
                            redirectOnSuccessAction.execute();
                        }
                    ).fail(
                        function () {
                            fullScreenLoader.stopLoader();
                        }
                    );
                }
            );
        }

        errorProcessor.process = wrapper.wrap(
            errorProcessor.process,
            function (originalProcess, response, messageContainer) {
                if(response.responseJSON && response.responseJSON.code)
                {
                    if(response.responseJSON.code === 475){
                        require(['CyberSource_ThreeDSecure/js/view/payment/cardinal'], function (Cardinal) {
                            $.when(
                                Cardinal.continue(response.responseJSON.parameters.cca, response.responseJSON.parameters.order)
                            )
                            .then((response)=>{
                                placeOrder({
                                    'extension_attributes': {'cca_response': response}
                                });
                            })
                            .fail((response) => {
                                originalProcess(response, messageContainer);
                            });
                        });
                    }
                    else if(response.responseJSON.code === 478){
                        placeOrder({
                            'extension_attributes': {'cca_response': ''}
                        })
                    }
                    else{
                        return originalProcess(response, messageContainer);
                    }
                }
                else{
                    return originalProcess(response, messageContainer);
                }
            });

        return errorProcessor;
    };
});
