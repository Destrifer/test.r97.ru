/* global $, request, savePartsWindow, confirmApproveWindow, repairForm */

document.addEventListener('DOMContentLoaded', function () {

    let jsonElem = document.getElementById('aside-controls-json');
    if (!jsonElem) {
        return;
    }
    const controlsData = JSON.parse(jsonElem.innerText);
    if (!controlsData.length) {
        return;
    }
    jsonElem = document.getElementById('repair-data-json');
    if (!jsonElem) {
        console.error('Данные ремонта не найдены.');
        return;
    }
    const API_URL = '/repair-api.php';
    const repair = JSON.parse(jsonElem.innerText);
    let blockFlag = false;

    renderControls(controlsData);


    function renderControls(controlsData) {
        let title = 'Отправить ремонт на проверку';
        if (controlsData[0] == 'approve') {
            title = 'Подтвердить ремонт';
        } else if (controlsData[0] == 'close') {
            title = 'Закрыть ремонт';
        }
        let tpl = `<img src="/_new-codebase/front/components/aside-controls/img/ok.png" title="${title}" data-action="${controlsData[0]}">`;
        if (controlsData[1] && !location.href.includes('step/6')) {
            tpl += `<img src="/_new-codebase/front/components/aside-controls/img/question.png" title="Перейти в поддержку" data-action="${controlsData[1]}">`;
        }
        document.body.insertAdjacentHTML('beforeEnd', `<div id="aside-controls" class="aside-controls">${tpl}</div>`);
    }


    $('body').on('click', '#aside-controls', function (event) {
        if (!event.target.dataset.action || blockFlag) {
            return;
        }
        blockFlag = true;
        switch (event.target.dataset.action) {
            case 'approve':
                onApprove(); // подтвердить админом
                break;

            case 'close': // закрыть ремонт мастером
                onClose();
                break;

            case 'check':
                onCheck(); // отправить на проверку СЦ
                break;

            case 'question':
                onQuestion(); // уйти в поддержку
                break;
        }
    });


    /* Отправить ремонт на проверку (для СЦ) */
    async function onCheck() {
        if (!confirm('Подтверждаете действие?')) {
            return;
        }
        if ((await hasFormErrors()) || (await hasCommonErrors()) || (await hasPartsErrors()) || (await hasFillErrors()) || (await hasPhotoErrors())) {
            blockFlag = false;
            return;
        }
        if (await needToSaveParts()) {
            savePartsWindow.open(repair.id);
            savePartsWindow.onSubmit = () => changeStatus('check');
        } else {
            changeStatus('check');
        }
        blockFlag = false;
    }


    /* Форма ремонта открыта и ждет сохранения */
    async function hasFormErrors() {
        let errorsFlag = false;
        const formElem = document.getElementById('repair-form');
        if (!formElem) {
            return false;
        }
        if (repairForm) { // существует форма ремонта
            const res = await repairForm.submit(formElem);
            errorsFlag = +res.error_flag;
        }
        /* if (document.getElementById('client-type')) { // вкладка Приемка
            const $repairForm = $(formElem);
            if (!$repairForm.valid()) {
                errorsFlag = true;
                $repairForm.trigger('submit');
            }
        }  */
        /* if (errorsFlag) {
            $.fancybox.open('<p>Пожалуйста, проверьте и сохраните заполненные данные перед отправкой ремонта.</p>');
        } */
        return errorsFlag;
    }


    /* Нужно сохранить оставляемые запчасти */
    async function needToSaveParts() {
        const res = await request.post(API_URL, { action: 'need-to-save-parts', repair_id: repair.id });
        return +res.need_to_save_parts_flag;
    }


    /* Ошибки недопустимости действия в текущем ремонте */
    async function hasCommonErrors() {
        const res = await request.post(API_URL, { action: 'check-common', repair_id: repair.id });
        if (+res.error_flag) {
            $.fancybox.open(`<p>${res.message}`);
            return true;
        }
        return false;
    }


    /* Неполученные заказы запчастей */
    async function hasPartsErrors() {
        const res = await request.post(API_URL, { action: 'check-parts', repair_id: repair.id });
        if (+res.error_flag) {
            $.fancybox.open(`<p>${res.message} <a href="${res.url}">Перейти к заказам</a></p>`);
            return true;
        }
        return false;
    }


    /* Ошибки заполнения данных ремонта */
    async function hasFillErrors() {
        const res = await request.post(API_URL, { action: 'check-repair', repair_id: repair.id });
        if (+res.error_flag) {
            $.fancybox.open(`<p>${res.message} <a href="${res.url}">Перейти на вкладку</a></p>`);
            return true;
        }
        return false;
    }


    /* Не загружены фото */
    async function hasPhotoErrors() {
        const res = await request.post(API_URL, { action: 'check-photos', repair_id: repair.id });
        if (+res.error_flag) {
            $.fancybox.open(`<p>Пожалуйста, загрузите необходимые фото. <a href="/edit-repair/${repair.id}/step/4/#error">Перейти к загрузке</a></p>`);
            return true;
        }
        return false;
    }


    /* Закрытие мастером */
    async function onClose() {
        if ((await hasFormErrors()) || (await hasFillErrors()) || (await hasPhotoErrors())) {
            blockFlag = false;
            return;
        }
        confirmApproveWindow.open(repair.id);
        confirmApproveWindow.onSubmit = async () => {
            if (await needToSaveParts()) {
                savePartsWindow.open(repair.id);
                savePartsWindow.onSubmit = () => changeStatus('approve');
            } else {
                changeStatus('approve');
            }
        };
        blockFlag = false;
    }


    /* Подтверждение админом */
    async function onApprove() {
        if ((await hasFormErrors()) || (await hasFillErrors())) {
            blockFlag = false;
            return;
        }
        confirmApproveWindow.open(repair.id);
        confirmApproveWindow.onSubmit = async () => {
            if (await needToSaveParts()) {
                savePartsWindow.open(repair.id);
                savePartsWindow.onSubmit = () => changeStatus('approve');
            } else {
                changeStatus('approve');
            }
        };
        blockFlag = false;
    }


    /* Уйти в поддержку */
    function onQuestion() {
        $('body').css('opacity', '.2');
        window.location.href = `/edit-repair/${repair.id}/step/6/#focus`;
    }


    function changeStatus(status) {
        let newStatus = (status == 'check') ? 'oncheck' : 'approve';
        $.get(`/ajax.php?type=update_repair_status&value=${newStatus}&id=${repair.id}`, function (resp) {
            if (resp && +resp.error_flag) {
                $.fancybox.open(`<p>${resp.message}</p>`);
            } else {
                document.location.replace('/dashboard/');
            }
        });
    }

});