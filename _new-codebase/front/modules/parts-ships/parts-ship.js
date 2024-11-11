/* global $ */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    const $rowTpl = $('#row-tpl').detach();
    const $providerInput = $('[data-input="provider"]');
    const $orderInput = $('[data-input="order"]');
    const $modelIDInput = $('[data-input="model_id"]');
    const $serialInput = $('[data-input="serial"]');
    const $addRowContainer = $('#add-row-container');
    const $notif = $('#form-notif');
    let blockFlag = false;


    initSelect2($('.select2'));


    const errorCb = function(jqXHR) {
        console.error('Ошибка сервера');
        console.error(jqXHR.responseText);
    };


    $('[data-datepicker]').datepicker({
        language: 'ru',
        autoClose: true,
        timepicker: true
    });


    if (document.querySelector('[data-input="ship-id"]').value != 0) {
        $('#parts-table input').attr('readonly', 'true');
        $('#parts-table select').attr('disabled', 'true');
        $('[data-input="send-date"]').attr('readonly', 'true');
    }


    $('#parts-ship-form').on('submit', function(event) {
        event.preventDefault();
        if (blockFlag) {
            return;
        }
        blockFlag = true;
        const data = new FormData(this);
        const submitBtn = this.querySelector('[type="submit"]');
        submitBtn.innerText = 'Сохранение...';
        $.ajax({
            type: 'POST',
            url: '?ajax=save',
            dataType: 'json',
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            success: function(resp) {
                submitBtn.innerText = 'Сохранить';
                if (resp.error_flag == 1) {
                    $notif.addClass('error').html(resp.message).fadeIn();
                    return;
                }
                $notif.fadeOut();
                location.href = '/parts-ships/';
            },
            complete: function() {
                blockFlag = false;
            },
            error: errorCb
        });
    });


    $('[data-input="serial"]').on('change', function() {
        $providerInput.val('');
        $orderInput.val('');
        if (!$serialInput.val()) {
            return;
        }
        $.ajax({
            type: 'POST',
            url: '/repair-api.php',
            dataType: 'json',
            data: `action=get-serial-info&serial=${$serialInput.val()}&model_id=${$modelIDInput.val()}`,
            success: function(resp) {
                $providerInput.val(resp.provider);
                $orderInput.val(resp.order);
            }
        });
    });


    $('body').on('click', '[data-action]', function(event) {
        event.preventDefault();
        switch (this.dataset.action) {
            case 'add-row':
                addRow();
                break;

            case 'del-row':
                delRow(this);
                break;
        }
    });


    function addRow() {
        const $newRow = $rowTpl.clone();
        $newRow.css('display', 'none');
        $addRowContainer.before($newRow);
        $newRow.fadeIn();
        initSelect2($('.select2', $newRow));
    }


    function delRow(triggerElem) {
        if (confirm('Удалить данные?')) {
            $(triggerElem.closest('[data-elem="row"]')).fadeOut(300, function() {
                this.remove();
            });
        }
    }


    function initSelect2($elems) {
        $elems.select2({
            language: 'ru'
        });
    }

});