document.addEventListener('DOMContentLoaded', function() {

    const $saveBtn = $('#save-attentions-trigger');
    let toSave = {};


    $('[data-attention-message]').on('input', function() {
        $saveBtn.show();
        toSave[this.dataset.attentionMessage] = this.innerText;
    });

    
    $saveBtn.on('click', function(){
        $saveBtn.hide();
        for (let id of Object.keys(toSave)){
            $.ajax({
                type: 'POST',
                url: '/repair-api.php',
                data: `action=update-attention-message&message_id=${id}&message=${toSave[id]}`
            });
        }
        toSave = {};
    });

});