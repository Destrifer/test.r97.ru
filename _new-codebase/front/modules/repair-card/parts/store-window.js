/* global $ */

function StoreWindow() {
    this.attachEvents();
}


StoreWindow.prototype.attachEvents = function() {
    $('body').on('click', '[data-on-click]', (event) => {
        event.preventDefault();
        switch (event.target.dataset.onClick) {
            case 'replace-depot':
                if (confirm('Подтверждаете замену склада?')) {
                    this.replaceDepot(event.target);
                }
                break;
        }
    });
};

StoreWindow.prototype.replaceDepot = function(triggerElem) {
    const partID = triggerElem.dataset.partId;
    const depotID = triggerElem.dataset.depotId;
    const orderID = triggerElem.dataset.orderId;
    const num = triggerElem.closest('[data-part-row]').querySelector('[data-input="num"]').value;
    $.ajax({
        type: 'POST',
        dataType: 'json',
        cache: false,
        error: function(jqXHR) {
            alert('К сожалению, произошла ошибка, пожалуйста, обратитесь к администратору.');
            console.log('Ошибка сервера');
            console.log(jqXHR.responseText);
        },
        url: '?ajax=replace-depot',
        data: `part_id=${partID}&depot_id=${depotID}&order_id=${orderID}&num=${num}`,
        success: (resp) => {
            if (+resp['error_flag']) {
                alert(resp['message']);
                return;
            }
            location.reload();
        }
    });
};

StoreWindow.prototype.open = function(partID, orderID) {
    $.fancybox.open({
        src: '?ajax=open-store',
        type: 'ajax',
        clickSlide: false,
        clickOutside: false,
        ajax: {
            settings: {
                type: 'POST',
                data: {
                    fancybox: true,
                    part_id: partID,
                    order_id: orderID,
                }
            }
        }
    });
};

StoreWindow.prototype.close = function() {
    $.fancybox.close();
};