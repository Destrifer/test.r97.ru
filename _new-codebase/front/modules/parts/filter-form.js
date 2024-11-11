document.addEventListener('DOMContentLoaded', function() {

    const url = new URL(location.href);

    $('[data-datepicker-interval]').datepicker({
        language: 'ru',
        autoClose: true,
        maxDate: new Date(),
        onSelect: function (formattedDate, date, inst) {
            inst.$el.trigger('change');
        }
    });


    $('.select2').select2({
        language: 'ru'
    });


    const fSelectOptions = {
        numDisplayed: 1,
        overflowText: '{n} выбрано',
        noResultsText: 'Не найдено',
        searchText: 'Поиск',
        showSearch: true
    };


    $('.fselect-depot', this.filterForm).fSelect({ ...fSelectOptions, placeholder: '-- все склады --' });


    $('.fselect-cat', this.filterForm).fSelect({ ...fSelectOptions, placeholder: '-- все категории --' });


    $('[data-filter]').on('change', function () {
        let name = this.name;
        let val = this.value;
        let values = [];
        if (this.type == 'checkbox' && !this.checked) {
            val = '';
        }
        if (this.multiple) {
            for (let i = 0; i < this.options.length; i++) {
                if (this.options[i].selected) {
                    values.push(this.options[i].value);
                }
            }
            name = name.replace(/\[\]/, '');
            if (!values.length) {
                url.searchParams.delete(name);
            } else {
                url.searchParams.set(name, values.join(','));
            }
        } else {
            if (!val || val == '0') {
                url.searchParams.delete(name);
            } else {
                url.searchParams.set(name, val);
            }
        }
        history.pushState(null, null, url.href);
        if (this.name == 'cat_id') {
            updateModelsSelect(this.value);
        }
        if (this.name == 'country_id') {
            updateDepotsSelect(this.value);
            if (!this.value) {
                updateDepotsSelect(null);
            } else {
                updateDepotsSelect(this.options[this.selectedIndex].innerText);
            }
        }
    });


    // TODO: дублирование с Parts в карточке ремонта
    function updateDepotsSelect(country) {
        const depotElem = document.getElementById('depot-filter');
        const $groups = $('[data-group]', depotElem); // groups в fSelect
        const $options = $('.fs-option', depotElem); // options в fSelect
        /* Сбросить состояние fSelect */
        $options.filter('.selected').removeClass('selected');
        depotElem.querySelector('[name="depot_id"]').value = undefined;
        $(depotElem.querySelector('[name="depot_id"]')).trigger('change');
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


    function updateModelsSelect(catID) {
        const $select = $('[name="model_id"]');
        $select.html('');
        $.ajax({
            type: 'POST',
            url: `/parts/?ajax=get-models-list`,
            data: `cat_id=${catID}`,
            dataType: 'json',
            success: function (resp) {
                $select.append(new Option('-- любая модель --', '', true, true));
                resp.forEach((model) => {
                    $select.append(new Option(model.name, model.id, true, true));
                });
                $select.val('');
                $select.trigger('change');
            },
        });
    }

});