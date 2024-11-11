document.addEventListener('DOMContentLoaded', function () {

    const tableURL = new URL(location.href);
    tableURL.searchParams.set('ajax', 'get-users');
    const url = new URL(location.href);


    $('#datatable').DataTable({
        ...datatableDefaultConfig,
        'processing': true,
        'serverSide': true,
        'ajax': tableURL.href,
        'order': [
            [1, 'asc']
        ]
    }).on('draw', function () {
        document.dispatchEvent(new CustomEvent('page:update'));
    });


    $('.select2').select2();


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
    });


    $('body').on('click', '[data-action]', function (event) {
        event.preventDefault();
        const $trigger = $(this);
        switch (this.dataset.action) {

            case 'change-status':
                changeStatus($trigger);
                break;

            case 'apply':
                location.reload();
                break;

            case 'reset':
                location.href = url.pathname;
                break;
        }
    });


    function changeStatus($trigger) {
        if (!confirm('Подтверждаете действие?')) {
            return;
        }
        const userID = $trigger.data('user-id');
        const newStatus = $trigger.data('value');
        $.ajax({
            type: 'POST',
            url: '?ajax=change-status',
            dataType: 'json',
            data: `new_status=${newStatus}&user_id=${userID}`,
            cache: false,
            success: function (resp) {
                if (resp.error_flag == 1) {
                    alert(resp.message);
                    return;
                }
                alert(resp.message);
                $trigger.hide();
            }
        });
    }


    $('.fselect').fSelect({
        placeholder: '-- любая роль --',
        numDisplayed: 1,
        overflowText: '{n} выбрано',
        noResultsText: 'Не найдено',
        searchText: 'Поиск',
        showSearch: true
    });

});