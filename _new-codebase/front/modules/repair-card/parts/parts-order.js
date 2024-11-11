/* global $, Part */
'use strict';


function PartsOrder(partsOrderElem, extraInfoWindow, userRole, confirmWindow, sendWindow, storeWindow, depotsWindow, hasOtherOrders) {
    this.$partsOrder = $(partsOrderElem);
    this.$partsContainer = $(partsOrderElem.querySelector('[data-elem="parts-container"]'));
    this.$initialTitle = $(partsOrderElem.querySelector('[data-elem="initial-parts"]'));
    this.parts = {};
    this.emptyElem = partsOrderElem.querySelector('[data-elem="empty"]');
    this.controls = {
        reopen: partsOrderElem.querySelector('[data-elem="reopen-btn"]'),
        return: partsOrderElem.querySelector('[data-elem="return-btn"]'),
        cancel: partsOrderElem.querySelector('[data-elem="cancel-btn"]'),
        create: partsOrderElem.querySelector('[data-elem="create-btn"]'),
        takeParts: partsOrderElem.querySelector('[data-elem="take-parts-btn"]'),
        send: partsOrderElem.querySelector('[data-elem="send-btn"]'),
        save: partsOrderElem.querySelector('[data-elem="save-btn"]'),
        approve: partsOrderElem.querySelector('[data-elem="approve-btn"]'),
        receive: partsOrderElem.querySelector('[data-elem="receive-btn"]'),
        update: partsOrderElem.querySelector('[data-elem="update-btn"]'),
        delete: partsOrderElem.querySelector('[data-elem="delete-btn"]'),
    };
    this.userRole = userRole;
    this.extraInfoWindow = extraInfoWindow;
    this.orderID = +partsOrderElem.querySelector('[data-input="order-id"]').value;
    this.statusID = +partsOrderElem.querySelector('[data-input="status-id"]').value;
    this.confirmWindow = confirmWindow;
    this.sendWindow = sendWindow;
    this.storeWindow = storeWindow;
    this.depotsWindow = depotsWindow; // выбор складов при принятии запчасти
    this.hasOtherOrders = hasOtherOrders;
    this.params = {
        type: 'POST',
        dataType: 'json',
        cache: false,
        error: function (jqXHR) {
            alert('К сожалению, произошла ошибка, пожалуйста, обратитесь к администратору.');
            console.log('Ошибка сервера');
            console.log(jqXHR.responseText);
        }
    };
    this.paramsForm = Object.assign({
        processData: false,
        contentType: false
    }, this.params);
    this.attachEvents();
    this.initParts();
    this.updateView();
}


