'use strict';
/*global $*/
document.addEventListener('DOMContentLoaded', function() {
    (function() {

        init();

        function init() {
            attachEvents();
        }

        function attachEvents() {
            $(document).on('input', '[data-input-filter]', onInput);
            $(document).on('change', '[data-input-filter]', onChange);
        }

        function onInput(event) {
            let type = this.dataset.inputFilter;
            switch (type) {
                case 'date':
                    inputDate(event);
                    break;
                case 'float':
                    inputFloat(event);
                    break;
                case 'phone':
                    inputPhone(event);
                    break;
                case 'int':
                    inputInt(event);
                    break;
                case 'serial':
                    inputSerial(event);
                    break;
            }
        }

        function onChange(event) {
            let type = this.dataset.inputFilter;
            switch (type) {
                case 'date':
                    changeDate(event);
                    break;
                case 'int':
                    changeInt(event);
                    break;
            }
        }

        function changeDate(event) {
            let el = event.target;
            if (!el.value.match(/^\d{2}\.\d{2}\.\d{4}$/)) {
                el.value = '';
            }
            let p = el.value.split('.');
            if (el.dataset.inputFilterMaxDate) {
                if (new Date() < new Date(p[2] + '-' + p[1] + '-' + p[0])) {
                    el.value = '';
                }
            }
            if (el.dataset.inputFilterMinDate) {
                if (new Date(p[2] + '-' + p[1] + '-' + p[0]) < new Date(el.dataset.inputFilterMinDate)) {
                    el.value = '';
                }
            }
        }

        function inputSerial(event) {
            let el = event.target;
            if (!el.value) {
                return;
            }
            el.value = el.value.replace(/[^A-Za-z0-9-]/g, '');
            el.value = el.value.toUpperCase();
        }


        function inputDate(event) {
            let el = event.target;
            el.value = el.value.replace(/[^\d\.]/g, '');
        }


        function inputPhone(event) {
            let el = event.target;
            el.value = el.value.replace(/[^0-9 \-,\(\)\+]/g, '');
            if ((event.which != 46 || el.value.indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        }


        function inputInt(event) {
            let el = event.target;
            el.value = el.value.replace(/[^0-9]/g, '');
            if (event.which < 48 || event.which > 57) {
                event.preventDefault();
            }
        }


        function changeInt(event) {
            let el = event.target;
            const max = +el.getAttribute('max');
            if (!max) {
                return;
            }
            if (+el.value > max) {
                el.value = max;
            }
        }


        function inputFloat(event) {
            let el = event.target;
            el.value = el.value.replace(/[^0-9\.]/g, '');
            if ((event.which != 46 || el.value.indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        }





    })();
});