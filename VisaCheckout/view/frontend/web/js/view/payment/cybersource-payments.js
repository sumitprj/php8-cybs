/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        rendererList.push(
            {
                type: 'chcybersourcevisa',
                component: 'CyberSource_VisaCheckout/js/view/payment/method-renderer/cybersource-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
