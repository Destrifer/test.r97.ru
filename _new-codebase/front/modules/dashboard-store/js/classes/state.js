/* global EventEmitter */

class State extends EventEmitter {

    constructor() {
        super();
        this.VER = 1; // версия данных
        this.state = {}; // ключ - значение
        this.url = new URL(location.href);
        this.tab = this._getTabURI();
        this.isChanged = false;
    }


    async load() {
        this._fire('loading');
        this.state = this._fromURL();
        if (this.state) {
            this._saveLocal();
        } else {
            this.state = this._fromLocal();
            if (!this.state) {
                this.state = await this._fromNetwork();
                this._saveLocal();
            }
            this._updateURL();
        }
        this._fire('loaded');
    }


    _fromURL() {
        const data = {};
        this.url.searchParams.forEach((v, k) => {
            data[k] = v;
        });
        return (Object.keys(data).length == 0) ? null : data;
    }


    _fromLocal() {
        if (localStorage.getItem('dashboard-store:ver') != this.VER) {
            return null;
        }
        let stateData = localStorage.getItem(`dashboard-store:${this.tab}`);
        return (stateData) ? JSON.parse(stateData) : null;
    }


    async _fromNetwork() {
       
        const data = new FormData();
        data.append('ajax', 'load-state');
        const response = await fetch(location.href, {
            method: 'POST',
            mode: 'same-origin',
            credentials: 'same-origin',
            body: data
        });
        return await response.json();
    }


    _getTabURI() {
        const path = this.url.pathname.split('/').filter(e => e);
        if (!path[1]) {
            throw new Error('Tab path not found.');
        }
        return path[1];
    }


    _updateURL() {
        for (let key in this.state) {
            if (!this.url.searchParams.has(key)) {
                this.url.searchParams.set(key, this.state[key]);
            }
        }
        history.pushState(null, null, this.url.href);
    }


    _saveLocal() {
        localStorage.setItem('dashboard-store:ver', this.VER);
        localStorage.setItem(`dashboard-store:${this.tab}`, JSON.stringify(this.state));
    }


    has(key) {
        return !!this.state[key];
    }


    getValue(key) {
        if (!this.state[key]) {
            return null;
        }
        return this.state[key];
    }


    getState() {
        return this.state;
    }


    setValue(key, value) {
        if (this.state[key] == value) {
            return;
        }
        this.isChanged = true;
        if (value && value != 0) {
            this.state[key] = value;
            this.url.searchParams.set(key, value);
        } else {
            delete this.state[key];
            this.url.searchParams.delete(key);
        }
        this._saveLocal();
        history.pushState(null, null, this.url.href);
    }


    update() {
        if (this.isChanged) {
            this._fire('changed');
            this.isChanged = false;
        }
    }

}