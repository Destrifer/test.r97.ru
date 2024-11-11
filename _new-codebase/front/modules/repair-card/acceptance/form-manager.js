/* global $, $repairForm*/

document.addEventListener('DOMContentLoaded', function() {

    let userData = JSON.parse(document.getElementById('user-data-json').innerHTML),
        repairData = JSON.parse(document.getElementById('repair-data-json').innerHTML),
        $saleDate = $('#sale-date'),
        $receiveDate = $('#receive-date'),
        $clientType = $('#client-type'),
        $receptStatus = $('#recept-status'),
        $admisStatus = $('#status-ship-id'),
        $warrantOption = $('#warranty-status-option'),
        $legalSection = $('#legal-entity-section'),
        $privateSection = $('#private-person-section'),
        $onwaySection = $('#onway-section'),
        $warranty = $('#warranty'),
        $serial = $('#serial'),
        $serialInfo = $('#serial-info'),
        $saveMode = $('#save-mode'),
        $anrp = $('#anrp-number'),
        $anrpInfo = $('#anrp-info'),
        $zones = $('#zones'),
        $onwayFlag = $('#onway-flag'),
        $warrantyCard = $('#warranty-card'),
        $noSerialFlag = $('#no-serial-flag'),
        $saleDateError = $('#sale-date-error'),
        $serialError = $('#serial-error');

    attachEvents();
    updateView();

    function updateView() {
        updateAdmissionStatus();
        updateRepairStatus();
        updateSerial();
        updateAnrp();
        toggleSections();
        if ($zones.length) {
            $zones.radiosToSlider();
        }
        updateZones();
        updateOnwayComment();
        updateRequired();
        updateReceptStatus();
        updateWarrantOption();
    }

    function updateRequired() {
        let clientType = parseInt($clientType.val());
        if (clientType == 2 && $saleDate.val()) {
            $('#client-name').prop('required', true);
            $('#client-phone').prop('required', true);
        }
        $clientType.prop('required', true);
        $receiveDate.prop('required', true);
        $receptStatus.prop('required', true);
        $warrantyCard.prop('required', true);
        $('#defect-client').prop('required', true);
        if (clientType == 2) {
            $saleDate.prop('required', false);
        } else {
            if (parseInt($receptStatus.val()) != 6) {
                $saleDate.prop('required', true);
            }
        }
    }

    function updateReceptStatus() {
        if (parseInt($receptStatus.val()) == 6) {
            $saleDate.prop('required', false);
            $('#client-name').prop('required', false);
            $('#client-address').prop('required', false);
            $('#client-phone').prop('required', false);
            $('#shop-name').prop('required', false);
            $('#shop-address').prop('required', false);
            $('#shop-phone').prop('required', false);
            $('[name="refuse_doc_flag"]').prop('required', false);
            return;
        }
        $('#client-name').prop('required', true);
        $('#client-address').prop('required', true);
        $('#client-phone').prop('required', true);
        $('[name="refuse_doc_flag"]').prop('required', true);
        toggleSections();
        updateAdmissionStatus();
    }

    function updateWarrantOption() {
        if ($warrantyCard.val() == 'Документы отсутствуют') {
            $warrantOption.prop('disabled', true);
            if (parseInt($receptStatus.val()) != 6) {
                $receptStatus.val(5);
            }
            return;
        }
        updateRepairStatus();
    }

    function updateAnrp() {
        if (!$anrp.val()) {
            $anrpInfo.hide();
            return;
        }
        $anrpInfo.html('<a target="_blank" href="/edit-repair/' + $anrp.val() + '/step/2/">Ремонт №' + $anrp.val() + '</a>');
        $anrpInfo.show();
    }

    function updateSerial() {
        if ($noSerialFlag.prop('checked')) {
            $serial.prop('readonly', true);
            $serial.prop('required', false);
        } else {
            $serial.prop('readonly', false);
            $serial.prop('required', true);
        }
        let serial = $serial.val();
        if (serial.length) {
            $noSerialFlag.prop('disabled', true);
        } else {
            $noSerialFlag.prop('disabled', false);
        }
        if (!serial.length || $noSerialFlag.prop('checked')) {
            $serial.removeClass('form__input-error');
            $serial.removeClass('form__input-ok');
            $serialError.hide();
            return;
        }
        $.get('/ajax.php?type=check_serials&id=' + repairData.id + '&model_name=' + repairData.model_id + '&serial=' + serial, function(data) {
            if (data.answer != 1) {
                $serial.addClass('form__input-error');
                $serial.removeClass('form__input-ok');
                $serialError.show();
                return;
            }
            $serial.removeClass('form__input-error');
            $serial.addClass('form__input-ok');
            $serialError.hide();
            if (parseInt(data.repeated_flag)) {
                $admisStatus.val(3);
            }
            if (data.table) {
                $serialInfo.html(data.table);
            }
            updateAdmissionStatus(data.repeated_flag);
        });
    }

    function updateAdmissionStatus(repeatedFlag) {
        let clientType = parseInt($clientType.val());
        if (clientType == 1 && parseInt($receptStatus.val()) != 6) {
            $saleDate.prop('required', true);
        }
        if ($admisStatus.val() == 3 && repeatedFlag) { // Повторный
            return;
        }
        if (clientType == 2 && $saleDate.val() != '') {
            $admisStatus.val(2); // Клиентский
            if (parseInt($receptStatus.val()) != 6) {
                $saleDate.prop('required', true);
            }
        } else if (clientType == 2 && $saleDate.val() == '') {
            $admisStatus.val(1); // Предторговый   
            $saleDate.prop('required', false);
        } else if (clientType == 1) {
            $admisStatus.val(2); // Клиентский  
        }
    }


    function updateRepairStatus() {
        if (!$saleDate.val()) {
            return;
        }
        let statusID = 0;
        let d = $saleDate.val().split('.');
        let d2 = $receiveDate.val().split('.');
        let date1 = new Date(d[2], d[1] - 1, d[0]);
        let date2 = new Date(d2[2], d2[1] - 1, d2[0]);
        let daysLag = Math.ceil(Math.abs(date2.getTime() - date1.getTime()) / (1000 * 3600 * 24));
        if (daysLag > parseInt($warranty.val())) {
            if (userData['role'] != 'admin' && userData['role'] != 'taker' && userData['role'] != 'slave-admin') {
                $warrantOption.prop('disabled', true);
            }
            statusID = 5; //Условно-гарантийный
            $saleDateError.show();
        } else {
            $warrantOption.prop('disabled', false);
            statusID = 1; //Гарантийный
            $saleDateError.hide();
        }
        if (userData.role != 'taker' && userData.role != 'admin' && userData.role != 'slave-admin') {
            $receptStatus.val(statusID);
        }
    }

    function toggleSections() {
        let clientType = parseInt($clientType.val());
        if (!clientType) {
            $privateSection.hide();
            $legalSection.hide();
            return;
        }
        $legalSection.show();
        if (parseInt($receptStatus.val()) != 6) {
            $('#shop-name').prop('required', true);
        }
        if (userData.role != 'taker' && userData.role != 'slave-admin' && parseInt($receptStatus.val() != 6)) {
            $('#shop-address').prop('required', true);
        }
        if (clientType == 2) {
            if (!$saleDate.val()) {
                $privateSection.hide();
                $('#client-name').prop('required', false);
                $('#client-address').prop('required', false);
                $('#client-phone').prop('required', false);
            } else {
                $privateSection.show();
                $('#client-name').prop('required', true);
                $('#client-address').prop('required', true);
                $('#client-phone').prop('required', true);
            }
        } else {
            $privateSection.show();
        }
    }

    function submit() {
        let id = getFormErrors(),
            $item = null;
        if (id) {
            $item = $('#' + id);
            $item.tooltipster('content', 'Пожалуйста, выберите вариант и/или заполните поле дополнения.');
            $item.tooltipster('open');
        }
        if (!$repairForm.valid()) {
            $item = $('.form__input-error:eq(0)');
        }
        if ($item) {
            let top = $item.offset().top - 100;
            $('html,body').stop().animate({
                scrollTop: top
            }, {
                duration: 500
            });
            return false;
        }
        $saveMode.val($(this).data('submit-trig'));
        $repairForm.submit();
    }

    function getFormErrors() {
        let fillFlag = 0;
        $('[name^=complex]').each(function(i, elem) {
            if ($(elem).prop('checked')) {
                fillFlag = 1;
                return false;
            }
        });
        if ($('#contents-extra').val().length) {
            fillFlag = 1;
        }
        if (!fillFlag) {
            return 'complex';
        }
        fillFlag = 0;
        $('[name^=visual]').each(function(i, elem) {
            if ($(elem).prop('checked')) {
                fillFlag = 1;
                return false;
            }
        });
        if (!fillFlag && !$('#visual-comment').val().length) {
            return 'visual';
        }
        return null;
    }

    function updateZones() {
        if ($onwayFlag.prop('checked')) {
            $onwaySection.show();
        } else {
            $onwaySection.hide();
        }
    }

    function updateOnwayComment() {
        if ($('[data-onway-flag]:checked:enabled').data('onway-flag') == 'approve') {
            $('#onway-reject-comment').hide();
        } else {
            $('#onway-reject-comment').show();
        }
    }

    function attachEvents() {

        $saleDate.on('blur', function() {
            updateRepairStatus();
            updateAdmissionStatus();
            toggleSections();
        });

        $receptStatus.on('change', function() {
            updateReceptStatus();
        });

        $clientType.on('change', function() {
            toggleSections();
            updateAdmissionStatus();
        });

        $receiveDate.on('blur', function() {
            updateRepairStatus();
        });

        $noSerialFlag.on('change', function() {
            updateSerial();
        });

        $serial.on('change', function() {
            updateSerial();
        });

        $anrp.on('change', function() {
            updateAnrp();
        });

        $onwayFlag.on('change', function() {
            updateZones();
        });

        $('[data-onway-flag]').on('change', updateOnwayComment);

        $warrantyCard.on('change', function() {
            updateWarrantOption();
        });

        $('[data-submit-trig]').on('click', submit);

        $('select[name="model_id"]').on('change', function() {
            $.get('/ajax.php?type=get_warranty_by_model&id=' + $(this).val(), function(datatwo) {
                $warranty.val(datatwo.warranty);
            });
        });

        $('input[name="visual[]"]').on('change', function() {
            if ($(this).data('group') == 'new') {
                $('input[data-group="old"]').prop('checked', false);
            } else if ($(this).data('group') == 'old') {
                $('input[data-group="new"]').prop('checked', false);
            }
        });

        $('input[name="complex[]"]').on('change', function() {
            if ($(this).data('group') == 'full') {
                $('input[data-group="part"]').prop('checked', false);
            } else if ($(this).data('group') == 'part') {
                $('input[data-group="full"]').prop('checked', false);
            }
        });

        $('select[readonly]').on('mousedown keydown', function() {
            return false;
        });


    }

});