PartsOrder.prototype.attachEvents = function () {
    this.$partsOrder.on('click', '[data-action]', (event) => {
        event.preventDefault();
        const $trigger = $(event.target);
        if ($trigger.hasClass('disabled')) {
            return;
        }
        let curPart;
        switch ($trigger.data('action')) {

            case 'receive-order':
                if (confirm('Подтвердить получение заказа?')) {
                    this.receive();
                }
                break;

            case 'send-order':
                this.sendWindow.open(this.send.bind(this));
                break;

            case 'save-order': // обновление неотправленного заказа 
                this.saveOrder();
                break;

            case 'update-order': // обновление уже отправленного заказа
                this.update();
                break;

            case 'delete-order':
                this.delete();
                break;

            case 'approve-order':
                if (confirm('Подтвердить заказ?')) {
                    this.save();
                }
                break;

            case 'reopen-order':
                if (confirm('Отменить акт?')) {
                    this.reopen();
                }
                break;

            case 'create-order':
                if (!confirm('Отправить заказ?')) {
                    return;
                }
                if (this.hasOtherOrders(this.orderID)) {
                    this.confirmWindow.open(
                        `У вас есть другие неполученные заказы запчастей. 
                        <br>Нажмите кнопку "Получить запчасти", если они получены.  
                        <br>Укажите причину дозаказа запчасти.`,
                        (data, message) => {
                            this.save(message);
                        });
                } else {
                    this.save();
                }
                break;

            case 'cancel-order':
                if (this.statusID == 3) { // Store sent
                    if (!confirm('Вы уверены, что не хотите вернуть отправленные запчасти на Главный склад? При нажатии "Ok" запчасти будут оприходованы на склад Разбор данного сервиса.')) {
                        return;
                    }
                }
                this.confirmWindow.setValue('Заказанной запчасти нет в наличии и мы не можем её отправить в законные сроки ремонта, поэтому по данной карточке одобрен АНРП. Заполните карточку и отправьте нам на проверку нажав большую галочку во вкладке "Ремонт".');
                this.confirmWindow.open(
                    'Пожалуйста, введите сообщение для СЦ.',
                    (data, message) => {
                        this.cancel(message);
                    });
                break;

            case 'take-parts':
                this.takeParts(); // просто получить запчасти, если они на своих складах
                break;

            case 'return-order':
                if (confirm('Вернуть заказ в СЦ?')) {
                    this.confirmWindow.setValue('В списке запчастей есть требуемая вам запчасть. Просим выбрать её из списка и сделать запрос снова.');
                    this.confirmWindow.open(
                        'Пожалуйста, введите сообщение для сервиса.',
                        (_, message) => {
                            this.return(message);
                        });
                }
                break;

            case 'return-part':
                curPart = new Part($trigger.closest('[data-part]'));
                if (curPart.getDepotID() != 1) {
                    this.returnPart(curPart, '', $trigger);
                } else {
                    if (confirm('Вернуть запчасть складу?')) {
                        this.confirmWindow.data = curPart;
                        this.confirmWindow.open(
                            `Вы возвращаете заказанную и отправленную вам деталь обратно, 
                            для этого вам потребуется подготовить её к возврату и дождаться курьера. 
                            После этого закажите требуемую вам деталь. 
                            Пожалуйста, укажите причину возврата запчасти.`,
                            (part, message) => {
                                this.returnPart(part, message, $trigger);
                            });
                    }
                }
                break;

            case 'receive-part':
                if (confirm('Принять запчасть на склад?')) {
                    curPart = new Part($trigger.closest('[data-part]'));
                    this.depotsWindow.open(curPart.getID(), this.orderID, function () {
                        $trigger.addClass('disabled');
                        curPart.$elem.find('[data-elem="spinner"]').hide();
                    });
                }
                break;

            case 'cancel-part':
                this.cancelPart(new Part($trigger.closest('[data-part]')), $trigger);
                break;

            case 'open-store':
                this.openStore(new Part($trigger.closest('[data-part]')));
                break;

            case 'remove-part':
                curPart = new Part($trigger.closest('[data-part]'));
                if (!this.updatableStatus() && !curPart.isNew() && (this.userRole == 'admin' || this.userRole == 'store')) {
                    this.confirmWindow.data = curPart;
                    this.confirmWindow.open(
                        'Пожалуйста, укажите причину удаления запчасти.',
                        (part, message) => {
                            this.removePart(part, message);
                        });
                } else {
                    this.removePart(curPart);
                }
                break;
        }
    });
};


PartsOrder.prototype.updatableStatus = function () {
    return this.statusID > 2;
};


PartsOrder.prototype.initParts = function () {
    this.parts = {};
    const $parts = this.$partsContainer.find('[data-part]');
    if (!$parts.length) {
        return;
    }
    let parts = {};
    $parts.each(function () {
        let part = new Part($(this));
        parts[part.getURI()] = part;
    });
    this.parts = parts;
};


PartsOrder.prototype.scrollInto = function () {
    $('html, body').animate({
        scrollTop: this.$partsOrder.offset().top - 100
    });
};


PartsOrder.prototype.addStorePart = function (part) {
    this.state('wait');
    $.ajax(Object.assign({
        url: '?ajax=get-store-part-html',
        data: `part_id=${part.getID()}&depot_id=${part.getDepotID()}`,
        success: (resp) => {
            this.$initialTitle.before(resp['html']);
            document.dispatchEvent(new CustomEvent('partAddedToOrder'));
            this.initParts();
        },
        complete: () => {
            this.state('ready');
            this.updateView();
        }
    }, this.params));
};


PartsOrder.prototype.addManualPart = function (partID) {
    this.state('wait');
    $.ajax(Object.assign({
        data: `part_id=${partID}`,
        url: '?ajax=get-manual-part-html',
        success: (resp) => {
            this.$initialTitle.before(resp['html']);
            this.initParts();
        },
        complete: () => {
            this.state('ready');
            this.updateView();
        }
    }, this.params));
};


