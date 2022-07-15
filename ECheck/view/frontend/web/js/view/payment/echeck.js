/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See LICENSE.txt for license details.
 */
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
                type: 'cybersourceecheck',
                component: 'CyberSource_ECheck/js/view/payment/method-renderer/echeck-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
