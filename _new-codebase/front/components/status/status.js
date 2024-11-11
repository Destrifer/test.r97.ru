/* global $ */

document.addEventListener('DOMContentLoaded', function() {

    let blockFlag = false;

    $('body').on('change selectmenuchange', '[data-status-select]', function() {
        if (this.value != 'Отклонен') {
            return;
        }
        $.fancybox.open({
            src: '/status/?ajax=get-reject-form&repair-id=' + this.dataset.repairId
        }, {
            type: 'ajax',
            clickSlide: false,
            clickOutside: false
        });
    });

    $('body').on('submit', '#reject-repair-form', function(event) {
        event.preventDefault();
        if (blockFlag) {
            return;
        }
        blockFlag = true;
        let $this = $(this);
        if (!$('[name=message]', this).val()) {
            blockFlag = false;
            return false;
        }
        $('[type="submit"]', this).html('Отправка...');
        $.ajax({
            type: 'POST',
            url: this.action,
            data: $this.serialize(),
            cache: false,
            complete: function() {
                let supportContainer = document.getElementById('support-container');
                console.log(supportContainer);
                if(supportContainer){
                    let d = new Date();
                    let str = d.getFullYear() + '-' + ('0'+(d.getMonth() + 1)).slice(-2) + '-' + ('0'+d.getDate()).slice(-2) + ' ' + ('0'+d.getHours()).slice(-2) + ':' + ('0'+d.getMinutes()).slice(-2) + ':' + ('0'+d.getSeconds()).slice(-2);
                    supportContainer.innerHTML += `
            <table id="answer">
            <tr>
                <td width="100px">Поддержка</td>
                <td width="200px">${str}</td>
            <td style="text-align:left;">Причина отклонения ремонта: ${$('[name=message]', $this).val()}</td>
            </tr>
            </table><br></br>`;
                }
                parent.$.fancybox.close();
            },
            error: function(jqXHR) {
                console.log('Ошибка сервера');
                console.log(jqXHR.responseText);
            }
        });
        return false;
    });

    $('body').on('click', '[data-close-fancybox-trigger]', function(event) {
        event.preventDefault();
        parent.$.fancybox.close();
        return false;
    });
});