PartsOrder.prototype.removePart = function (part, message = '') {
    if (part.isNew() || this.updatableStatus()) {
        part.remove();
        this.initParts();
        this.updateView();
        return;
    }
    this.state('wait');
    $.ajax(Object.assign({
        url: '?ajax=del-part',
        data: `part_id=${part.getID()}&origin=${part.getOrigin()}&order_id=${this.orderID}&message=${message}`,
        success: (resp) => {
            if (+resp['error_flag']) {
                alert(resp['message']);
                return;
            }
            part.remove();
            this.initParts();
            this.updateView();
        },
        complete: () => {
            this.state('ready');
        }
    }, this.params));
};


PartsOrder.prototype.openStore = function (part) {
    this.storeWindow.open(part.getID(), this.orderID);
};


PartsOrder.prototype.cancelPart = function (part, $trigger) {
    const val = (+$trigger.data('value')) ? 0 : 1;
    this.state('wait');
    $.ajax(Object.assign({
        url: '?ajax=cancel-part',
        data: `cancel_flag=${val}&part_id=${part.getID()}&origin=${part.getOrigin()}&order_id=${this.orderID}`,
        success: (resp) => {
            if (+resp['error_flag']) {
                alert(resp['message']);
                return;
            }
            $trigger[0].setAttribute('data-value', val);
            if ($trigger.hasClass('active')) {
                $trigger.removeClass('active');
                part.undoCancel();
            } else {
                $trigger.addClass('active');
                part.cancel();
            }
        },
        complete: () => {
            this.state('ready');
        }
    }, this.params));
};


PartsOrder.prototype.returnPart = function (part, message, $trigger) {
    this.state('wait');
    $.ajax(Object.assign({
        url: '?ajax=return-part',
        data: `part_id=${part.getID()}&order_id=${this.orderID}&message=${message}`,
        success: (resp) => {
            if (+resp['error_flag']) {
                alert(resp['message']);
                return;
            }
            $trigger.addClass('disabled');
        },
        complete: () => {
            this.state('ready');
        }
    }, this.params));
};


PartsOrder.prototype.receive = function () {
    const orderID = this.orderID;
    this.state('wait');
    $.ajax(Object.assign({
        url: '?ajax=receive-order',
        data: `order_id=${orderID}`,
        success: (resp) => {
            if (+resp['error_flag']) {
                alert(resp['message']);
            } else {
                location.reload();
            }
        }
    }, this.params));
};


PartsOrder.prototype.reopen = function () {
    const orderID = this.orderID;
    this.state('wait');
    $.ajax(Object.assign({
        url: '?ajax=reopen-order',
        data: `order_id=${orderID}`,
        success: (resp) => {
            if (+resp['error_flag']) {
                alert(resp['message']);
            } else {
                location.reload();
            }
        }
    }, this.params));
};


PartsOrder.prototype.return = function (message) {
    const orderID = this.orderID;
    this.state('wait');
    $.ajax(Object.assign({
        url: '?ajax=return-order',
        data: `order_id=${orderID}&message=${message}`,
        success: (resp) => {
            if (+resp['error_flag']) {
                alert(resp['message']);
            } else {
                location.reload();
            }
        }
    }, this.params));
};


PartsOrder.prototype.saveOrder = function () {
    this.state('wait');
    const data = new FormData(this.$partsOrder[0]);
    $.ajax(Object.assign({
        url: '?ajax=edit-order',
        data: data,
        success: (resp) => {
            alert(resp['message']);
            if (!+resp['error_flag']) {
                location.reload();
            } else {
                this.state('ready');
            }
        }
    }, this.paramsForm));
};


PartsOrder.prototype.update = function () {
    this.state('wait');
    const data = new FormData(this.$partsOrder[0]);
    $.ajax(Object.assign({
        url: '?ajax=update-order',
        data: data,
        success: (resp) => {
            alert(resp['message']);
            if (!+resp['error_flag']) {
                location.reload();
            } else {
                this.state('ready');
            }
        }
    }, this.paramsForm));
};


