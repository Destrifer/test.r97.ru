/* global $ */

document.addEventListener('DOMContentLoaded', function() {

    const placeholderElem = document.getElementById('check-model-service-placeholder');
    if (!placeholderElem) {
        return;
    }
    const title = 'Проверка модели на обслуживаемость:';
    placeholderElem.innerHTML = `
                <div class="check-model-service" id="check-model-service">
                  <label id="check-model-service-message"  class="check-model-service__label" for="check-model-service-input">${title}</label>
                  <input type="search" list="models-list" id="check-model-service-input" placeholder="Название модели..." class="check-model-service__input">
                  <datalist id="models-list"></datalist>
                  </div>`;
    const widgetElem = document.getElementById('check-model-service');
    const messageElem = document.getElementById('check-model-service-message');
    const inputElem = document.getElementById('check-model-service-input');
    const datalist = document.getElementById('models-list');
    let loadedFlag = false;
    let loadingFlag = false;

    $(inputElem).on('focus', function() {
        if (loadedFlag) {
            return;
        }
        loadedFlag = true;
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '/ajax.php?type=get-all-models',
            success: function(resp) {
                let html = '';
                for (let model of Object.values(resp)) {
                    html += `<option value="${model}">`;
                }
                datalist.innerHTML = html;
            },
        });
    });


    $(inputElem).on('change', function() {
        const model = this.value.trim();
        if (loadingFlag) {
            return;
        }
        if (model.length < 7) {
            reset();
            return;
        }
        loadingFlag = true;
        messageElem.innerText = 'Проверка...';
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '/ajax.php?type=check-model-service',
            data: `model=${model}`,
            success: function(resp) {
                messageElem.innerText = resp.message;
                if (+resp.service_flag) {
                    widgetElem.classList.remove('no');
                    widgetElem.classList.add('yes');
                } else {
                    widgetElem.classList.remove('yes');
                    widgetElem.classList.add('no');
                }
            },
            complete: () => loadingFlag = false
        });
    });


    function reset() {
        widgetElem.classList.remove('yes');
        widgetElem.classList.remove('no');
        messageElem.innerText = title;
    }

});