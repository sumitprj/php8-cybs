define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    var
        creditCartTypes = {
            'DI': [new RegExp('^6(?:011\\d{12}|5\\d{14}|4[4-9]\\d{13}|22(?:1(?:2[6-9]|[3-9]\\d)|[2-8]\\d{2}|9(?:[01]\\d|2[0-5]))\\d{10})$'), new RegExp('^[0-9]{3}$'), true]
        },
        validateCardTypeAdditional = function (value, element, params) {
            var ccType;

            if (!value || !params) {
                return false;
            }

            ccType = $(params).val();
            value = value.replace(/\s/g, '').replace(/\-/g, '');

            if (creditCartTypes[ccType] && creditCartTypes[ccType][0]) {
                return creditCartTypes[ccType][0].test(value);
            } else if (creditCartTypes[ccType] && !creditCartTypes[ccType][0]) {
                return true;
            }

            return false;
        };

    return function (validation) {

        if (typeof $.validator.methods['validate-cc-type'] === 'undefined') {
            return validation;
        }

        $.validator.methods['validate-cc-type'] = wrapper.wrap(
            $.validator.methods['validate-cc-type'],
            function (_super, value, element, params) {
                return _super(value, element, params) || validateCardTypeAdditional(value, element, params);
            }
        );

        return validation;
    };
});
