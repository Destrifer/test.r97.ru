/* global $ */

document.addEventListener('DOMContentLoaded', function() {

    const tableURL = new URL(location.href);
    tableURL.searchParams.set('ajax', 'get-dicts');

    $('#datatable').DataTable({
        ...datatableDefaultConfig,
        'processing': true,
        'serverSide': true,
        'ajax': tableURL.href
    });


});