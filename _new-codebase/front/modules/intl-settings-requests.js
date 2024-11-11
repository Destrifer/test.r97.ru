/* global $ */

document.addEventListener('DOMContentLoaded', function() {
    var params = {
        initialCountry: 'ru',
        autoHideDialCode: false,
        nationalMode: false,
        placeholderNumberType: 'FIXED_LINE',
        onlyCountries: ['am', 'ua', 'by', 'kz', 'ru', 'tj', 'md', 'kg'],
        localizedCountries: {
            'am': 'Армения',
            'ua': 'Украина',
            'by': 'Беларусь',
            'kz': 'Казахстан',
            'ru': 'Россия',
            'tj': 'Таджикистан',
            'md': 'Молдова',
            'kg': 'Киргизия'
        },
        utilsScript: '/_new-codebase/front/vendor/intl-tel-input/js/utils.js?1562189064761'
    };

    const intls = {};
    const inputs = document.querySelectorAll('[data-intl-tel-input]');
    for(let input of inputs) {
        intls[input.getAttribute('name')] = window.intlTelInput(input, params);
    }

    
    // here, the index maps to the error code returned from getValidationError - see readme
    var errorMap = ['Неверный номер', 'Неверный код страны', 'Не хватает цифр', 'Лишние цифры', 'Неверный номер'];


    $('[data-intl-tel-input]').on('blur', function() {
        const $input = $(this);
        const intl = intls[this.getAttribute('name')];
        $input.removeClass('field-validation-error');
        if ($input.val().length && !intl.isValidNumber()) {
            var errorCode = intl.getValidationError();
            if (errorCode < 0 || errorMap[errorCode] == 'undefined') {
                return;
            }
            $input.addClass('field-validation-error');
            $input.tooltipster('content', errorMap[errorCode]);
            $input.tooltipster('open');
        }
    });

});