var config = {
    map: {
        '*': {
            'CyberSource_SecureAcceptance/template/payment/iframe.html': 'CyberSource_Recaptcha/template/payment/iframe.html',
            'CyberSource_SecureAcceptance/template/payment/hosted/iframe.html': 'CyberSource_Recaptcha/template/payment/hosted/iframe.html',
            'CyberSource_SecureAcceptance/template/payment/hosted/redirect.html': 'CyberSource_Recaptcha/template/payment/hosted/redirect.html',
            'CyberSource_SecureAcceptance/template/payment/sa/redirect.html': 'CyberSource_Recaptcha/template/payment/sa/redirect.html',
            'CyberSource_SecureAcceptance/template/payment/sa/iframe.html': 'CyberSource_Recaptcha/template/payment/sa/iframe.html',
            'CyberSource_SecureAcceptance/template/payment/flex-microform.html': 'CyberSource_Recaptcha/template/payment/flex-microform.html'        }
    },
    config: {
        mixins: {
            'CyberSource_SecureAcceptance/js/view/payment/method-renderer/iframe': {
                'CyberSource_Recaptcha/js/iframe-mixin': true
            },
            'Magento_Ui/js/view/messages': {
                'CyberSource_Recaptcha/js/ui-messages-mixin': true
            }
        }
    }
};
