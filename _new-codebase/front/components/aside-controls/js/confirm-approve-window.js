/* global $ */

const confirmApproveWindow = {};

confirmApproveWindow.repairID = 0;

confirmApproveWindow.open = function(repairID) {
    this.repairID = repairID;
    $.fancybox.open({
        src: '/repair-card/?ajax=get-confirm-approve-window',
        type: 'ajax',
        clickSlide: false,
        clickOutside: false,
        ajax: {
            settings: {
                type: 'POST',
                data: {
                    fancybox: true,
                    repair_id: repairID
                }
            }
        }
    });
};


confirmApproveWindow.onSubmit = null;


/* Сохраняет окно оставленных запчастей */
$('body').on('click', '#approve-confirm-control', function() {
    let anrpParam = -1;
    let curAnrpParam = -1;
    let $input = $('[name="anrp_param"]:checked');
    if ($input.length) {
        anrpParam = $input.val();
        curAnrpParam = document.getElementById('cur-anrp-param').value;
    }
    $.ajax({
        type: 'POST',
        url: '/ajax.php?type=save-settings',
        data: 'anrp_param=' + anrpParam + '&repair_id=' + confirmApproveWindow.repairID + '&cur_anrp_param=' + curAnrpParam,
        success: function(resp) {
            $.fancybox.close();
            if (+resp['error_flag']) {
                alert(resp['message']);
                return;
            }
            if (!confirmApproveWindow.onSubmit) {
                return;
            }
            confirmApproveWindow.onSubmit();
        },
    });
    $.fancybox.close();
});