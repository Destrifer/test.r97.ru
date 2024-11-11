/* global $ */

function Repair() {

    const API_URL = '/repair-api.php';
    const errorFn = function(jqXHR) {
        console.log('Ошибка сервера');
        console.log(jqXHR.responseText);
    };

    /* Создать новый ремонт на основе текущего */
    this.createPrototype = function(repairID, cb) {
        query(`action=create-prototype&repair_id=${repairID}`, cb);
    };

    this.del = function(repairID, permFlag) {
        query(`action=del&repair_id=${repairID}&perm_flag=${(permFlag) ? 1 : 0}`);
    };

    this.needToConfirmMaster = function(masterID, repairID, cb) {
        query(`action=need-to-confirm-master&repair_id=${repairID}&master_id=${masterID}`, cb);
    };

    this.take = function(userID, repairID) {
        // TODO: устаревшая функция
        $.ajax({
            type: 'GET',
            cache: false,
            url: `/ajax.php?type=add_master&value=${userID}&id=${repairID}`,
            complete: function() {
                window.location.href = `/edit-repair/${repairID}/`;
            }
        });
    };

    this.setMaster = function(masterID, repairID) {
        query('action=set-master&master_id=' + masterID + '&repair_id=' + repairID);
    };

    this.getRepeated = function(repairID, serial) {
        // TODO: устаревшая функция
        $.fancybox.open({
            src: '/show-double/' + serial + '/' + repairID + '/',
            type: 'iframe'
        });
    };

    this.changeStatus = function(status, repairID) {
        // TODO: устаревшая функция
        $.ajax({
            type: 'GET',
            cache: false,
            url: '/ajax.php?type=update_repair_status&value=' + encodeURIComponent(status) + '&id=' + repairID
        });
    };

    this.changeApproveDate = function(newDate, repairID) {
        query('action=change-approve-date&date=' + newDate + '&repair_id=' + repairID);
    };

    /* Метка "проверить" */
    this.setAttentionFlag = function(attentionFlag, repairID, message) {
        query('action=change-attention-flag&attention_flag=' + attentionFlag + '&repair_id=' + repairID + '&message=' + message);
    };

    this.massChange = function(status, masterID, repairIDs, cb) {
        // TODO: устаревшая функция
        $.ajax({
            type: 'GET',
            cache: false,
            url: '/ajax.php?type=mass_update&status_admin=' + encodeURIComponent(status) + '&master_id=' + masterID + '&value=' + JSON.stringify(repairIDs),
            complete: cb
        });
    };

    function query(data, cb) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            cache: false,
            url: API_URL,
            data: data,
            success: function(resp) {
                if (cb) {
                    cb(resp);
                }
            },
            error: errorFn
        });
    }
}