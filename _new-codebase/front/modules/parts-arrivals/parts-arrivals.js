let selectedParts = [];

document.addEventListener('DOMContentLoaded', function () {

    const API_URL = '/parts-arrivals/';
    const url = new URL(location.href);
    const tableURL = new URL(location.href);
    tableURL.searchParams.set('ajax', 'get-parts');


    const $table = $('#datatable').DataTable({
        ...datatableDefaultConfig,
        'processing': true,
        'serverSide': true,
        'ajax': tableURL.href,
        'order': [
            [2, 'asc']
        ]
    });


   

    $('body').on('click', '[data-action]', function (event) {
        event.preventDefault();
        switch (this.dataset.action) {

            case 'generate-excel':
                generateExcel(this);
                break;

            case 'apply':
                $table.page('first');
                $table.state.save();
                location.reload();
                break;

            case 'reset':
                $table.page('first');
                $table.state.save();
                location.href = url.pathname;
                break;
        }
    });


    $('body').on('change', '[data-input]', function (event) {
        event.preventDefault();
        switch (this.dataset.input) {

            case 'arrival-name':
                updateArrival(this);
                break;
        }
    });


    function updateArrival(inputElem) {
        let needReload = false;
        if (!inputElem.value.length) {
            if (!confirm('Удалить запчасть из прихода?')) {
                return;
            }
            needReload = true;
        }
        $.ajax({
            type: 'POST',
            url: `${API_URL}?ajax=update-arrival-name`,
            data: `arrival_part_id=${inputElem.dataset.arrivalPartId}&new_arrival_name=${inputElem.value}`,
            dataType: 'json',
            success: function (resp) {
                if (+resp.is_error) {
                    alert(resp.message);
                    return;
                }
                if (needReload) {
                    $('#datatable').DataTable().ajax.reload();
                }
            },
        });
    }


    function generateExcel(btnElem) {
        btnElem.style.opacity = '.3';
        url.searchParams.set('action', 'generate-excel');
        location.href = url.href;
        setTimeout(() => {
            btnElem.style.opacity = '';
        }, 5000);
    }

});