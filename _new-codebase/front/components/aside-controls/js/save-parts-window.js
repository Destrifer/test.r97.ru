const savePartsWindow = {};

savePartsWindow.repairID = 0;

savePartsWindow.open = function(repairID) {
    this.repairID = repairID;
    $.fancybox.open({
        src: '/repair-card/?ajax=get-save-parts-window',
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


savePartsWindow.onSubmit = null;


/* Сохраняет окно оставленных запчастей */
$('body').on('click', '#check-all-parts', function() {
    $('.save-parts-table input[type="checkbox"]:visible').prop('checked', this.checked);
});

$('body').on('click', '#check-all-stand', function() {
    $('#save-parts-window tr[id="1"]').toggle();
});

/* Сохраняет окно оставленных запчастей */
$('body').on('submit', '#save-parts-window', function(event) {
    event.preventDefault();
    const data = new FormData(this);
    data.append('repair_id', savePartsWindow.repairID);
    $.ajax({
        type: 'POST',
        url: '/repair-card/?ajax=save-parts-window',
        processData: false,
        contentType: false,
        data: data,
        success: function(resp) {
            $.fancybox.close();
            if (+resp['error_flag']) {
                alert(resp['message']);
                return;
            }
            if (!savePartsWindow.onSubmit) {
                return;
            }
            savePartsWindow.onSubmit();
        },
        dataType: 'json'
    });
});