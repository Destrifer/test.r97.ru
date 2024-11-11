/* global $ */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const $serialTpl = $('#serial-tpl').detach();
    const $modelTpl = $('#model-tpl').detach();
    const $depotTpl = $('#depot-tpl').detach();
    const delPhotoPaths = [];
    const delModelIDs = [];
    const delSerialIDs = [];
    const delDepotIDs = [];
    const $notif = $('#form-notif');
    const nameIDInput = document.querySelector('[data-input="name-id"]');
    const nameExtraInput = document.querySelector('[data-input="extra-name"]');
    const nameInput = document.querySelector('[data-input="name"]');
    const isCountedInput = document.querySelector('[data-input="extra-is-counted"]');
    const modelIDInput = document.querySelector('[data-input="extra-model-id"]');
    const catIDInput = document.querySelector('[data-input="extra-cat-id"]');
    let blockFlag = false;


    initSelect2($('.select2'));
    initDatepicker($('[data-datepicker]'));


    const errorCb = function (jqXHR) {
        console.error('Ошибка сервера');
        console.error(jqXHR.responseText);
    };


    initDepots();
    updateForm();


    $('#part-form').on('submit', function () {
        if (blockFlag) {
            return;
        }
        blockFlag = true;
        if (delPhotoPaths.length) {
            $('#del-photo-paths').val(JSON.stringify(delPhotoPaths));
        }
        if (delModelIDs.length) {
            $('#del-model-ids').val(JSON.stringify(delModelIDs));
        }
        if (delSerialIDs.length) {
            $('#del-serial-ids').val(JSON.stringify(delSerialIDs));
        }
        if (delDepotIDs.length) {
            $('#del-depot-ids').val(JSON.stringify(delDepotIDs));
        }
        const data = new FormData(this);
        const submitBtn = this.querySelector('[type="submit"]');
        submitBtn.innerText = 'Сохранение...';
        $.ajax({
            type: 'POST',
            url: '?ajax',
            dataType: 'json',
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            success: function (resp) {
                submitBtn.innerText = 'Сохранить';
                if (resp.errorFlag == 1) {
                    $notif.addClass('error').html(resp.message).fadeIn();
                    return;
                }
                if (resp.redir_url) {
                    location.href = resp.redir_url;
                }
                $('[data-history]').remove();
                $notif.removeClass('error').html(resp.message).fadeIn();
                setTimeout(() => {
                    $notif.fadeOut();
                }, 3000);
            },
            complete: function () {
                blockFlag = false;
            },
            error: errorCb
        });
        return false;
    });


    $('body').on('change', '[data-input]', function () {
        switch (this.dataset.input) {
            case 'name-id':
            case 'extra-name':
            case 'extra-model-id':
            case 'extra-is-counted':
            case 'extra-cat-id':
                updateName();
                break;

            case 'attr-id':
                updateForm();
                break;

            case 'photo-file':
                uploadPhoto(this);
                break;

            case 'serial':
                setSerial(this.value, $(this).closest('[data-serial-id]'));
                break;

            case 'model':
                setModel(this.value, $(this).closest('[data-model-id]'));
                break;
        }
    });


    function updateName() {
        let name = '';
        let extraName = nameExtraInput.value.trim();
        let catName = catIDInput.options[catIDInput.selectedIndex].text;
        let modelName = modelIDInput.options[modelIDInput.selectedIndex].text;
        let tplName = nameIDInput.options[nameIDInput.selectedIndex].text;
        if (modelIDInput.value) {
            name = '(' + modelName.trim() + ')';
        } else if (catIDInput.value) {
            name = '(' + catName.trim() + ')';
        }
        if (extraName) {
            name = (extraName + ' ' + name).trim();
        }
        if (nameIDInput.value) {
            tplName = tplName.substring(0, tplName.length - 1); // удаление правой скобки
            name = (tplName + ' ' + name).trim() + ')';
        }
        if (isCountedInput.checked) {
            name = name + '*';
        }
        nameInput.value = name;
    }


    function uploadPhoto(fileElem) {
        const files = fileElem.files;
        if (typeof files == 'undefined') {
            alert('Файлы не выбраны.');
            return;
        }
        const data = new FormData();
        $.each(files, function (key, value) {
            data.append(key, value);
        });
        data.append('ajax', 'upload-photo');
        const params = {
            type: 'POST',
            dataType: 'json',
            data: data,
            url: '?ajax',
            processData: false,
            contentType: false,
            cache: false,
            error: errorCb,
        };
        params.success = function (resp) {
            if (resp['message']) {
                alert(resp['message']);
                return;
            }
            $('#photos-container .photos__item_add-btn').before(resp['photo_html']);
        };
        $.ajax(params);
    }


    /* Установка свойств блока для выбранной модели */
    function setModel(modelID, $modelBlock) {
        if (!modelID) {
            return;
        }
        cleanModelBlock($modelBlock);
        $modelBlock.attr('data-model-id', modelID);
    }


    /* Установка свойств блока для выбранного номера */
    function setSerial(serialID, $serialBlock) {
        $serialBlock.attr('data-serial-id', serialID);
        $serialBlock.find('[data-input="serial-id"]').val(serialID);
        if (!serialID) {
            return;
        }
        serialUsed(serialID, $serialBlock);
    }


    /* Удаление серийных номеров */
    function cleanModelBlock($modelBlock) {
        $modelBlock.find('[data-serial-id]').remove();
    }


    /* Добавляет select с номерами */
    function addSerialsSelect($serial) {
        const modelID = $serial.closest('[data-model-id]').attr('data-model-id');
        const selectID = `serials-select-${modelID}`;
        let $select = $(`#${selectID}`);
        if ($select.length) {
            placeSelect($select, $serial);
            return;
        }
        $select = $(`<select data-input="serial" style="display: none" id="${selectID}" class="form__select select2"></select>`);
        $select.append('<option value="">- номер не выбран -</option>');
        $.ajax({
            type: 'POST',
            url: '?ajax',
            dataType: 'json',
            data: `ajax=get-serials&model_id=${modelID}`,
            success: function (resp) {
                if (resp) {
                    for (let i = 0, len = resp.length; i < len; i++) {
                        $select.append(`<option value="${resp[i]['id']}">${resp[i]['full_model_serial']}</option>`);
                    }
                }
                $('body').append($select);
                placeSelect($select, $serial);
            },
            error: errorCb
        });


        function placeSelect($select, $serial) {
            let $newSelect = $select.clone();
            $newSelect.attr('id', '');
            $serial.find('[data-serial-holder]').replaceWith($newSelect);
            initSelect2($newSelect);
        }
    }


    /* Данный номер уже введен в других полях */
    function serialUsed(serialID, $serial) {
        const $serials = $serial.closest('[data-model-id]').find(`[data-serial-id="${serialID}"]`);
        if ($serials.length > 1) {
            $serials.addClass('model__repeat-error');
            setTimeout(() => {
                $serials.removeClass('model__repeat-error');
            }, 3500);
            return true;
        }
        return false;
    }


    $('body').on('click', '[data-action]', function (event) {
        event.preventDefault();
        switch (this.dataset.action) {
            case 'check-all':
                $('[name="cat_id[]"]').prop('checked', true);
                break;

            case 'uncheck-all':
                $('[name="cat_id[]"]').prop('checked', false);
                break;

            case 'open-cats-window':
                openCatsWindow();
                break;

            case 'save-cats-window':
                saveCatsWindow();
                break;

            case 'close-cats-window':
                $.fancybox.close();
                break;

            case 'del-photo':
                delPhoto($(this).closest('[data-photo]'));
                break;

            case 'del-depot':
                delDepot($(this).closest('[data-depot-id]'));
                break;

            case 'del-model':
                delModel($(this).closest('[data-model-id]'));
                break;

            case 'del-serial':
                delSerial($(this).closest('[data-serial-id]'));
                break;

            case 'add-serial':
                if (!$(this).closest('[data-model-id]').attr('data-model-id')) {
                    alert('Пожалуйста, введите название модели.');
                    return;
                }
                addSerialBlock($(this).closest('[data-trigger]'));
                break;

            case 'add-all-serials':
                addAllSerials($(this).closest('[data-model-id]'));
                break;

            case 'add-model':
                addModelBlock($(this).closest('[data-trigger]'));
                break;

            case 'add-depot':
                addDepotBlock($(this).closest('[data-trigger]'));
                break;

            case 'rotate-photo':
                rotatePhoto($(this).closest('[data-photo]'), $(this));
                break;

        }
    });


    function saveCatsWindow() {
        const partID = document.getElementById('part-id').value;
        const data = new FormData(document.getElementById('cats-window-form'));
        data.append('ajax', 'save-cats-window');
        data.append('part_id', partID);
        $.ajax({
            type: 'POST',
            url: '?ajax',
            dataType: 'json',
            processData: false,
            contentType: false,
            cache: false,
            data: data,
            error: function () {
                alert('При сохранении произошла ошибка, пожалуйста, обратитесь к администратору.');
                errorCb();
            }
        });
        $.fancybox.close();
    }


    function openCatsWindow() {
        const partID = document.getElementById('part-id').value;
        if (!partID) {
            alert('Пожалуйста, сохраните запчасть перед выбором категорий.');
            return;
        }
        $.fancybox.open({
            src: '?',
            type: 'ajax',
            clickSlide: false,
            clickOutside: false,
            ajax: {
                settings: {
                    type: 'POST',
                    data: {
                        fancybox: true,
                        part_id: partID,
                        ajax: 'get-cats-window'
                    }
                }
            }
        });
    }


    function rotatePhoto($photo, $trigger) {
        if (blockFlag) {
            return;
        }
        blockFlag = true;
        $photo.addClass('wait');
        const direction = $trigger.data('direction');
        const path = $photo.find('[data-photo-path]').val();
        $.ajax({
            type: 'POST',
            url: '?ajax',
            dataType: 'json',
            data: `ajax=rotate-photo&direction=${direction}&photo_path=${path}`,
            success: function (resp) {
                if (+resp.error_flag) {
                    alert(resp.message);
                    return;
                }
                const src = `${resp['path']}?t=${Math.random()}`;
                const $img = $photo.find('[data-photo-img]');
                const $newImg = $img.clone();
                $newImg.attr('src', src);
                $img.after($newImg);
                $img.remove();
                $photo.find('[data-photo-link]').attr('href', src);
                $photo.find('[data-photo-path]').val(resp['path']);
            },
            complete: function () {
                blockFlag = false;
                $photo.removeClass('wait');
            },
            error: errorCb
        });
    }


    function delDepot($depot) {
        const depotID = $depot.attr('data-depot-id');
        if (depotID) {
            if (!confirm('Удалить данные о складе? Изменения вступят в силу после нажатия кнопки "Сохранить".')) {
                return;
            }
            delDepotIDs.push(depotID);
        }
        $depot.fadeOut(300, function () {
            this.remove();
        });
    }


    function delSerial($serial) {
        const serialID = $serial.attr('data-serial-id');
        if (serialID) {
            if (!confirm('Удалить номер? Изменения вступят в силу после нажатия кнопки "Сохранить".')) {
                return;
            }
            delSerialIDs.push(serialID);
        }
        $serial.fadeOut(300, function () {
            this.remove();
        });
    }


    function delModel($model) {
        const modelID = $model.attr('data-model-id');
        if (modelID) {
            if (!confirm('Удалить модель? Изменения вступят в силу после нажатия кнопки "Сохранить".')) {
                return;
            }
            delModelIDs.push(modelID);
        }
        $model.fadeOut(300, function () {
            this.remove();
        });
    }


    function delPhoto($photo) {
        if (!confirm('Удалить фото? Изменения вступят в силу после нажатия кнопки "Сохранить".')) {
            return;
        }
        delPhotoPaths.push($photo.find('[data-photo-path]').val());
        $photo.fadeOut(300, function () {
            this.remove();
        });
    }


    /* Добавить все номера модели */
    function addAllSerials($model) {
        const modelID = $model.attr('data-model-id');
        $.ajax({
            type: 'POST',
            url: '?ajax',
            dataType: 'json',
            data: `ajax=get-all-serials-html&model_id=${modelID}`,
            success: function (resp) {
                $model.find('[data-serials]').html(resp['serials_html']);
            },
            error: errorCb
        });
    }


    function addDepotBlock($trigger) {
        const $newDepot = $depotTpl.clone();
        $newDepot.css('display', 'none');
        $trigger.before($newDepot);
        $newDepot.fadeIn();
        initSelect2($('.select2', $newDepot));
        return $newDepot;
    }


    function addModelBlock($trigger) {
        const $newModel = $modelTpl.clone();
        $newModel.css('display', 'none');
        $trigger.before($newModel);
        $newModel.fadeIn();
        initSelect2($('.select2', $newModel));
    }


    function addSerialBlock($trigger) {
        const $newSerial = $serialTpl.clone();
        $newSerial.css('display', 'none');
        $trigger.before($newSerial);
        $newSerial.fadeIn();
        addSerialsSelect($newSerial);
    }


    function initDepots() {
        if (document.querySelector('[data-depot-id]')) {
            return;
        }
        addDepotBlock($('[data-action="add-depot"]').closest('[data-trigger]'));
    }


    function updateForm() {
        const val = document.getElementById('attr-id').value;
        const link = document.getElementById('open-cats-window-link');
        if (val == 2) { // стандартная запчасть, показать ссылку категорий
            link.style.display = '';
        } else {
            link.style.display = 'none';
        }
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
            maxDate: new Date()
        });
    }

});