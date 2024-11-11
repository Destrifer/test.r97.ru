/* global $ */

document.addEventListener('DOMContentLoaded', function () {

    const tableURL = new URL(location.href);
    tableURL.searchParams.set('ajax', 'get-log');

    const $table = $('#datatable').DataTable({
        ...datatableDefaultConfig,
        'processing': true,
        'serverSide': true,
        'ajax': tableURL.href
    });


    $('[data-datepicker-interval]').datepicker({
        language: 'ru',
        autoClose: true,
        maxDate: new Date(),
        onSelect: function (formattedDate, date, inst) {
            inst.$el.trigger('change');
        }
    });


    $('body').on('click', '[data-action]', function (event) {
        event.preventDefault();
        switch (this.dataset.action) {
            case 'revert':
                revert(this);
                break;

            case 'apply':
                $table.page('first');
                $table.state.save();
                location.reload();
                break;

            case 'reset':
                $table.page('first');
                $table.state.save();
                location.href = url.pathname;
                break;
        }
    });


    function revert(triggerElem) {
        if (!confirm('Отменить операцию?')) {
            return;
        }
        const id = triggerElem.dataset.id;
        $.ajax({
            type: 'POST',
            url: '?ajax=revert',
            data: `id=${id}`,
            dataType: 'json',
            success: function (resp) {
                if (+resp.error_flag) {
                    alert(resp.message);
                } else {
                    triggerElem.insertAdjacentHTML('beforebegin', '<i>готово</i>');
                    triggerElem.remove();
                }
            },
        });
    }


});