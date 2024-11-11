class ColsBuilder {


    constructor(tabURI) {
        this.tabURI = tabURI;
        this.API_URL = location.href;
        this.cols = document.querySelectorAll('th[data-col-uri]');
    }


    update(){
        const data = new FormData();
        data.append('ajax', 'save-cols');
        data.append('tab_uri', this.tabURI);
        data.append('cols_data', JSON.stringify(this._collectData()));
        fetch(location.href, {
            method: 'POST',
            mode: 'same-origin',
            credentials: 'same-origin',
            body: data
        });
    }


    _collectData(){
        const result = [];
        this.cols.forEach(col => {
            let i = {};
            i.name = col.querySelector('[data-col-name]').innerText;
            i.uri = col.dataset.colUri;
            i.is_sortable = col.dataset.sorting !== undefined;
            i.width = parseInt(col.style.minWidth);
            result.push(i);
        });
        return result;
    }


}