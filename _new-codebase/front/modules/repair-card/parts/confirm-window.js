/* global $ */

function ConfirmWindow(elem) {
    this.elem = elem;
    this.data = null;
    this.titleElem = elem.querySelector('[data-elem="title"]');
    this.input = elem.querySelector('[data-input="confirm-value"]');
    this.cb = null;
    this.attachEvents();
}

ConfirmWindow.prototype.setValue = function(value) {
    this.input.value = value;
};

ConfirmWindow.prototype.attachEvents = function() {
    $(this.elem)
        .on('click', '[data-on-click]', (event) => {
            switch (event.target.dataset.onClick) {
                case 'confirm':
                    if (this.cb) {
                        this.cb(this.data, this.input.value);
                    }
                    this.close();
                    this.reset();
                    break;

                case 'close':
                    this.close();
                    this.reset();
                    break;
            }
        });
};

ConfirmWindow.prototype.open = function(title, cb) {
    this.titleElem.innerHTML = title;
    $.fancybox.open(this.elem);
    this.cb = cb;
};

ConfirmWindow.prototype.close = function() {
    $.fancybox.close();
};

ConfirmWindow.prototype.reset = function() {
    this.data = null;
    this.input.value = '';
    this.cb = null;
};