PartsOrder.prototype.delete = function () {
    this.state('wait');
    const data = new FormData(this.$partsOrder[0]);
    $.ajax(Object.assign({
        url: '?ajax=delete-order',
        data: data,
        success: (resp) => {
            alert(resp['message']);
            if (!+resp['error_flag']) {
                location.reload();
            } else {
                this.state('ready');
            }
        }
    }, this.paramsForm));
};


PartsOrder.prototype.send = function (sendDate, transCompanyID, trackNum) { // отправка кладовщиком
    this.state('wait');
    const orderData = new FormData(this.$partsOrder[0]);
    orderData.append('send_date', sendDate);
    orderData.append('transport_company_id', transCompanyID);
    orderData.append('track_num', trackNum);
    $.ajax(Object.assign({
        url: '?ajax=send-order',
        data: orderData,
        success: (resp) => {
            alert(resp['message']);
            if (!+resp['error_flag']) {
                location.reload();
            } else {
                this.state('ready');
            }
        }
    }, this.paramsForm));
};


PartsOrder.prototype.save = function (message) {
    if (!isValid.call(this)) {
        return;
    }
    if (this.userRole != 'admin' && this.userRole != 'store') {
        const commonPartIDs = getCommonPartIDs.call(this); // в заказе общие запчасти (не оригинальные)
        if (commonPartIDs.length) {
            this.extraInfoWindow.openExtraInfoWindow(commonPartIDs, this, this.$partsOrder.find('[data-input="repair-id"]').val());
            return;
        }
    }
    this.sendOrder(message);


    function isValid() {
        const $qtys = this.$partsContainer.find('[data-input="qty"]');
        let validFlag = true;
        $qtys.each(function () {
            if (!+this.value) {
                validFlag = false;
                this.classList.add('form__input-error');
                return;
            }
            this.classList.remove('form__input-error');
        });
        return validFlag;
    }


    function getCommonPartIDs() {
        const $parts = this.$partsContainer.find('[data-part]');
        const ids = [];
        $parts.each(function () {
            let attrElem = this.querySelector('[data-input="attr-id"]');
            if (attrElem && +attrElem.value == 2) {
                ids.push(this.querySelector('[data-input="part-id"]').value);
            }
        });
        return ids;
    }
};


PartsOrder.prototype.isSent = function () {
    return this.statusID != 0;
};


PartsOrder.prototype.sendOrder = function (message = '') {
    this.state('wait');
    const data = new FormData(this.$partsOrder[0]);
    data.append('message', message);
    $.ajax(Object.assign({
        url: '?ajax=save-order',
        data: data,
        success: (resp) => {
            alert(resp['message']);
            if (!+resp['error_flag']) {
                location.reload();
            } else {
                this.state('ready');
            }
        }
    }, this.paramsForm));
};


PartsOrder.prototype.state = function (type) {
    if (type == 'wait') {
        this.$partsOrder.addClass('wait');
        return;
    }
    this.$partsOrder.removeClass('wait');
};


PartsOrder.prototype.cancel = function (message) {
    this.state('wait');
    const data = new FormData(this.$partsOrder[0]);
    data.append('message', message);
    $.ajax(Object.assign({
        data: data,
        url: '?ajax=cancel-order',
        success: (resp) => {
            if (+resp['error_flag']) {
                alert(resp['message']);
            } else {
                location.reload();
            }
        },
        complete: () => {
            this.state('ready');
        }
    }, this.paramsForm));
};


/* Получение запчастей мастером */
PartsOrder.prototype.takeParts = function () {
    if (this.userRole == 'service' && !confirm('Вы выбрали детали со своего склада Разбор, хранящиеся у вас по договору с нами. Подтверждаете их выбор для ремонта?')) {
        return;
    }
    this.state('wait');
    const data = new FormData(this.$partsOrder[0]);
    $.ajax(Object.assign({
        data: data,
        url: '?ajax=take-parts',
        success: (resp) => {
            if (+resp['error_flag']) {
                alert(resp['message']);
                this.state('ready');
            } else {
                location.href = `/edit-repair/${this.$partsOrder.find('[data-input="repair-id"]').val()}/step/2/`;
            }
        }
    }, this.paramsForm));
};


