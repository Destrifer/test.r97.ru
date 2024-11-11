/* global $ */
'use strict';

function ExtraInfoWindow() {
    this._attachEvents();
    this.order = null;
}


ExtraInfoWindow.prototype.errorCb = function(jqXHR) {
    alert('К сожалению, произошла ошибка, пожалуйста, обратитесь к администратору.');
    console.log('Ошибка сервера');
    console.log(jqXHR.responseText);
};


ExtraInfoWindow.prototype._attachEvents = function() {
    $('body').on('change', '[data-input="photo-file"]', (event) => {
        this.uploadPhoto(event.currentTarget);
    });
};


ExtraInfoWindow.prototype.uploadPhoto = function(fileElem) {
    const files = fileElem.files;
    if (typeof files == 'undefined') {
        alert('Файлы не выбраны.');
        return;
    }
    const $part = $(fileElem).closest('[data-part]');
    const $preview = $part.find('[data-elem="preview"]');
    const data = new FormData();
    $.each(files, function(key, value) {
        data.append(key, value);
    });
    data.append('part_id', $part.data('id'));
    const params = {
        type: 'POST',
        dataType: 'json',
        data: data,
        url: '?ajax=upload-photo&t=1',
        processData: false,
        contentType: false,
        cache: false,
        error: (jqXHR) => this.errorCb(jqXHR)
    };
    params.success = function(resp) {
        if (resp['message']) {
            alert(resp['message']);
            return;
        }
        $preview.html(`<img src="${resp['path']}">`);
        $part.find('[data-input="photo-path"]').val(resp['path']);
    };
    $.ajax(params);
};


ExtraInfoWindow.prototype.showManualPartWindow = function(order, repairID) {
    this.order = order;
    $.fancybox.open({
        src: '?ajax=get-manual-part-window&t=1',
        type: 'ajax',
        clickSlide: false,
        clickOutside: false,
        ajax: {
            settings: {
                type: 'POST',
                data: {
                    fancybox: true,
                    repair_id: repairID
                }
            }
        }
    });
};


ExtraInfoWindow.prototype.saveInfoWindow = function() {
    const formElem = document.getElementById('extra-info-window');
    if (formElem.classList.contains('wait')) {
        return;
    }
    formElem.classList.add('wait');
    const data = new FormData(formElem);
    const params = {
        type: 'POST',
        dataType: 'json',
        data: data,
        url: '?ajax=save-extra-window&t=1',
        processData: false,
        contentType: false,
        cache: false,
        error: (jqXHR) => this.errorCb(jqXHR)
    };
    params.success = (resp) => {
        if (resp['message']) {
            alert(resp['message']);
            return;
        }
        this.order.sendOrder();
    };
    $.ajax(params);
};


ExtraInfoWindow.prototype.saveManualPartWindow = function() {
    const formElem = document.getElementById('manual-part-window');
    if (formElem.classList.contains('wait')) {
        return;
    }
    formElem.classList.add('wait');
    const data = new FormData(formElem);
    const params = {
        type: 'POST',
        dataType: 'json',
        data: data,
        url: '?ajax=save-manual-part-window&t=1',
        processData: false,
        contentType: false,
        cache: false,
        error: (jqXHR) => this.errorCb(jqXHR)
    };
    params.success = (resp) => {
        if (resp['message']) {
            alert(resp['message']);
            return;
        }
        this.order.addManualPart(resp['part_id']);
        $.fancybox.close();
    }; 
    params.complete = function() {
        formElem.classList.remove('wait');
    };
    $.ajax(params);
};


ExtraInfoWindow.prototype.openExtraInfoWindow = function(commonPartIDs, order, repairID) {
    this.order = order;
    $.fancybox.open({
        src: '?ajax=get-extra-info-window&t=1',
        type: 'ajax',
        clickSlide: false,
        clickOutside: false,
        ajax: {
            settings: {
                type: 'POST',
                data: {
                    fancybox: true,
                    part_ids: commonPartIDs.join(','),
                    repair_id: repairID
                }
            }
        }
    });
};


ExtraInfoWindow.prototype.showPartWindow = function(part) {
    $.fancybox.open(part.$elem[0].querySelector('[data-part-modal]'));
};