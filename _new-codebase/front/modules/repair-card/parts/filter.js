/* global $, Part */

class Filter {

    constructor(partsList) {
        this.filterForm = document.getElementById('filter-form');
        this.partsList = partsList;
        this.isPending = false;
        if (!Filter.initFlag) {
            Filter.initFlag = true;
            this.attachEvents();
        }
    }


    loadParts() {
        this.search();
    }


    attachEvents() {
        $(this.filterForm).on('submit', (event) => {
            event.preventDefault();
            this.filterForm.querySelector('[type="submit"]').innerHTML = 'Загрузка...';
            this.search();
        });
        $('[data-action="reset"]').on('click', function () {
            location.reload();
        });
        $('[data-input="country-id"]').on('change', (event) => {
            if (!event.target.value) {
                this.filterDepotsByCountry(null);
            } else {
                this.filterDepotsByCountry(event.target.options[event.target.selectedIndex].innerText);
            }
        });
        $('.fselect', this.filterForm).fSelect({
            placeholder: '-- все --',
            numDisplayed: 1,
            overflowText: '{n} выбрано',
            noResultsText: 'Не найдено',
            searchText: 'Поиск',
            showSearch: true
        });
        $('.select2', this.filterForm).select2({
            language: 'ru'
        });
    }


    filterDepotsByCountry(country) {
        const depotElem = document.getElementById('depot-filter');
        const $groups = $('[data-group]', depotElem); // groups в fSelect
        const $options = $('.fs-option', depotElem); // options в fSelect
        /* Сбросить состояние fSelect */
        $options.filter('.selected').removeClass('selected');
        depotElem.querySelector('[name="depot_id[]"]').value = undefined;
        depotElem.querySelector('.fs-label').innerText = '-- не выбрано --';
        /* Если страна не выбрана, отобразить всё */
        if (country === null) {
            $groups.show(); // показать все groups
            $options.show(); // показать все options
            return;
        }
        /* Поиск индекса страны в fSelect */
        let index = null;
        const options = depotElem.querySelectorAll('.fs-optgroup-label');
        for (let i = 0, len = options.length; i < len; i++) {
            if (options[i].innerText == country) {
                index = options[i].dataset.group;
                break;
            }
        }
        if (index === null) { // скрыть всё, склада страны нет
            $groups.hide();
            $options.hide();
            return;
        }
        $groups.hide(); // скрыть все groups (так как страна выбрана)
        $options.show();
        $options.filter(`.fs-option:not(.g${index})`, depotElem).hide(); // скрыть все options, кроме выбранной страны
    }


    search() {
        const self = this;
        if (self.isPending) {
            return;
        }
        self.isPending = true;
        const data = new FormData(this.filterForm);
        $.ajax({
            type: 'POST',
            dataType: 'json',
            processData: false,
            contentType: false,
            cache: false,
            url: '?ajax=parts-search',
            data: data,
            success: (resp) => {
                self.partsList.$partsList.html(resp['html']);
                self.partsList.updateView();
            },
            error: function (jqXHR) {
                alert('К сожалению, произошла ошибка, пожалуйста, обратитесь к администратору.');
                console.log('Ошибка сервера');
                console.log(jqXHR.responseText);
            },
            complete: function () {
                self.filterForm.querySelector('[type="submit"]').innerHTML = 'Применить';
                self.isPending = false;
            }
        });
    }




}