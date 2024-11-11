/* global $ */

const api = new API();
const repairForm = {};

document.addEventListener('DOMContentLoaded', function () {

    const $blocksContainer = $('#blocks-container');
    const $repairBlockTpl = $('#repair-block-tpl').detach();
    const $nonRepairBlockTpl = $('#nonrepair-block-tpl').detach();
    const $diagBlockTpl = $('#diag-block-tpl').detach();
    const $form = $('#repair-form');
    const $notif = $('#form-notif');
    const $installSection = $('#approve-install-section');
    const userData = JSON.parse($('#user-data-json').text());
    const repairData = JSON.parse($('#repair-data-json').text());


    $('[data-datepicker]').datepicker({
        language: 'ru',
        autoClose: true,
        maxDate: new Date(),
        onShow: function (inst) {
            if (inst.el.getAttribute('readonly') !== null) {
                inst.hide();
            }
        }
    });

    $('.select2').select2({
        language: 'ru'
    });


    updateControls();
    let blocks = $blocksContainer[0].querySelectorAll('[data-block]');
    for (let i = 0, len = blocks.length; i < len; i++) {
        updateBlock($(blocks[i]));
    }
    updateTakerForm();
    updateInstallSection();


    /* Заблокировать кнопки "Добавить ремонт" и др. в зависимости от блоков */
    function updateControls() {
        if ($('[data-block="repair"]:visible').length) {
            $('[data-action="add-nonrepair"]').addClass('disabled');
        } else {
            $('[data-action="add-nonrepair"]').removeClass('disabled');
        }
        if ($('[data-block="nonrepair"]:visible').length) {
            $('[data-action="add-repair"]').addClass('disabled');
        } else {
            $('[data-action="add-repair"]').removeClass('disabled');
        }
        if (userData.role == 'taker') {
            $('[data-action="add-repair"]').addClass('disabled');
        }
    }

    $('[data-input="master-id"]').on('change', function () {
        updateTakerForm();
    });


    function updateTakerForm() {
        if (userData.role != 'taker') {
            return;
        }
        if ($('[data-input="master-id"]').val() == '-1') {
            $('input').removeAttr('readonly');
            $('select').removeAttr('disabled');
        } else {
            $('input').attr('readonly', 'true');
            /*$('select').attr('disabled', 'true');*/      
            $('[data-input="repair-final"]').removeAttr('readonly');
            $('[data-input="master-id"] option').removeAttr('disabled');
        }
    }


    $('[data-input="repair-final"]').on('change', function () {
        const checked = this.checked;
        const flags = document.querySelectorAll('[data-input="repair-final"]');
        for (let i = 0, len = flags.length; i < len; i++) {
            flags[i].checked = false;
        }
        if (checked) {
            this.checked = true;
        }
    });


    $('body').on('click', '.form__input-error', hideError);


    repairForm.submit = function (formElem) {
        return new Promise((resolve) => {
            api.saveForm(formElem, function (resp) {
                updateWorkIDs(resp);
                showNotif(resp.message, resp.errors);
                const typeInput = document.getElementById('repair-type');
                const priceInput = document.getElementById('total-price');
                if (typeInput && priceInput) {
                    typeInput.value = resp.repair_type;
                    priceInput.value = resp.total_price;
                }
                resolve(resp);
            });
        });
    };


    /* Сохранение формы */
    $form.on('submit', function (event) {
        event.preventDefault();
        repairForm.submit(this);
    });


    /* Показать сообщение после сохранения */
    function showNotif(message, errors) {
        if (errors) {
            $notif.addClass('error');
            showErrors(errors);
        } else {
            $notif.removeClass('error');
            clearErrors();
            $('[data-reset-me]').val('');
        }
        $notif.html(message).show();
        setTimeout(function () {
            $notif.fadeOut(300);
        }, 3000);
    }


    /* Показать уведомления об ошибках */
    function showErrors(errors) {
        let $target, $field, $elem, $error;
        for (let name in errors) {
            $target = $('[data-error-target^="' + name + '"]:eq(' + errors[name].index + ')');
            $field = $('[name^="' + name + '"]:not([disabled="disabled"]):eq(' + errors[name].index + ')');
            $elem = ($target.length) ? $target : $field;
            $elem.addClass('form__input-error');
            $error = $('<div class="form__error">');
            $error.text(errors[name].message);
            $elem.after($error);
        }
        /* Скролл к ошибке */
        let $item = $('.form__input-error:eq(0)');
        if (!$item.length) {
            return;
        }
        let top = $item.offset().top - 100;
        $('html,body').stop().animate({
            scrollTop: top
        }, {
            duration: 500
        });
    }


    function clearErrors() {
        $('.form__error').remove();
    }


    function hideError() {
        let $this = $(this);
        $this.removeClass('form__input-error');
        let $er = $this.next();
        if ($er.hasClass('form__error')) {
            $er.remove();
        }
    }


    /* Действия */
    $('body').on('click', '[data-action]', function (event) {
        event.preventDefault();
        if (this.classList.contains('disabled')) {
            return;
        }
        if (+repairData.blocked_flag) {
            alert('Информацию о ремонте в данный момент нельзя изменить.');
            return;
        }
        switch (this.dataset.action) {
            case 'save-and-close':
                api.saveForm($form[0], function (resp) {
                    if (resp.errors) {
                        showNotif(resp.message, resp.errors);
                    } else {
                        document.location.replace(repairData.back_url);
                    }
                });
                break;
            case 'add-repair':
                addBlock('repair');
                break;
            case 'add-nonrepair':
                addBlock('nonrepair');
                break;
            case 'add-diag':
                addBlock('diag');
                break;
            case 'del-block':
                if (confirm('Удалить информацию?')) {
                    delBlock(this.closest('[data-block]'));
                }
                break;
            case 'choose-part':
                choosePart(this.closest('[data-block]'));
                break;
        }
    });


    /* Обновляет id проделанной работы */
    function updateWorkIDs(resp) {
        if (!resp.work_ids) {
            return;
        }
        const $blocks = $blocksContainer.find('[data-block]:visible');
        let i = 0;
        $blocks.each(function () {
            let idInput = this.querySelector('[data-input="id"]');
            if (idInput) {
                idInput.value = resp.work_ids[i];
            } else {
                console.error('Block id is missing.');
            }
            i++;
        });
    }


    /* Кнопка "Выбрать запчасть" */
    function choosePart(blockElem) {
        api.saveForm($form[0], function (resp) {
            if (resp.errors) {
                showNotif(resp.message, resp.errors);
            } else {
                document.location.replace(`/edit-repair/${repairData.id}/step/3/`);
            }
        });
    }


    /* События change */
    $('body').on('change', '[data-input]', function () {
        let $elem, partTypeID, $partInput;
        switch (this.dataset.input) {
            /* Выбрана запчасть в блоке "Без ремонта" */
            case 'part-select':
                checkPartBalance(+this.value, $(this).closest('[data-block]'));
                break;

            /* Выбран неисправный блок или элемент */
            case 'problems-select':
                if (this.value == 5) { // дефект не обнаружен
                    $partInput = $(this).closest('.row').find('[data-input="part-select"]');
                    $partInput.val(-1); // не использовалась
                    $partInput.trigger('change.select2');
                }
                $elem = $(this).closest('.row').find('[data-input="repair-type-select"]');
                $elem.val('');
                $elem.attr('disabled', 'true');
                partTypeID = $(this).closest('.row').find('[data-input="part-type-id"]').val();
                api.getRepairTypesByProblemID(this.value, partTypeID, function (resp) {
                    $elem.html(resp.html);
                    $elem[0].removeAttribute('disabled');
                });
                break;
        }
    });


    function checkPartBalance(partID, $block) {
        if (partID <= 0 || $block.data('block') != 'nonrepair' || userData.role != 'master') {
            return;
        }
        const validCode = (Math.floor(Math.random() * (9999 - 1000 + 1)) + 1000).toString();
        $block.css({ opacity: '.4', pointerEvents: 'none' });
        api.checkPartBalance(partID, (resp) => {
            if (+resp.has_part) {
                let res = prompt('Запчасть имеется на складе. Если вы хотите продолжать заполнение "Без ремонта", введите число ' + validCode + ' ниже:');
                if (res != validCode) {
                    $block.remove();
                    return;
                } else {
                    $block.find('[data-input="notify-admin"]').val(1);
                }
            }
            $block.css({ opacity: '', pointerEvents: '' });
        });
    }


    /* События input */
    $('body').on('input', '[data-input]', function () {
        let block = this.closest('[data-block]');
        let priceInput, qtyInput, sumElem;
        switch (this.dataset.input) {

            case 'price':
            case 'num':
                priceInput = block.querySelector('[data-input="price"]');
                qtyInput = block.querySelector('[data-input="qty"]');
                sumElem = block.querySelector('[data-name="sum"]');
                if (!priceInput.value.length || !qtyInput.value.length) {
                    sumElem.value = '';
                    return;
                }
                sumElem.value = parseFloat(priceInput.value) * parseFloat(qtyInput.value);
                break;
        }
    });


    /* Добавить блок проделанной работы */
    function addBlock(type) {
        let $tpl;
        if (type == 'repair') {
            $tpl = $repairBlockTpl;
        } else if (type == 'diag') {
            $tpl = $diagBlockTpl;
        } else {
            $tpl = $nonRepairBlockTpl;
        }
        let $newBlock = $tpl.clone();
        $newBlock.css('display', 'none');
        $blocksContainer.append($newBlock);
        updateBlock($newBlock);
        $newBlock.fadeIn();
        $('.select2', $newBlock).select2();
        $newBlock[0].scrollIntoView({
            block: 'center',
            behavior: 'smooth'
        });
        updateControls();
        updateInstallSection();
    }


    /* Удалить блок проделанной работы */
    function delBlock(blockElem) {
        let orderedFlagInput = blockElem.querySelector('[data-ordered-flag-input]');
        if (+blockElem.querySelector('[data-input="id"]').value && orderedFlagInput && +orderedFlagInput.value) {
            alert('Заказанную запчасть нельзя удалить.');
            return;
        }
        $(blockElem).fadeOut(200, function () {
            if (!+blockElem.querySelector('[data-input="id"]').value) {
                this.remove();
            } else {
                blockElem.querySelector('[data-input="del-flag"]').value = 1;
            }
            updateControls();
            updateInstallSection();
        });
    }


    /* Флажок "заказать запчасть" */
    $('body').on('change', '[data-ordered-flag]', function () {
        if (this.checked && !confirm('Подтверждаю, что выполнил все рекомендации по ремонту или доработке данной модели, скачал и обновил ПО из вкладки "Схемы и ПО".')) {
            this.checked = false;
            return;
        }
        let $block = $(this.closest('[data-block]'));
        setFlagState(this.checked, 'ordered-flag', $block);
        if (this.checked) {
            setFlagState(false, 'own-flag', $block);
        }
        updateBlock($block);
    });


    /* Флажок "собственная запчасть" */
    $('body').on('change', '[data-own-flag]', function () {
        let $block = $(this.closest('[data-block]'));
        setFlagState(this.checked, 'own-flag', $block);
        if (this.checked) {
            setFlagState(false, 'ordered-flag', $block);
        } else {
            $block.find('[data-input="price"]').val('');
            $block.find('[data-name="sum"]').val('');
        }
        updateBlock($block);
    });


    function setFlagState(checkedFlag, flagName, $block) {
        if (checkedFlag) {
            $block.find(`[data-${flagName}-input]`).val(1);
            $block.find(`[data-${flagName}]`).prop('checked', true);
        } else {
            $block.find(`[data-${flagName}-input]`).val(0);
            $block.find(`[data-${flagName}]`).prop('checked', false);
        }
    }


    function updateBlock($block) {
        const $orderedFlag = $block.find('[data-ordered-flag-input]');
        if ($orderedFlag.length) {
            if (+$orderedFlag.val()) {
                $block.find('[data-unordered-form]').addClass('inactive').removeClass('active');
                $block.find('[data-ordered-form]').hide().removeClass('inactive').addClass('active').fadeIn();
            } else {
                $block.find('[data-unordered-form]').hide().removeClass('inactive').addClass('active').fadeIn();
                $block.find('[data-ordered-form]').addClass('inactive').removeClass('active');
            }
        }
        const $ownFlag = $block.find('[data-own-flag-input]');
        if ($ownFlag.length) {
            if (+$ownFlag.val()) {
                $('[data-unordered-form]', $block).find('[data-if-own]').removeAttr('readonly');
            } else {
                $('[data-unordered-form]', $block).find('[data-if-own]').attr('readonly', 'true');
            }
        }
        $block.find('.inactive input, .inactive select').attr('disabled', 'true');
        $block.find('.active input, .active select').removeAttr('disabled');
    }


    function updateInstallSection() {
        if ($('[data-block="repair"]').length) {
            $installSection.show();
        } else {
            $installSection.hide();
        }
    }


    if (window.location.href.indexOf('errors') != -1) {
        $form.trigger('submit');
    }
});


function API() {


    let params = {
        type: 'POST',
        dataType: 'json',
        cache: false,
        url: '?ajax=get-repair-types',
        error: function (jqXHR) {
            console.log('Ошибка сервера');
            console.log(jqXHR.responseText);
        }
    };


    function checkPartBalance(partID, cb) {
        let p = Object.assign({}, params);
        p.url = '?ajax=check-part-balance';
        p.data = `part_id=${partID}`;
        p.success = cb;
        $.ajax(p);
    }


    function saveForm(formElem, cb) {
        let p = Object.assign({}, params);
        p.processData = false;
        p.contentType = false;
        p.url = '?ajax=save-form';
        p.data = new FormData(formElem);
        p.success = cb;
        $.ajax(p);
    }


    function getRepairTypesByProblemID(problemID, partTypeID, cb) {
        partTypeID = (partTypeID) ? partTypeID : 0;
        let p = Object.assign({}, params);
        p.url = '?ajax=get-repair-types';
        p.data = `problem_id=${problemID}&part_type_id=${partTypeID}`;
        p.success = cb;
        $.ajax(p);
    }


    return {
        checkPartBalance,
        getRepairTypesByProblemID,
        saveForm
    };
}