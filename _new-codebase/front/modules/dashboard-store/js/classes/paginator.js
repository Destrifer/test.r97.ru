class Paginator {

    constructor(state) {
        this.state = state;
        this.pageNum = this.state.getValue('page') ?? 1;
        this.pageLen = this.state.getValue('len') ?? 100;
        this.navContainers = document.querySelectorAll('[data-pagination="nav"]');
        this.infoBoxContainers = document.querySelectorAll('[data-pagination="info-box"]');
        this.pageLenInput = document.querySelector('[data-pagination="page-len-input"]');
        this.pageLenInput.value = this.pageLen;
        this.timeoutID = 0; // для оптимизации
        this._addEvents();
    }


    _addEvents() {
        $('body').on('click', '[data-pagination="page-num"]', (event) => {
            this.pageNum = +event.target.dataset.value;
            this.state.setValue('page', (this.pageNum > 1 ? this.pageNum : 0));
            this.state.update();
        });
        $(this.pageLenInput).on('change', (event) => {
            clearTimeout(this.timeoutID);
            this.pageLen = +event.target.value;
            this.timeoutID = setTimeout(() => {
                this.state.setValue('len', this.pageLen);
                this.state.update();
            }, 650);
        });
    }


    updateView(data) {
        this.data = data;
        this._renderNav();
        this._renderInfoBox();
    }


    _renderNav() {
        let html = '';
        const pagination = this._getPaginationData();
        pagination.forEach(item => {
            if (item.isDot || item.pageNum == this.pageNum) {
                html += `<div class="pagination-nav__item ${(item.isDot) ? 'pagination-nav__item_dot' : 'active'}">${item.pageNum}</div>`;
            } else {
                html += `<div data-pagination="page-num" data-value="${item.pageNum}" class="pagination-nav__item">${item.pageNum}</div>`;
            }
        });
        for (let i = 0; i < this.navContainers.length; i++) {
            this.navContainers[i].innerHTML = html;
        }
    }


    _getPaginationData() {
        const pagesCnt = Math.ceil(+this.data.totalCnt / this.pageLen);
        let startPos = this.pageNum - 2;
        startPos = startPos < 1 ? 1 : startPos;
        let endPos = this.pageNum + 2;
        endPos = endPos > pagesCnt ? pagesCnt : endPos;
        const pagination = [];
        if (startPos > 1) {
            pagination.push({ pageNum: 1, isDot: false });
            if (startPos > 2 && pagesCnt > 7) {
                pagination.push({ pageNum: '...', isDot: true });
            }
        }
        for (let i = startPos; i <= endPos; i++) {
            pagination.push({ pageNum: i, isDot: false });
        }
        if (endPos < pagesCnt) {
            if (endPos < pagesCnt - 1 && pagesCnt > 7) {
                pagination.push({ pageNum: '...', isDot: true });
            }
            pagination.push({ pageNum: pagesCnt, isDot: false });
        }
        return pagination;
    }


    _renderInfoBox() {
        const fmt = new Intl.NumberFormat('ru-RU');
        const html = `Выбрано: ${fmt.format(this.pageLen)}, всего: ${fmt.format(this.data.totalCnt)}`;
        for (let i = 0; i < this.infoBoxContainers.length; i++) {
            this.infoBoxContainers[i].innerHTML = html;
        }
    }

}