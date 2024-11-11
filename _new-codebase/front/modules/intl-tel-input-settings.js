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

    // here, the index maps to the error code returned from getValidationError - see readme
    var errorMap = ['Неверный номер', 'Неверный код страны', 'Не хватает цифр', 'Лишние цифры', 'Неверный номер'];

    var phoneFields = {
        'shop-phone': {
            'input': window.intlTelInput(document.getElementById('shop-phone'), params),
            'error': $('#shop-phone-error')
        },
        'client-phone': {
            'input': window.intlTelInput(document.getElementById('client-phone'), params),
            'error': $('#client-phone-error')
        }
    };

    $('[data-intl-tel-input]').on('blur', function() {
        let phone = phoneFields[this.id];
        this.classList.remove('phone-error');
        phone.error.html('');
        phone.error.hide();
        if (this.value.length && !phone.input.isValidNumber()) {
            var errorCode = phone.input.getValidationError();
            if (errorCode < 0 || errorMap[errorCode] == 'undefined') {
                return;
            }
            phone.error.html(errorMap[errorCode]);
            phone.error.show();
        }
    });

});