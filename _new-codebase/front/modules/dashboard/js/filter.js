/* global $ */

function Filter(url) {

    const $filterItemTpl = $('#filter-item-template').detach();
    const $delFilterBtn = $('#del-filter-btn');
    this.activeFilters = {}; // коллекция активных фильтров
    let filterItemsCnt = document.querySelectorAll('[data-filter-item]').length;


    this.init = function() {
        $('[data-select2-input]').select2();
        this.update();
    };

    this.update = function() {
        if (filterItemsCnt) {
            $delFilterBtn.fadeIn(400);
        } else {
            $delFilterBtn.hide();
        }
    };

    /* Добавляет блок фильтра */
    this.add = function() {
        filterItemsCnt++;
        let $newBlock = $filterItemTpl.clone();
        $('#filter-items-ctr').append($newBlock);
        $newBlock.slideDown(300, this.update);
    };


    /* Убирает блок фильтра и значение из URL */
    this.del = function($filterItem) {
        filterItemsCnt--;
        let name = $filterItem.data('filter-item');
        $filterItem.slideUp(300, function() {
            $(this).remove();
        });
        if (!name) {
            return;
        }
        delete this.activeFilters[name];
        url.searchParams.delete(name);
        history.pushState(null, null, url.href);
        fire('update');
    };


    /* Выбор типа фильтра и загрузка значений */
    this.loadInput = function($select) {
        let uri = $select.val();
        let $filterItem = $select.closest('[data-filter-item]');
        if ($filterItem.data('filter-item')) {
            /* Удаляет старое значение фильтра из url */
            url.searchParams.delete($filterItem.data('filter-item'));
            history.pushState(null, null, url.href);
        }
        $filterItem.data('filter-item', uri);
        $.ajax({
            type: 'POST',
            dataType: 'json',
            cache: false,
            url: url.href,
            data: 'ajax=load-filter&filter-uri=' + uri,
            success: function(resp) {
                let $ctr = $filterItem.find('[data-filter-value-ctr]');
                $ctr.html(resp.filter_input_html);
                initFilterInput(resp.filter_type, $ctr);
            },
            error: function(jqXHR) {
                console.log('Ошибка сервера');
                console.log(jqXHR.responseText);
            }
        });
    };


    /* Фильтр добавляется к URL */
    this.toURL = function() {
        let f = this.activeFilters;
        let $inputs = $('[data-filter-input]');
        $inputs.each(function() {
            let val = $(this).val().trim();
            if (val && val != 0) {
                url.searchParams.set(this.name, val);
                f[this.name] = val;
            } else {
                url.searchParams.delete(this.name);
                delete f[this.name];
            }
        });
        url.searchParams.delete('page');
        history.pushState(null, null, url.href);
        fire('update');
    };


    /* Инициализация поля ввода значения */
    function initFilterInput(type, $ctr) {
        switch (type) {
            case 'date_interval':
                $('[data-datepicker]', $ctr).datepicker({
                    language: 'ru',
                    autoClose: true,
                    maxDate: new Date(),
                    onSelect: function(formattedDate, date, inst) {
                        if (formattedDate.indexOf('-') !== -1) { // если выбраны обе даты интервала
                            inst.$el.trigger('change');
                        }
                    }
                });
                break;

            case 'select_search':
                $ctr.find('[data-select2-input]').select2();
                break;
        }
    }

    function fire(eventName) {
        document.dispatchEvent(new CustomEvent('filter:' + eventName));
    }
}