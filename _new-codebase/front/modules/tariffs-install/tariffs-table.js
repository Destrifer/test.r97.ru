document.addEventListener('DOMContentLoaded', function () {

    const tableURL = new URL(location.href);
    tableURL.searchParams.set('ajax', 'get-cats');
    let data = {};
    let blockFlag = false;


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


    $('body').on('click', '[data-action]', function (event) {
        event.preventDefault();
        switch (this.dataset.action) {
            case 'save-changes':
                save();
                break;
        }
    });


    function save() {
        if (blockFlag || !Object.keys(data).length) {
            return;
        }
        blockFlag = true;
        $.ajax({
            type: 'POST',
            url: '/tariffs-install/?ajax=update-cost',
            data: 'data=' + JSON.stringify(data),
            success: function (resp) {
                if (+resp['error_flag']) {
                    alert(resp['message']);
                    return;
                }
                alert('Тарифы сохранены.');
                data = {};
            },
            complete: function () {
                blockFlag = false;
            },
        });
    }


    $('body').on('change', '[data-input]', function () {
        switch (this.dataset.input) {
            case 'install-cost':
            case 'dismant-cost':
                collectData(this.dataset.input, this.dataset.catId, this.value);
                break;
        }
    });


    $(document).on('passprotect:unblock', function () {
        $('[data-action="save-changes"]').removeClass('disabled');
    });


    window.onbeforeunload = function () {
        if (Object.keys(data).length) {
            return false;
        }
    };


    function collectData(field, catID, value) {
        data[catID + field] = { field, value, 'cat_id': catID };
    }


});