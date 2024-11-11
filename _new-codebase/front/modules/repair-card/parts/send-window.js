/* global $ */

function SendWindow(elem) {
    if (!elem) {
        return;
    }
    this.elem = elem;
    this.$sendDate = $('[data-input="send-date"]', elem);
    this.$transCompanyID = $('[data-input="transport-company-id"]', elem);
    this.$trackNum = $('[data-input="track-num"]', elem);
    this.cb = null;
    this.attachEvents();
}


SendWindow.prototype.attachEvents = function() {
    $(this.elem)
        .on('click', '[data-on-click]', (event) => {
            switch (event.target.dataset.onClick) {
                case 'send':
                    if (this.cb) {
                        this.cb(this.$sendDate.val(), this.$transCompanyID.val(), this.$trackNum.val());
                    }
                    this.close();
                    this.reset();
                    break;

                case 'close':
                    this.close();
                    break;
            }
        });
    this.$sendDate.datepicker({
        language: 'ru',
        autoClose: true,
        minDate: new Date()
    });
};

SendWindow.prototype.open = function(cb) {
    $.fancybox.open(this.elem);
    this.cb = cb;
};

SendWindow.prototype.close = function() {
    $.fancybox.close();
};

SendWindow.prototype.reset = function() {
    this.$sendDate.val('');
    this.$trackNum.val('');
    this.cb = null;
};