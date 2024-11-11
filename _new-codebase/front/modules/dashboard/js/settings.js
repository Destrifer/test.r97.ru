/* global $ */

function Settings(user) {

    /* Загрузка настройки из базы */
    this.load = (key, fn) => {
        const uri = getURI(key);
        const val = localStorage.getItem(uri);
        if (val || val === false) {
            fn(val);
            return;
        }
        const data = new FormData();
        data.append('uri', uri);
        data.append('ajax', 'load-settings');
        $.ajax({
            type: 'POST',
            dataType: 'json',
            processData: false,
            contentType: false,
            cache: false,
            url: location.href,
            data: data,
            success: function(resp) {
                if (!resp.settings.length) {
                    localStorage.setItem(uri, false);
                } else {
                    localStorage.setItem(uri, resp.settings);
                }
                fn(resp.settings);
            }
        });
    };


    this.get = (key) => {
        const uri = getURI(key);
        return localStorage.getItem(uri);
    };


    this.set = (key, val) => {
        const uri = getURI(key);
        localStorage.setItem(uri, val);
    };


    this.save = function(key, val) {
        const uri = getURI(key);
        const data = new FormData();
        data.append('val', val);
        data.append('uri', uri);
        data.append('ajax', 'save-settings');
        $.ajax({
            type: 'POST',
            processData: false,
            contentType: false,
            cache: false,
            url: location.href,
            data: data
        });
        localStorage.setItem(uri, val);
    };


    function getURI(key) {
        const url = new URL(location.href);
        let tab = url.searchParams.get('tab');
        if (!tab) {
            tab = 'all';
        }
        if (!key) {
            return `${user.id}:dashboard:${tab}`;
        }
        return `${user.id}:dashboard:${tab}:${key}`;
    }
}