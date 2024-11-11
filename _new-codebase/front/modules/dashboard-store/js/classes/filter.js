class Filter {

    constructor(state) {
        this.state = state;
        this.container = document.querySelector('[data-filter="container"]');
        this.cache = { options: {} };
        this.activeFilters = new Set();
        this.prevFilterURI = ''; // значение uri фильтра до изменения change
        this.clearFiltersBtn = document.querySelector('[data-filter="clear-filter"]');
        this._initTpl();
        this._addEvents();
        this._updateView();
    }


    async _updateView() {
        await this.load();
        for (let filterURI in this.filters) {
            if (!this.state.has(filterURI) || this.activeFilters.has(filterURI)) {
                continue;
            }
            this._addFilter(filterURI, this.state.getValue(filterURI));
            this.activeFilters.add(filterURI);
        }
        this._updateControls();
    }


    _updateControls() {
        if (!this.activeFilters.size) {
            this.clearFiltersBtn.style.display = 'none';
        } else {
            this.clearFiltersBtn.style.display = '';
        }
    }


    _initTpl() {
        this.tpl = `<div class="filter" data-filter="item" style="display: none">
                        <div class="filter__name" data-filter="uri">
                            
                        </div>
                        <div class="filter__value" data-filter="value">
                            
                        </div>
                        <div class="filter__del-btn" data-filter="delete">&#215;</div>
                    </div>`;
    }


    async load() {
        const data = new FormData();
        data.append('ajax', 'get-filters');
        const response = await fetch(location.href, {
            method: 'POST',
            mode: 'same-origin',
            credentials: 'same-origin',
            body: data
        });
        this.filters = await response.json();
    }


    _addEvents() {
        $('[data-filter="add-filter"]').on('click', (event) => {
            event.preventDefault();
            this._addFilter();
            this._updateControls();
        });
        $('[data-filter="clear-filter"]').on('click', (event) => {
            event.preventDefault();
            this._clearFilter();
        });
        $('body').on('click', '[data-filter="delete"]', ({ target }) => {
            const filterElem = target.closest('[data-filter="item"]');
            this._deleteFilter(filterElem, filterElem.querySelector('[data-filter="filter-select"]').value);
            this.state.update();
            this._updateControls();
        });
        $('body').on('focus', '[data-filter="filter-select"]', ({ target }) => {
            this.prevFilterURI = target.value;
        });
        $('body').on('change', '[data-filter="filter-select"]', ({ target }) => {
            const filterURI = target.value;
            if (this.activeFilters.has(filterURI)) {
                target.value = this.prevFilterURI;
                alert('Фильтр уже выбран.');
                return;
            }
            if (this.prevFilterURI !== filterURI) {
                this.activeFilters.delete(this.prevFilterURI);
                this.state.setValue(this.prevFilterURI, null);
            }
            this.activeFilters.add(filterURI);
            this._updateControls();
            this._renderFilterValue(filterURI, '', target.closest('[data-filter="item"]'));
        });
        $('body').on('change', '[data-filter="input"]', (event) => {
            const input = event.target;
            let name = input.name;
            let val = input.value;
            if (input.multiple) {
                let values = [];
                for (let i = 0; i < input.options.length; i++) {
                    if (input.options[i].selected) {
                        values.push(input.options[i].value);
                    }
                }
                name = name.replace(/\[\]/, '');
                val = values.join(',');
            }
            this.state.setValue(name, val);
            this.state.update();
        });
    }


    _addFilter(filterURI = '', value = '') {
        this.container.insertAdjacentHTML('afterbegin', this.tpl);
        const container = this.container.querySelector('[data-filter="item"]');
        this._renderFilterURI(filterURI, container);
        if (value) {
            this._renderFilterValue(filterURI, value, container);
        }
        $(container).fadeIn();
    }


    _clearFilter() {
        const filterElems = this.container.querySelectorAll('[data-filter="item"]');
        for (let i = 0; i < filterElems.length; i++) {
            this._deleteFilter(filterElems[i], filterElems[i].querySelector('[data-filter="filter-select"]').value);
        }
        this.state.update();
        this._updateControls();
    }


    _renderFilterURI(filterURI, container) {
        const uriContainer = container.querySelector('[data-filter="uri"]');
        let selected = '';
        let selectHTML = `<select data-filter="filter-select">
        <option value="">-- выберите фильтр --</option>`;
        for (let key in this.filters) {
            selected = (key === filterURI) ? 'selected' : '';
            selectHTML += `<option value="${key}" ${selected}>${this.filters[key].name}</option>`;
        }
        selectHTML += '</select>';
        uriContainer.innerHTML = selectHTML;
    }


    _renderFilterValue(filterURI, value, container) {
        const valueContainer = container.querySelector('[data-filter="value"]');
        if (!filterURI) {
            valueContainer.innerHTML = '';
            return;
        }
        this._getValueRenderer(filterURI, value)(valueContainer);
    }


    _getValueRenderer(filterURI, value) {
        const type = this.filters[filterURI].type;
        value = value.toString();
        switch (type) {
            case 'text':
                return (container) => {
                    container.innerHTML = `<input type="text" data-filter="input" value="${value}" name="${filterURI}" placeholder="Введите значение...." />`;
                }

            case 'number':
                return (container) => {
                    container.innerHTML = `<input type="number" min="0" data-filter="input" value="${value}" name="${filterURI}" placeholder="Введите значение...." />`;
                }

            case 'select':
                return (container) => {
                    container.innerHTML = `<select data-filter="input" name="${filterURI}"><option value="" disabled>Загрузка...</option></select>`;
                    if (!this.cache.options[filterURI]) {
                        $.ajax({
                            type: 'POST',
                            url: location.href,
                            data: 'ajax=get-filter-options&filter_uri=' + filterURI,
                            success: (resp) => {
                                this.cache.options[filterURI] = resp;
                                container.querySelector('[data-filter="input"]').innerHTML = this._renderSelectOptions(this.cache.options[filterURI], value);
                            }
                        });
                    } else {
                        container.querySelector('[data-filter="input"]').innerHTML = this._renderSelectOptions(this.cache.options[filterURI], value);
                    }
                };

            case 'date':
                return (container) => {
                    container.innerHTML = `<input type="text" data-range="true" value="${value}" data-multiple-dates-separator=" - " data-datepicker data-filter="input" name="${filterURI}" placeholder="Выберите дату...." />`;
                    $('[data-datepicker]', container).datepicker({
                        language: 'ru',
                        autoClose: true,
                        maxDate: new Date(),
                        onSelect: function (formattedDate, date, inst) {
                            if (formattedDate.indexOf('-') !== -1) { // если выбраны обе даты интервала
                                inst.$el.trigger('change');
                            }
                        }
                    })
                };

            default:
                throw Error('Unknown input type: ' + type);
        }
    }


    _deleteFilter(filterElem, filterURI) {
        this.activeFilters.delete(filterURI);
        this.state.setValue(filterURI, null);
        $(filterElem).fadeOut(100);
    }


    _renderSelectOptions(data, value = '') {
        let selected = '';
        let html = '<option value="">-- Выберите вариант --</option>';
        Object.entries(data).forEach((entry) => {
            selected = (value == entry[0]) ? 'selected' : '';
            html += `<option value="${entry[0]}" ${selected}>${entry[1]}</option>`;
        });
        return html;
    }


}