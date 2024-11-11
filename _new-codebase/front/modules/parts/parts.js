let selectedParts = [];

document.addEventListener('DOMContentLoaded', function () {

    const API_URL = '/parts/';
    const url = new URL(location.href);
    const tableURL = new URL(location.href);
    tableURL.searchParams.set('ajax', 'get-parts');
    const filterSection = document.getElementById('filter-section');

    const $table = $('#datatable').DataTable({
        ...datatableDefaultConfig,
        'processing': true,
        'serverSide': true,
        'ajax': tableURL.href,
        'pageLength': 100,
        'order': [
            [2, 'asc']
        ]
    }).on('draw', function () {
        filterSection.style.visibility = '';
        $('[data-fancybox]').fancybox({
            thumbs: {
                autoStart: true,
                hideOnClose: true,
                parentEl: '.fancybox-container',
                axis: 'x'
            },
            buttons: [
                'zoom',
                'download',
                'thumbs',
                'close'
            ],
        });
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

            case 'select-part':
                selectPart(this);
                break;

            case 'clone-part':
                clonePart(this);
                break;

            case 'restore-part':
                restorePart(this);
                break;

            case 'select-all':
                selectAllParts();
                break;

            case 'deselect-all':
                deselectAllParts();
                break;
        }
    });


    function generateExcel(btnElem) {
        btnElem.style.opacity = '.3';
        url.searchParams.set('action', 'generate-excel');
        location.href = url.href;
        setTimeout(() => {
            btnElem.style.opacity = '';
        }, 5000);
    }


    function selectAllParts() {
        const partElems = document.querySelectorAll('[data-action="select-part"]');
        for (let i = 0, len = partElems.length; i < len; i++) {
            partElems[i].classList.remove('active');
            selectPart(partElems[i]);
        }
    }


    function deselectAllParts() {
        const partElems = document.querySelectorAll('[data-action="select-part"]');
        for (let i = 0, len = partElems.length; i < len; i++) {
            partElems[i].classList.add('active');
            selectPart(partElems[i]);
        }
    }


    function restorePart(triggerElem) {
        if (restorePart.isBlocked) {
            return;
        }
        restorePart.isBlocked = true;
        $.ajax({
            type: 'POST',
            url: `${API_URL}?ajax=restore-part`,
            data: `part_id=${triggerElem.dataset.partId}`,
            dataType: 'json',
            success: function (resp) {
                if (+resp.is_error) {
                    alert(resp.message);
                    return;
                }
                $('#datatable').DataTable().ajax.reload();
            },
            complete: () => {
                restorePart.isBlocked = false;
            }
        });
    }


    function clonePart(triggerElem) {
        if (clonePart.isBlocked) {
            return;
        }
        clonePart.isBlocked = true;
        $.ajax({
            type: 'POST',
            url: `${API_URL}?ajax=clone-part`,
            data: `part_id=${triggerElem.dataset.partId}&depot_id=${triggerElem.dataset.depotId}`,
            dataType: 'json',
            success: function (resp) {
                if (+resp.is_error) {
                    alert(resp.message);
                    return;
                }
                $('#datatable').DataTable().ajax.reload();
            },
            complete: () => {
                clonePart.isBlocked = false;
            }
        });
    }


    function selectPart(triggerElem) {
        const depotID = triggerElem.dataset.depotId;
        const partID = triggerElem.dataset.partId;
        if (triggerElem.classList.contains('active')) {
            triggerElem.classList.remove('active');
            selectedParts = selectedParts.filter(part => part.id != partID);
        } else {
            triggerElem.classList.add('active');
            selectedParts.push({ id: partID, depot_id: depotID });
        }
        document.dispatchEvent(new CustomEvent('parts:selectionchanged'));
    }

});