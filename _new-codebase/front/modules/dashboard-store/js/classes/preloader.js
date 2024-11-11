class Preloader {

    constructor() {
        this.preloader = this._addPreloader();
    }


    show() {
        this.preloader.style.display = '';
    }


    hide() {
        this.preloader.style.display = 'none';
    }


    _addPreloader() {
        document.body.insertAdjacentHTML('beforeend', `<div id="preloader" style="display: none" class="preloader__overlay">
                <div class="preloader">Загрузка данных...</div>
            </div>`);
        return document.getElementById('preloader');
    }

}