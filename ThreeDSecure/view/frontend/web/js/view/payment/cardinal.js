define(
    [
        'jquery',
        'songbird',
        'CyberSource_ThreeDSecure/js/action/create-token'
    ],
    function ($, Cardinal, getTokenAction) {
        return {
            init: function (debug) {
                if (!debug) {
                    return;
                }
                Cardinal.configure({
                    logging: {
                        level: "on"
                    }
                });
            },
            setup: function (messageContainer, data, accountNumber) {

                var setupDeferred = $.Deferred(), that = this;
                Cardinal.on('payments.setupComplete', this.setupCompleteHandler.bind(this, setupDeferred));
                console.log('setup start');

                getTokenAction(messageContainer, data).done(function (token) {
                    Cardinal.setup('init', {
                        jwt: token
                    })
                });

                if (typeof accountNumber !== 'undefined') {
                    setupDeferred.then(function () {
                        return this.binDetect(accountNumber);
                    }.bind(this));
                }

                return setupDeferred.promise();

            },
            continue: function (data, order) {

                var validateDeferred = $.Deferred();
                Cardinal.on('payments.validated', this.paymentValidatedHandler.bind(this, validateDeferred));
                console.log('Sending order data to Cardinal:', data, order);
                Cardinal.continue('cca', data, order);

                return validateDeferred.promise();
            },
            setupCompleteHandler: function (deferred, result) {
                Cardinal.off('payments.setupComplete');
                setTimeout(function () {
                    deferred.resolve(result.sessionId);
                }, 1);
                console.log('setup complete', JSON.stringify(result)); //TODO: remove after development
            },
            paymentValidatedHandler: function (deferred, data, jwt) {
                Cardinal.off('payments.validated');
                if (data.ErrorNumber !== 0) {
                    deferred.reject({
                        status: 400,
                        responseText: JSON.stringify({message: data.ErrorDescription})
                    });
                }

                setTimeout(function () {
                    deferred.resolve(jwt);
                }, 1);
            },
            binDetect: function (ccNumber) {

                var deferred = $.Deferred();

                Cardinal.trigger("bin.process", ccNumber).then(function (result) {
                    console.log('BIN detect promise done, result:', JSON.stringify(result));
                    deferred.resolve();
                });

                return deferred.promise();
            }
        }
    });