PartsOrder.prototype.hasPart = function (partURI) {
    if (this.parts[partURI] !== undefined) {
        return true;
    }
    return false;
};


/* Нумерация строк, пустой заказ и др. */
PartsOrder.prototype.updateView = function () {
    const $parts = this.$partsContainer.find('[data-part]');
    const self = this;
    /* Остаток на складе */
    if (this.userRole == 'admin' || this.userRole == 'store') {
        this.$partsOrder.find('[data-elem="depot-num"]').show();
    } else {
        this.$partsOrder.find('[data-elem="depot-num"]').remove();
    }
    /* Нет запчастей в заказе */
    if (!$parts.length) {
        this.emptyElem.style.display = '';
    } else {
        this.emptyElem.style.display = 'none';
        $parts.each(function () {
            let $part = $(this);
            self.updatePartControlsView($part, self.userRole, self.statusID);
            if (this.userRole != 'service') {
                $part.find('[data-elem="extra"]').show();
            } else {
                $part.find('[data-elem="extra"]').hide();
            }
        });
    }
    this.updateControlsView(this.userRole, this.statusID, $parts.length);
};


/* Запчасти только со склада "Разбор" */
PartsOrder.prototype.hasOnlyServiceParts = function () {
    const parts = this.$partsContainer[0].querySelectorAll('[data-part]');
    let part;
    for (let i = 0, len = parts.length; i < len; i++) {
        part = new Part($(parts[i]));
        if (part.getDepotID() == 1) {
            return false;
        }
    }
    return true;
};

PartsOrder.prototype.updateControlsView = function (userRole, statusID, partsCnt) {
    Object.keys(this.controls).map(key => { this.controls[key].style.display = 'none'; });
    let controlsToShow = [];
    if (!partsCnt) {
        return;
    }
    if (userRole == 'admin') {
        switch (statusID) {

            case 1: // Service sent
                controlsToShow = ['approve', 'cancel', 'return', 'save'];
                break;

            case 2: // Admin checked
                controlsToShow = ['send', 'cancel', 'return', 'save'];
                break;

            case 3: // Store sent
                controlsToShow = ['update', 'cancel', 'return'];
                break;

            case 4: // Service received
                controlsToShow = ['update', 'cancel'];
                break;

            case 5: // Canceled
                controlsToShow = ['reopen'];
                break;
        }
    } else if (userRole == 'master') {
        if (statusID == 0) {
            controlsToShow = ['takeParts'];
        } else {
            controlsToShow = ['delete'];
            this.blockChanges();
        }
    } else if (userRole == 'service') {
        switch (statusID) {
            case 0:
                if (this.hasOnlyServiceParts()) {
                    controlsToShow = ['takeParts'];
                } else {
                    controlsToShow = ['create'];
                }
                break;

            case 1:
            case 2:
                this.blockChanges();
                break;

            case 3:
                controlsToShow = ['receive'];
                this.blockChanges();
                break;

            case 4:
            case 5:
                this.blockChanges();
                break;

        }
    }
    controlsToShow.map((key) => {
        this.controls[key].style.display = '';
    });
};


PartsOrder.prototype.updatePartControlsView = function ($part, userRole, statusID) {
    const part = new Part($part);
    const controls = {
        'store-btn': () => ((userRole == 'admin' || userRole == 'store') && (statusID == 1 || statusID == 2) && part.getOrigin() == 'store'),
        'cancel-part-btn': () => ((userRole == 'admin' || userRole == 'store') && (statusID == 1 || statusID == 2)),
        'return-part-btn': () => (userRole == 'service' && statusID == 4 && part.getOrigin() == 'store'),
        'receive-part-btn': () => ((userRole == 'admin' || userRole == 'store') && +$part.data('return-flag') == 1)
    };
    let elem, name;
    for (name in controls) {
        elem = $part[0].querySelector(`[data-elem="${name}"]`);
        if (elem) {
            elem.style.display = controls[name]() ? '' : 'none';
        }
    }
};


PartsOrder.prototype.blockChanges = function () {
    this.$partsContainer.find('[data-input="qty"]').attr('disabled', true);
    this.$partsContainer.find('[data-elem="remove-btn"]').hide();
};