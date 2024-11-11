/* global $ */

document.addEventListener('DOMContentLoaded', function () {

    const placeholderElem = document.getElementById('check-serial-placeholder');
    if (!placeholderElem) {
        return;
    }
    const resultPopup = `<div id="check-serial-popup" class="check-serial-popup">
                            <table>
                                <tbody id="check-serial-table">
                                    
                                </tbody>
                            </table>
                         </div>`;
    const title = 'Проверка серийного номера:';
    placeholderElem.innerHTML = `
                <div class="check-serial" id="check-serial">
                  <label id="check-serial-message"  class="check-serial__label" for="check-serial-input">${title}</label>
                  <input type="search" id="check-serial-input" placeholder="Серийный номер..." class="check-serial__input">
                  ${resultPopup}  
                </div>`;
    const widgetElem = document.getElementById('check-serial');
    const messageElem = document.getElementById('check-serial-message');
    const inputElem = document.getElementById('check-serial-input');
    const resultPopupElem = document.getElementById('check-serial-popup');
    const resultTableElem = document.getElementById('check-serial-table');
    let loadingFlag = false;
    let timeoutID = null; // для ввода при поиске


    document.body.addEventListener('click', function (event) {
        if (!event.target.closest('#check-serial')) {
            closePopup();
        }
    });

    $(inputElem).on('change', function () {
        const serial = this.value.trim();
        if (serial.length < 6) {
            messageElem.innerText = 'Проверка серийного номера:';
        }
    });

    $(inputElem).on('click', function () {
        if (loadingFlag) {
            return;
        }
        const serial = this.value.trim();
        if (serial.length < 6) {
            return;
        }
        openPopup();
    });

    $(inputElem).on('input', function () {
        if (loadingFlag) {
            return;
        }
        const serial = this.value.trim();
        if (serial.length < 6) {
            return;
        }
        if (timeoutID) {
            clearTimeout(timeoutID);
        }
        timeoutID = setTimeout(function () {
            timeoutID = null;
            loadingFlag = true;
            messageElem.innerText = 'Проверка...';
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '/ajax.php?type=check-serial',
                data: `serial=${serial}`,
                success: function (resp) {
                    if (!resp.length) {
                        messageElem.innerText = 'Ремонты не найдены';
                        closePopup('');
                        return;
                    }
                    let html = '';
                    for (let i = 0; i < resp.length; i++) {
                        html += applyTPL(resp[i]);
                    }
                    messageElem.innerText = 'Ремонтов: ' + resp.length;
                    openPopup(html);
                },
                complete: () => loadingFlag = false
            });
        }, 1000);
    });


    function openPopup(html) {
        if (html) {
            resultTableElem.innerHTML = html;
        }
        resultPopupElem.classList.add('open');
    }


    function closePopup(html) {
        if (!resultPopupElem.classList.contains('open')) {
            return;
        }
        if (html !== undefined) {
            resultTableElem.innerHTML = `<tr class="check-serial-popup__head">
                                            <td colspan="100" class="check-serial-popup__value">Ремонты не найдены</td>
                                         </tr>`;
        }
        resultPopupElem.classList.remove('open');
    }


    function applyTPL(data) {
        const keys = Object.keys(data);
        let tpl = rowTPL;
        data['serial'] = data['serial'].replace(inputElem.value, `<span class="check-serial-popup__highlight">${inputElem.value}</span>`)
        keys.forEach(key => {
            tpl = tpl.replace('{' + key + '}', data[key]);
        });
        return tpl;
    }


    const rowTPL = `<tr class="check-serial-popup__head">
    <td colspan="100"><a href="{url}" target="_blank" class="check-serial-popup__value check-serial-popup__value_id">Ремонт №{id}</a> <span class="check-serial-popup__value check-serial-popup__value_serial">{serial}</span></td>
</tr>
<tr>
    <td>
        <span class="check-serial-popup__name">СЦ:</span>
        <span class="check-serial-popup__value">{service}</span></td>
    <td colspan="2">
        <span class="check-serial-popup__name">Мастер:</span>
        <span class="check-serial-popup__value">{master}</span></td>
</tr>
<tr>
    <td>
        <span class="check-serial-popup__name">Модель:</span>
        <span class="check-serial-popup__value">{model}</span></td>
    <td>
        <span class="check-serial-popup__name">Тип ремонта:</span>
        <span class="check-serial-popup__value">{type}</span></td>
    <td>
        <span class="check-serial-popup__name">Итог ремонта:</span>
        <span class="check-serial-popup__value">{result}</span>
    </td>
</tr>
<tr>
    <td>
        <span class="check-serial-popup__name">Дата приема:</span>
        <span class="check-serial-popup__value">{receive_date}</span></td>
    <td>
        <span class="check-serial-popup__name">Дата продажи:</span>
        <span class="check-serial-popup__value">{sell_date}</span>
    </td>
    <td>
        <span class="check-serial-popup__name">Дата готовности:</span>
        <span class="check-serial-popup__value">{ready_date}</span>
    </td>
</tr>`;

});