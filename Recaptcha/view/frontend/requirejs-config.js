var config = {
    map: {
        '*': {
            'CyberSource_SecureAcceptance/template/payment/iframe.html': 'CyberSource_Recaptcha/template/payment/iframe.html',
            'CyberSource_SecureAcceptance/template/payment/hosted/iframe.html': 'CyberSource_Recaptcha/template/payment/hosted/iframe.html',
            'CyberSource_SecureAcceptance/template/payment/hosted/redirect.html': 'CyberSource_Recaptcha/template/payment/hosted/redirect.html'
        }
    },
    config: {
        mixins: {
            'CyberSource_SecureAcceptance/js/view/payment/method-renderer/iframe': {
                'CyberSource_Recaptcha/js/iframe-mixin': true
            }
        }
    }
};
