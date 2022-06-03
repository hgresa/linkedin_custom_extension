define(['jquery'], function ($) {
    'use strict'

    function fieldIsRequired(elementId)
    {
        return $(`#${elementId}`).hasClass('required')
    }

    function validateLinkedinProfileUrl(value) {
        const regex = /^(http(s)?:\/\/)?([\w]+\.)?linkedin\.com\/(pub|in|profile)/gm;

        return regex.test(value)
    }

    return function () {
        $.validator.addMethod(
            'validate-linkedin-profile-url',
            function (value, element) {
                if (!fieldIsRequired(element.id) && element.value === '') {
                    return true
                }

                return validateLinkedinProfileUrl(value)
            },
            $.mage.__('Given URL is not valid')
        )
    }
})
