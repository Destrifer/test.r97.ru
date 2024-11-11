class Sorting {

    constructor(state) {
        this.activeCol = null;
        this.rows = [];
        this.state = state;
        this.colURI = this.state.getValue('sort') ?? '';
        this.direction = this.state.getValue('dir') ?? '';
        this._addEvents();
    }


    _addEvents() {
        $(document.body).on('dblclick', '[data-sorting]', (event) => {
            if (!this.rows.length) {
                return;
            }
            const currCol = event.currentTarget;
            if (this.activeCol != currCol) {
                this._deactivate(this.activeCol);
                this._activate(currCol);
            }
            this.activeCol = currCol;
            this.colURI = this.activeCol.dataset.colUri;
            this.direction = this._toggleDirection(this.activeCol);
            this.state.setValue('sort', this.colURI);
            this.state.setValue('dir', this.direction);;
            this.state.update();
        });
    }


    updateView(rows) {
        this.rows = rows;
        if (this.rows.length < 2 || !this.colURI) {
            return;
        }
        const currCol = document.querySelector(`[data-col-uri="${this.colURI}"]`);
        if(!currCol){
            return;
        }
        this._deactivate(this.activeCol);
        this._activate(currCol);
        this.activeCol = currCol;
        if (this.direction == 'desc') {
            this.activeCol.classList.add('sorting_desc');
        } else {
            this.activeCol.classList.remove('sorting_desc');
        }
    }


    _toggleDirection(col) {
        if (col.classList.contains('sorting_desc')) {
            col.classList.remove('sorting_desc');
            return '';
        }
        col.classList.add('sorting_desc');
        return 'desc';
    }


    _activate(col) {
        const index = $(col).index();
        for (let i = 0, len = this.rows.length; i < len; i++) {
            this.rows[i].children[index].classList.add('sorting');
        }
    }


    _deactivate(col) {
        if (!col) {
            return;
        }
        const index = $(col).index();
        for (let i = 0, len = this.rows.length; i < len; i++) {
            this.rows[i].children[index].classList.remove('sorting', 'sorting_desc');
        }
    }

}