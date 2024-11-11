/* global $ */

function Table(url) {
    'use strict';
    this.header = document.getElementById('table-header');
    this.headerCols = this.header.querySelectorAll('th');
    this.body = document.getElementById('table-body');
    this.sortField = document.getElementById('sort-field');
    this.sortDir = document.getElementById('sort-dir');
    let curSortColIndex = -1; // индекс текущего столбца сортировки
    const $paginationCtr = $('[data-pagination]');
    const $totalRepairsCnt = $('[data-cnt="total-repairs"]');
    const $repairsPageCnt = $('[data-cnt="repairs-on-page"]');
    const $loader = $('#loader');
    const numPerPage = document.getElementById('num-per-page-field');


    colResizer.call(this);


    /* Сохранение настроек */
    $('#save-table-settings-trig').on('click', (e) => {
        if (e.currentTarget.classList.contains('active')) {
            return;
        }
        e.currentTarget.classList.add('active');
        setTimeout(() => e.currentTarget.classList.remove('active'), 1200);
        const cols = {};
        this.headerCols.forEach(function(col, i) {
            cols[i] = col.offsetWidth;
        });
        saveColsWidth(cols);
    });


    /* Сохранение ширины столбцов в БД */
    function saveColsWidth(settings) {
        let tab = url.searchParams.get('tab');
        if (!tab) {
            tab = 'all';
        }
        const data = new FormData();
        data.append('width', JSON.stringify(settings));
        data.append('tab', tab);
        data.append('ajax', 'save-cols-width');
        $.ajax({
            type: 'POST',
            processData: false,
            contentType: false,
            cache: false,
            url: location.href,
            data: data
        });
    }


    function colResizer() {
        const table = this;
        let headerCol;
        let dataCol;
        let resizeTrig;
        let pos = 0;

        $(table.headerCols).on('click', '[data-col-resizer]', function() {
            return false;
        });

        $(table.header).on('mousedown', '[data-col-resizer]', function() {
            headerCol = this.parentElement;
            headerCol.parentElement.style.cursor = 'col-resize';
            resizeTrig = this;
            resizeTrig.style.opacity = 1;
            let i = Array.prototype.slice.call(table.headerCols).indexOf(headerCol);
            let r = table.body.querySelector('[data-repair-id]');
            if (!r) {
                return;
            }
            dataCol = r.querySelectorAll('td')[i];
        });

        $('body').on('mouseup', function() {
            if (!headerCol) {
                return;
            }
            resizeTrig.style.opacity = '';
            resizeTrig = undefined;
            headerCol.parentElement.style.cursor = '';
            headerCol = undefined;
            dataCol = undefined;
            pos = 0;
        });

        $('body').on('mousemove', function(e) {
            if (!headerCol) {
                return;
            }
            let d = (!pos) ? 1 : Math.abs(pos - e.clientX);
            let newWidth = (pos < e.clientX) ? headerCol.offsetWidth + d : headerCol.offsetWidth - d;
            if (dataCol) {
                dataCol.style.minWidth = newWidth + 'px';
                if (dataCol.offsetWidth > newWidth) { // нельзя сделать ширину меньше
                    newWidth = dataCol.offsetWidth; // использовать минимальную ширину столбца с данными 
                }
            }
            headerCol.style.minWidth = newWidth + 'px';
            resizeTrig.innerText = newWidth; // показать ширину столбца
            pos = e.clientX;
        });
    }


    this.resize = function() {
        let r = this.body.querySelector('[data-repair-id]');
        if (!r) {
            return;
        }
        let dataCols = r.querySelectorAll('td');
        for (let i = 0, len = dataCols.length; i < len; i++) {
            dataCols[i].style.width = this.headerCols[i].style.minWidth;
            let { width } = getComputedStyle(dataCols[i]); // расчет реальной ширины ячейки данных
            width = parseFloat(width);
            if (parseFloat(this.headerCols[i].offsetWidth) < width) { // не удается сузить ячейку с данными
                this.headerCols[i].style.minWidth = `${width}px`; // тогда расширить ячейку шапки
                dataCols[i].style.minWidth = `${width}px`; // установить реальную ширину ячейке с данными 
            } else {
                dataCols[i].style.minWidth = `${this.headerCols[i].offsetWidth}px`; // выровнять ширину ячейки с данными соответственно шапке 
            }
            dataCols[i].style.width = '';
        }
    };


    /* Выделяет отсортированный столбец */
    function colorSortCol(curCol, sortDir) {
        // расцвечивание заголовка столбца
        this.headerCols.forEach(function(col) {
            col.classList.remove('active', 'asc', 'desc');
        });
        curCol.classList.add('active', sortDir);
        // расцвечивание всех столбцов
        let newIndex = Array.prototype.indexOf.call(this.headerCols, curCol);
        const rows = this.body.querySelectorAll('[data-repair-id]');
        for (let i = 0, len = rows.length; i < len; i++) {
            let tds = rows[i].querySelectorAll('td');
            if (tds.length < 2) {
                break;
            }
            tds[newIndex].classList.add('active');
            if (curSortColIndex > 0 && newIndex != curSortColIndex) {
                tds[curSortColIndex].classList.remove('active');
            }
        }
        curSortColIndex = newIndex;
    }


    function initWidgets() {
        $('[data-air-datepicker]').datepicker({
            language: 'ru',
            autoClose: true,
            maxDate: new Date(),
            onSelect: function(formattedDate, date, inst) {
                document.dispatchEvent(new CustomEvent('dateSelected', {
                    detail: {
                        formattedDate,
                        date,
                        inst
                    }
                }));
            }
        });
    }


    this.load = function() {
        $loader.show();
        const table = this;
        $.ajax({
            type: 'POST',
            dataType: 'json',
            cache: false,
            url: url.href,
            data: 'ajax=load-rows&sort_field=' + table.sortField.value + '&sort_dir=' + table.sortDir.value + '&num_per_page=' + numPerPage.value,
            success: function(resp) {
                table.body.innerHTML = resp.rows_html;
                $paginationCtr.html(resp.pagination_html);
                $totalRepairsCnt.text(new Intl.NumberFormat('ru-RU').format(resp.total_repairs_cnt));
                $repairsPageCnt.text(table.body.querySelectorAll('[data-repair-id]').length);
                table.resize();
                const curCol = table.header.querySelector('[data-sort-col="' + table.sortField.value + '"]');
                if (curCol) {
                    colorSortCol.call(table, curCol, table.sortDir.value);
                }
                initWidgets();
            },
            error: function(jqXHR) {
                console.log('Ошибка сервера');
                console.log(jqXHR.responseText);
            },
            complete: function() {
                $loader.hide();
            }
        });
    };
}