/* global $, PartsList, Part, PartsOrder, Filter, ExtraInfoWindow, DepotsWindow, ConfirmWindow, SendWindow, StoreWindow */

document.addEventListener('DOMContentLoaded', function () {


    const confirmWindow = new ConfirmWindow(document.getElementById('confirm-window'));
    const sendWindow = new SendWindow(document.getElementById('send-window'));
    const storeWindow = new StoreWindow();
    const depotsWindow = new DepotsWindow();
    const userData = JSON.parse($('#user-data-json').text());
    const extraInfoWindow = new ExtraInfoWindow();
    const repairID = $('body').find('[data-input="repair-id"]').val();
    const orders = [];
    const elems = document.querySelectorAll('[data-order]');
    for (let i = 0, len = elems.length; i < len; i++) {
        orders.push(new PartsOrder(elems[i], extraInfoWindow, userData['role'], confirmWindow, sendWindow, storeWindow, depotsWindow, hasOtherOrders));
    }
    const list = new PartsList(document.getElementById('parts-list'), (orders[0] ? orders[0] : null), userData['role']);
    const filter = new Filter(list);

    filter.loadParts();


    /* Определяет, есть ли другие неполученные заказы */
    function hasOtherOrders(curOrderID) {
        for (let i = 0; i < orders.length; i++) {
            if (orders[i].orderID != curOrderID && orders[i].statusID == 3) { // store sent
                return true;
            }
        }
        return false;
    };


    $('body').on('click', '[data-action]', function (event) {
        event.preventDefault();
        const $trigger = $(this);
        switch (this.dataset.action) {

            case 'save-window':
                extraInfoWindow.saveInfoWindow();
                break;

            case 'add-manual-part':
                extraInfoWindow.saveManualPartWindow();
                break;

            case 'open-manual-part-window':
                extraInfoWindow.showManualPartWindow(orders[0], repairID);
                break;

            case 'show-extra':
                extraInfoWindow.showPartWindow(new Part($trigger.closest('[data-part]')));
                break;

            case 'close-window':
                $.fancybox.close();
                break;

            case 'order-part':
                list.orderPart(new Part($trigger.closest('[data-part]')));
                break;
        }
    });

});