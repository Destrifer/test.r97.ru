document.addEventListener('DOMContentLoaded', function() {

    const tableURL = new URL(location.href);
    tableURL.searchParams.set('ajax', 'get-requests');


    $('#datatable').DataTable({
        ...datatableDefaultConfig,
        'processing': true,
        'serverSide': true,
        'ajax': tableURL.href,
        'order': [
            [1, 'desc']
        ]
    });

});