/* global $ */

document.addEventListener('DOMContentLoaded', function () {

    let $select = $('#models-select');

    if (!$select.length) {
        return;
    }

    let $modelsSearchInput = $('#models-search');
    let $modelsResult = $('#models-search-result');
    let $serialsSearchInput = $('#serials-search');
    let $serialsResult = $('#serials-search-result');
    let $selectedModels = $('#selected-models');
    let $curFileID = $('#cur-file-id');
    let $curSerialID = $('#cur-serial-id');
    let $selectAllFlag = $('#select-all-flag');
    let $curModel = null;
    let modelName = $select.find('[data-name="model"]').text();
    let curData = {};


    $('body').on('SelectModelsOpen', function () {
        loadCurData();
        $serialsSearchInput.val('');
    });


    function loadCurData() {
        let data = new FormData();
        data.append('serial_id', $curSerialID.val());
        data.append('file_id', $curFileID.val());
        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: data,
            url: '?ajax=load-cur-data',
            processData: false,
            contentType: false,
            cache: false,
            success: function (response) {
                $select.find('[data-name="provider"]').text(response.serial.provider);
                $select.find('[data-name="order"]').text(response.serial.order);
                curData = response;
                $modelsSearchInput.val($modelsSearchInput.data('default')).trigger('input');
            },
            error: ajaxError
        });
    }


    $modelsSearchInput.on('input', function () {
        let data = new FormData();
        if (!this.value.length) {
            $modelsResult.html('');
            $serialsResult.html('');
            return;
        }
        data.append('request', this.value);
        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: data,
            url: '?ajax=search',
            processData: false,
            contentType: false,
            cache: false,
            success: function (response) {
                let html = '';
                response.forEach(function (row) {
                    html += '<li class="models-select__result-item" data-select-models-item data-select-models-id="' + row['id'] + '">' + row['name'] + '</li>';
                });
                $modelsResult.html(html);
                $modelsResult.children().each(function () {
                    if (this.innerText === modelName) {
                        this.click();
                        return false;
                    }
                });
            },
            error: ajaxError
        });
    });


    $serialsSearchInput.on('input', function () {
        let $elems = $serialsResult.children();
        let request = this.value.toLowerCase();
        if (!request.length) {
            $elems.show();
            return;
        }
        $elems.each(function (index, el) {
            let serial = el.querySelector('[data-search="serial"]').innerText.toLowerCase();
            let order = el.querySelector('[data-search="order"]').innerText.toLowerCase();
            if (serial.includes(request) || order.includes(request)) {
                el.style.display = '';
                return;
            }
            el.style.display = 'none';
        });
    });


    $select.on('click', '[data-select-models-item]', function () {
        let activeFlag = (this.classList.contains('active')) ? 1 : 0;
        $('[data-select-models-item]', $select).removeClass('active');
        if (!activeFlag) {
            this.classList.add('active');
            $curModel = $(this);
            loadSerials(this.dataset.selectModelsId);
        } else {
            $curModel = null;
            $serialsResult.html('');
        }
    });



    $select.on('change', '[data-select-models-flag="order"]', function () {
        let serial = this.closest('[data-select-models-serial]').dataset.selectModelsSerial;
        let order = this.closest('[data-select-models-order]').dataset.selectModelsOrder;
        let model = $curModel.text();
        let id = this.value;
        let $curItem = $selectedModels.find('#selected-item-' + id);
        if (this.checked) {
            if (!$curItem.length) {
                $selectedModels.prepend('<div id="selected-item-' + id + '" data-id="' + id + '" class="models-select__selected-item"><b>' + model + '</b>, номер: ' + serial + ', заказ: ' + order + '</div>');
            }
        } else {
            $curItem.remove();
        }
    });


    $selectAllFlag.on('change', function () {
        let newState = this.checked;
        let $elems = $serialsResult.find('[data-select-models-flag]');
        $elems.each(function (i, checkbox) {
            if (!checkbox.disabled) {
                checkbox.checked = newState;
                $(checkbox).change();
            }
        });
    });


    $('[data-select-models-action]').on('click', function () {
        if (this.dataset.selectModelsAction == 'save') {
            let $selected = $selectedModels.children();
            if ($selected.length) {
                let data = new FormData();
                $selected.each(function () {
                    data.append('serial_id[]', this.dataset.id);
                });
                data.append('file_id', $('#cur-file-id').val());
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    url: '?ajax=save-models',
                    processData: false,
                    contentType: false,
                    cache: false,
                    error: ajaxError,
                    complete: () => {
                        location.reload();
                    }
                });
                return;
            }
        }
        parent.$.fancybox.close();
    });


    function loadSerials(modelID) {
        let data = new FormData();
        data.append('model_id', modelID);
        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: data,
            url: '?ajax=load-serials',
            processData: false,
            contentType: false,
            cache: false,
            success: function (response) {
                $serialsResult.html('');
                $selectAllFlag[0].checked = false;
                if (!response) {
                    alert('Серийных номеров не найдено.');
                    return;
                }
                let html = '';
                let checked = '';
                response.forEach(function (row) {
                    checked = (curData.serials.indexOf(row['id']) == -1) ? '' : 'checked disabled';
                    html += '<label class="models-select__result-item" data-select-models-serial="' + row['serial'] + '" data-select-models-order="' + row['order'] + '"><input type="checkbox" ' + checked + ' data-select-models-flag="order" value="' + row['id'] + '"> <span data-search="serial">' + row['serial'] + '</span>, завод: <span data-search="provider">' + row['provider'] + '</span>, заказ: <span data-search="order">' + row['order'] + '</span></label>';
                });
                $serialsResult.html(html);
            },
            error: ajaxError
        });
    }

    function ajaxError(jqXHR) {
        alert('При запросе произошла ошибка.');
        console.log('Ошибка сервера');
        console.log(jqXHR.responseText);
    }
});