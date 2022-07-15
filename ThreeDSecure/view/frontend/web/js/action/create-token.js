define([
    'jquery',
    'mage/url',
    'Magento_Ui/js/model/messageList',
    'mage/cookies'
], function ($, urlBuilder, globalMessageList) {
    'use strict';

    return function (messageContainer, data) {
        var deferred = $.Deferred();
        var messages = messageContainer || globalMessageList;

        data = $.extend(data || {}, {
            'form_key': $.mage.cookies.get('form_key')
        });

        $.ajax(
            urlBuilder.build('cybersource3ds/cca/requestToken', {}),
            {'data': data, 'method': 'POST'}
        ).then(
            function (response) {
                if (!response.success) {
                    messages.addErrorMessage(response.error_msg);
                    deferred.reject();
                    return;
                }
                deferred.resolve(response.token);
            }
        ).fail(function () {
            deferred.reject();
        });

        return deferred.promise();
    };
});
