/* global selectedParts */

document.addEventListener('DOMContentLoaded', function () {

    const $disposalWindow = $('#disposal-window');
    const disposalForm = document.getElementById('disposal-form');
    const $openDisposalWindowBtn = $('[data-action="open-disposal-window"]');
    let blockFlag = false;
    let numInputs = null;
    $openDisposalWindowBtn.addClass('disabled');


    $('[data-datetimepicker]', $disposalWindow).datepicker({
        language: 'ru',
        autoClose: true,
        timepicker: true
    });


    $('body').on('input', '[data-input="set-num-for-all"]', function () {
        if (!numInputs) {
            return;
        }
        const val = this.value;
        for (let input of numInputs) {
            let maxVal = +input.getAttribute('max');
            if (maxVal < val) {
                input.value = maxVal;
            } else {
                input.value = val;
            }
        }
    });


    $('body').on('click', '[data-action]', function (event) {
        event.preventDefault();
        switch (this.dataset.action) {

            case 'apply-disposal':
                applyDisposal();
                break;

            case 'send-disposal-request':
                if (confirm('Подтверждаете запрос на утилизацию?')) {
                    sendDisposalRequest();
                }
                break;

            case 'cancel-disposal':
                cancelDisposal();
                break;

            case 'open-disposal-window':
                openDisposalWindow();
                break;
        }
    });


    $(document).on('parts:selectionchanged', function () {
        if (selectedParts.length) {
            $openDisposalWindowBtn.removeClass('disabled');
        } else {
            $openDisposalWindowBtn.addClass('disabled');
        }
    });


    function applyDisposal() {
        if (blockFlag) {
            return;
        }
        blockFlag = true;
        const data = new FormData(disposalForm);
        $.ajax({
            type: 'POST',
            url: '?ajax=dispose-parts',
            dataType: 'json',
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            success: function (resp) {
                if (+resp.error_flag) {
                    alert(resp.message);
                } else {
                    location.reload();
                }
            },
            complete: function () {
                blockFlag = false;
            },
            error: function (jqXHR) {
                console.error('Ошибка сервера');
                console.error(jqXHR.responseText);
            }
        });
    }


    function sendDisposalRequest() {
        if (blockFlag) {
            return;
        }
        blockFlag = true;
        $.ajax({
            type: 'POST',
            url: '?ajax=send-dispose-request',
            dataType: 'json',
            data: new FormData(disposalForm),
            processData: false,
            contentType: false,
            cache: false,
            success: function (resp) {
                if (+resp.error_flag) {
                    alert(resp.message);
                } else {
                    location.reload();
                }
            },
            complete: function () {
                blockFlag = false;
            },
            error: function (jqXHR) {
                console.error('Ошибка сервера');
                console.error(jqXHR.responseText);
            }
        });
    }


    function cancelDisposal() {
        $.fancybox.close();
        $('[data-input="set-num-for-all"]').val(1);
        numInputs = null;
    }


    function openDisposalWindow() {
        const ids = [];
        const depotIDs = [];
        for (let part of selectedParts) {
            ids.push(part.id);
            depotIDs.push(part.depot_id);
        }
        $.ajax({
            type: 'POST',
            url: '?ajax=get-dispose-parts-table',
            dataType: 'json',
            data: `part_ids=${ids.join(',')}&depot_ids=${depotIDs.join(',')}`,
            cache: false,
            success: function (resp) {
                const listContainer = document.getElementById('disposal-parts-list');
                listContainer.innerHTML = resp.parts_table_html;
                numInputs = listContainer.querySelectorAll('[data-input="disp-part-num"]');
            },
            error: function (jqXHR) {
                console.error('Ошибка сервера');
                console.error(jqXHR.responseText);
            }
        });
        $.fancybox.open($disposalWindow);
    }

});