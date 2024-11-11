class TopBtn {

    constructor(viewportElem) {
        this.START_OFFSET = 600;
        this.isBlocked = false;
        this.viewport = viewportElem;
        this.btn = this._addBtn();
        this._addEvents();
    }


    _addEvents() {
        this.btn.addEventListener('click', () => {
            this.isBlocked = true;
            let newPos;
            if (this.btn.classList.contains('active')) { // обратно
                newPos = this.btn.dataset.lastPos;
            } else { // наверх, с сохранением позиции
                newPos = 0;
                this.btn.dataset.lastPos = this.viewport.scrollTop;
                this.btn.classList.add('active');
            }
            $(this.viewport).animate({ scrollTop: newPos }, 400, () => this.isBlocked = false);
        });

        this.viewport.addEventListener('scroll', () => {
            if (this.isBlocked) {
                return;
            }
            if (this.viewport.scrollTop > this.START_OFFSET) {
                this.btn.style.display = '';
                this.btn.classList.remove('active'); // старая позиция больше не нужна
            } else {
                if (!this.btn.classList.contains('active')) {
                    this.btn.style.display = 'none';
                }
            }
        }, { passive: true });
    }


    _addBtn() {
        document.body.insertAdjacentHTML('beforeend', '<div class="top-btn icon-up-big" id="top-btn" title="Наверх/обратно" style="display: none"></div>');
        return document.getElementById('top-btn');
    }

}