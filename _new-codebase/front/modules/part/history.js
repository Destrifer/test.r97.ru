/* global $ */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const $historyInTpl = $('#history-in-tpl').detach();
    const $historyOutTpl = $('#history-out-tpl').detach();


    initSelect2($('.select2'));
    initDatepicker($('[data-datepicker]'));


    $('body').on('click', '[data-action]', function (event) {
        event.preventDefault();
        switch (this.dataset.action) {

            case 'del-history':
                delHistory($(this).closest('[data-history]'));
                break;

            case 'add-history-in':
                addHistoryInBlock($(this).closest('[data-trigger]'));
                break;

            case 'add-history-out':
                addHistoryOutBlock($(this).closest('[data-trigger]'));
                break;
        }
    });


    $('body').on('change', '[data-input]', function () {
        switch (this.dataset.input) {

            case 'reason-id':
                updateBlock($(this).closest('[data-history]'), this.value);
                break;

            case 'history-model-id':
            case 'history-serial':
                loadSerialInfo($(this).closest('[data-history]'));
                break;
        }
    });


    function delHistory($history) {
        $history.fadeOut(300, function () {
            this.remove();
        });
    }


    function updateBlock($block, reasonID) {
        if (reasonID == 4) { // отправка потребителю
            $('[data-elem="recip-field"]', $block).show();
            $('[data-elem="repair-id-field"]', $block).hide();
            $('[data-elem="model-field"]', $block).show();
            $('[data-elem="provider-field"]', $block).show();
            $('[data-elem="order-field"]', $block).show();
            $('[data-elem="serial-field"]', $block).show();
            if (!$block.data('select-init-flag')) {
                initSelect2($('.select2-models', $block));
                $block.data('select-init-flag', true);
            }
        } else {
            $('[data-elem="recip-field"]', $block).hide();
            $('[data-elem="repair-id-field"]', $block).show();
            $('[data-elem="model-field"]', $block).hide();
            $('[data-elem="provider-field"]', $block).hide();
            $('[data-elem="order-field"]', $block).hide();
            $('[data-elem="serial-field"]', $block).hide();
        }
    }


    function loadSerialInfo($block) {
        const $serialInput = $('[data-input="history-serial"]', $block);
        const $providerInput = $('[data-input="history-provider"]', $block);
        const $orderInput = $('[data-input="history-order"]', $block);
        const $modelInput = $('[data-input="history-model-id"]', $block);
        $providerInput.val('');
        $orderInput.val('');
        if (!$serialInput.val() || !+$modelInput.val()) {
            return;
        }
        $.ajax({
            type: 'POST',
            url: '/repair-api.php',
            dataType: 'json',
            data: `action=get-serial-info&serial=${$serialInput.val()}&model_id=${$modelInput.val()}`,
            success: function (resp) {
                $providerInput.val(resp.provider);
                $orderInput.val(resp.order);
            }
        });
    }


    function addHistoryInBlock($trigger) {
        const $newBlock = $historyInTpl.clone();
        $newBlock.css('display', 'none');
        $trigger.before($newBlock);
        $newBlock.fadeIn();
        initDatepicker($('[data-datetimepicker]', $newBlock));
        initSelect2($('.select2-depots', $newBlock));
        return $newBlock;
    }


    function addHistoryOutBlock($trigger) {
        const $newBlock = $historyOutTpl.clone();
        $newBlock.css('display', 'none');
        $trigger.before($newBlock);
        $newBlock.fadeIn();
        initDatepicker($('[data-datetimepicker]', $newBlock));
        initSelect2($('.select2-depots', $newBlock));
        return $newBlock;
    }


    function initSelect2($elems) {
        $elems.select2({
            language: 'ru'
        });
    }


    function initDatepicker($elems) {
        $elems.datepicker({
            language: 'ru',
            autoClose: true,
            timepicker: true
        });
    }

});