define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/url'
], function ($, modal, urlBuilder) {
    'use strict';

    return function (Component) {
        return Component.extend(
            {
                placeOrder: function () {
                    if (!this.validate()) {
                        return;
                    }  
                   var isEnabled = window.checkoutConfig.cybersource_recaptcha.enabled.cybersource;
                   if(isEnabled)
                        var options = {
                            type: 'popup',
                            responsive: true,
                            innerScroll: true,
                            buttons: [{
                                text: $.mage.__('OK'),
                                class: 'mymodal1',
                                click: function () {
                                    var url = urlBuilder.build("checkout");
                                    window.location = url;
                                    this.closeModal();
                                }
                            }]
                        };
                
                        var popup = modal(options, $('#flex-recaptcha'));    
                        var rresponse = jQuery('#g-recaptcha-response').val();
                        if(rresponse.length == 0) {
                            $("#flex-recaptcha").modal("openModal");
                            this.isPlaceOrderActionAllowed(false);
                        }
                        $('#flex-recaptcha').on('modalclosed', function() { 
                            var url = urlBuilder.build("checkout");
                            window.location = url;
                        });
                }
            }
        );
    };
});