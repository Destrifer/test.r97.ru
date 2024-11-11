class Resizer {

    constructor(colsBuilder) {
        this.colsBuilder = colsBuilder;
        this.activeCol = null;
        this.resizer = null;
        this.pos = 0;
        this.timeoutID = 0;
        this._addEvents();
    }


    _addEvents() {
        document.body.addEventListener('mousedown', (event) => {
            if (event.target.dataset.resizer === undefined) {
                return;
            }
            if (this.timeoutID) {
                clearTimeout(this.timeoutID);
            }
            this.activeCol = event.target.parentElement;
            this.resizer = event.target;
            document.body.style.cursor = 'col-resize'; // чтобы курсор не дергался
            this.resizer.style.opacity = '1';
        });

        document.body.addEventListener('mousemove', (event) => {
            if (!this.activeCol) {
                return;
            }
            const d = (!this.pos) ? 1 : Math.abs(this.pos - event.clientX);
            const newWidth = (this.pos < event.clientX) ? this.activeCol.offsetWidth + d : this.activeCol.offsetWidth - d;
            this.activeCol.style.minWidth = newWidth + 'px';
            this.resizer.innerText = newWidth; // показать ширину
            this.pos = event.clientX;
        });

        document.body.addEventListener('mouseup', () => {
            if (!this.activeCol) {
                return;
            }
            this.activeCol = null;
            this.resizer.style.opacity = '0';
            this.resizer = null;
            this.pos = 0;
            document.body.style.cursor = '';
            this.timeoutID = setTimeout(() => {
                this.colsBuilder.update();
            }, 2000);
        });
    }

}