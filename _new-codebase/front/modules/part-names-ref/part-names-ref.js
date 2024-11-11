/* global $ */

document.addEventListener('DOMContentLoaded', function () {

    const tableURL = new URL(location.href);
    tableURL.searchParams.set('ajax', 'get-names');
    let isPending = false;
    let data = {};

    const $table = $('#datatable').DataTable({
        ...datatableDefaultConfig,
        'processing': true,
        'serverSide': true,
        'ajax': tableURL.href
    });


    $('body').on('click', '[data-action]', function (event) {
        event.preventDefault();
        switch (this.dataset.action) {
            case 'add-name':
                openAddForm();
                break;

            case 'save':
                save();
                break;
        }
    });

    $('#add-form').on('submit', function (event) {
        event.preventDefault();
        const $form = $(this);
        const data = new FormData(this);
        $.ajax({
            type: 'POST',
            url: '?ajax=add-name',
            processData: false,
            contentType: false,
            data,
            complete: function () {
                $.fancybox.close();
                $form.find('input').val('');
                $table.order([0, 'desc']);
                $table.page('first');
                $table.ajax.reload();
            }
        });
    });

    function openAddForm() {
        $.fancybox.open($('#add-form-window'));
    }


    function save() {
        if (isPending || !Object.keys(data).length) {
            return;
        }
        isPending = true;
        $.ajax({
            type: 'POST',
            url: '?ajax=save',
            data: 'data=' + JSON.stringify(data),
            success: function (resp) {
                if (+resp['error_flag']) {
                    alert(resp['message']);
                    return;
                }
                alert('Шаблоны сохранены.');
                $table.order([0, 'desc']);
                $table.page('first');
                $table.ajax.reload();
                data = {};
            },
            complete: function () {
                isPending = false;
            },
        });
    }


    $('body').on('change', '[data-input]', function () {
        switch (this.dataset.input) {
            case 'en':
            case 'ru':
                collectData(this.dataset.input, this.dataset.id, this.value);
                break;
        }
    });


    window.onbeforeunload = function () {
        if (Object.keys(data).length) {
            return false;
        }
    };


    function collectData(field, id, value) {
        data[id + field] = { id, field, value };
    }


});