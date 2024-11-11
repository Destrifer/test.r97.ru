class Preloader {

    constructor() {
        this.loader = this._addLoader();
    }


    enable() {
        this.loader.style.display = '';
    }


    disable() {
        this.loader.style.display = 'none';
    }


    _addLoader() {
        document.body.insertAdjacentHTML('beforeend', `<div id="loader" class="loader__overlay">
                <div class="loader">Загрузка данных...</div>
            </div>`);
        return document.getElementById('loader');
    }

}