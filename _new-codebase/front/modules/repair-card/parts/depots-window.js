/* global $ */

function DepotsWindow() {
    this.attachEvents();
}


DepotsWindow.prototype.attachEvents = function() {
    $('body').on('submit', '#receive-part-form', (event) => {
        event.preventDefault();
        this.receivePart(event.currentTarget);
    });
};

DepotsWindow.prototype.receivePart = function(formElem) {
    const data = new FormData(formElem);
    $.ajax({
        type: 'POST',
        dataType: 'json',
        cache: false,
        processData: false,
        contentType: false,
        error: function(jqXHR) {
            alert('К сожалению, произошла ошибка, пожалуйста, обратитесь к администратору.');
            console.log('Ошибка сервера');
            console.log(jqXHR.responseText);
        },
        url: '?ajax=receive-part',
        data: data,
        success: (resp) => {
            if (+resp['error_flag']) {
                alert(resp['message']);
                return;
            }
            if (this.cb) {
                this.cb();
            }
            this.close();
        }
    });
};

DepotsWindow.prototype.open = function(partID, orderID, cb = null) {
    this.cb = cb;
    $.fancybox.open({
        src: '?ajax=open-depots-window',
        type: 'ajax',
        clickSlide: false,
        clickOutside: false,
        ajax: {
            settings: {
                type: 'POST',
                data: {
                    fancybox: true,
                    part_id: partID,
                    order_id: orderID
                }
            }
        },
        afterLoad: function(instance, current) {
            current.$content.find('.select2').select2();
        }
    });
};

DepotsWindow.prototype.close = function() {
    $.fancybox.close();
};