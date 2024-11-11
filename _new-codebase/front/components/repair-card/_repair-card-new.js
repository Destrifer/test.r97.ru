'use strict';
/* global $, repairData */

document.addEventListener('DOMContentLoaded', function() {
    let elem = document.getElementById('summary-status-select');
    if (!elem) {
        return;
    }
    elem.addEventListener('change', saveStatus);


    function saveStatus() {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            cache: false,
            url: '/repair-card/?ajax=save-status',
            data: 'status=' + this.value + '&repair_id=' + repairData.id,
            error: function(jqXHR) {
                console.log('Ошибка сервера');
                console.log(jqXHR.responseText);
            }
        });
    }
});