/* global selectedParts */

document.addEventListener('DOMContentLoaded', function () {


    const $openMoveWindowBtn = $('[data-action="open-move-window"]');
    $openMoveWindowBtn.addClass('disabled');
    let moveForm = null;
    let blockFlag = false;


    $('body').on('click', '[data-action]', function (event) {
        event.preventDefault();
        switch (this.dataset.action) {
            case 'apply-move':
                applyMove();
                break;

            case 'open-move-window':
                openMoveWindow();
                break;

            case 'close-window':
                $.fancybox.close();
                break;
        }
    });


    $(document).on('parts:selectionchanged', function () {
        if (selectedParts.length) {
            $openMoveWindowBtn.removeClass('disabled');
        } else {
            $openMoveWindowBtn.addClass('disabled');
        }
    });


    function openMoveWindow() {
        $.fancybox.open({
            src: '?ajax=get-move-parts-window',
            type: 'ajax',
            clickSlide: false,
            clickOutside: false,
            afterLoad: function (_, current) {
                current.$content.find('.select2').select2();
                moveForm = document.getElementById('move-form');
            }
        });
    }


    function applyMove() {
        if (blockFlag) {
            return;
        }
        blockFlag = true;
        const ids = [];
        const depotIDs = [];
        for (let part of selectedParts) {
            ids.push(part.id);
            depotIDs.push(part.depot_id);
        }
        const data = new FormData(moveForm);
        data.append('part_ids', ids.join(','));
        data.append('depot_ids', depotIDs.join(','));
        $.ajax({
            type: 'POST',
            url: '?ajax=move-parts',
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


});