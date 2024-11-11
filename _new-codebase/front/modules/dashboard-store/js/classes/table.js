/* global EventEmitter */

class Table extends EventEmitter {

    constructor() {
        super();
        this.viewport = document.getElementById('table-viewport');
        this.body = document.getElementById('table-body');
        this.table = document.querySelector('table');
        this.rows = [];
        this.data = {};
        this.isLoading = false;
    }


    load() {
        const table = this;
        table._fire('loading');
        $.ajax({
            type: 'POST',
            url: location.href,
            dataType: 'json',
            data: 'ajax=load-table',
            success: function (resp) {
                table.body.innerHTML = resp.rows;
                table.rows = table.table.querySelectorAll('tr');
                table.pagination = resp.pagination;
            },
            error: function (jqXHR) {
                alert('К сожалению, произошла ошибка во время запроса.');
                console.log('Ошибка сервера');
                console.log(jqXHR.responseText);
            },
            complete() {
                table._fire('loaded');
            }
        });
    }

}