/* global $ */

document.addEventListener('DOMContentLoaded', function() {

    const url = new URL(location.href);

    $('.select2').select2({
        language: 'ru'
    });


    $('[data-filter]').on('change', function() {
        if (!this.value || this.value == '0') {
            url.searchParams.delete(this.name);
        } else {
            url.searchParams.set(this.name, this.value);
        }
        history.pushState(null, null, url.href);
    });


    $('[data-action]').on('click', function(event) {
        event.preventDefault();
        switch (this.dataset.action) {
            case 'apply':
                location.reload();
                break;

            case 'reset':
                location.href = url.pathname;
                break;
        }
    });


});