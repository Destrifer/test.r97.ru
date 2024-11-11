/* global $, Part */

function PartsList(partsListElem, partsOrder, userRole) {
    this.$partsList = $(partsListElem);
    this.partsOrder = partsOrder;
    this.blockFlag = false;
    this.userRole = userRole;
    this._attachEvents();
}


PartsList.prototype._attachEvents = function() {

    let $popup;


    this.$partsList.on('click', '[data-action]', function(event) {
        event.preventDefault();
        let part;
        switch(this.dataset.action){
             
            case 'open-log':
                part = new Part($(this).closest('[data-part]'));
                window.open(`/parts-log/?part=${part.getID()}&depot=${part.getDepotID()}`, '_blank').focus();
                break;

            case 'open-info':
                if($popup){
                    $popup.fadeOut();
                }
                $popup = $(this).next('[data-elem="popup"]');
                $popup.fadeIn(200);
                break;
        }
       
    });

    
    $('body').on('click', function(event) {
        const elem = event.target;
        if(elem.dataset.elem && elem.dataset.elem == 'popup' || elem.dataset.action && elem.dataset.action == 'open-info'){
            return;
        }
        if($popup){
            $popup.fadeOut(100);
            $popup = null;
        }    
    });

    $(document).on('partAddedToOrder', () => {
        this.blockFlag = false;
        this.$partsList.removeClass('wait');
        this.updateView();
    });
};


PartsList.prototype.getParts = function() {
    return this.$partsList[0].querySelectorAll('[data-part]');
};


/* Добавить запчасть в заказ */
PartsList.prototype.orderPart = function(part) {
    if (this.partsOrder.hasPart(part.getURI())) {
        alert('Запчасть уже есть в заказе.');
        return;
    }
    if (this.blockFlag) {
        return;
    }
    this.partsOrder.scrollInto();
    this.blockFlag = true;
    this.$partsList.addClass('wait');
    this.partsOrder.addStorePart(part);
};


PartsList.prototype.updateView = function() {

    const partsOrder = this.partsOrder;
    const userRole = this.userRole;

    $('.select2', this.$partsList).select2({
        language: 'ru',
        templateResult: function(state){
            const parts = state.text.split(' - ');
            if(parts.length < 2){
                return state.text;
            }
            if(!parts[2]){
                parts[2] = '';
            }
            let html = ` 
            <span class="s2-name-num">
                <span class="s2-name">${parts[0]}</span>
                <span class="s2-num">${parts[1]}</span>
            </span>
            <span class="s2-place">${parts[2]}</span>`;
            return $(html);
        }
    });

    $('[data-part]', this.$partsList).each(function() {
        let $part = $(this);
        if (this.userRole != 'service') {
            $part.find('[data-elem="extra"]').show();
        } else {
            $part.find('[data-elem="extra"]').hide();
        }
        updateControls($part, partsOrder, userRole);
    });


    function updateControls($part, partsOrder, userRole) {
        const $orderBtn = $part.find('[data-name="order-btn"]');
        if (userRole == 'admin') {
            $orderBtn.show();
            return;
        }
        if (!partsOrder) {
            $orderBtn.hide();
            return;
        }
        if (!partsOrder.isSent()) { // если заказ уже отправлен
            $orderBtn.show(); // показать кнопку заказа только у админа
        } else {
            $orderBtn.hide();
        }
    }
};