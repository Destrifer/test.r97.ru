/* global $ */

document.addEventListener('DOMContentLoaded', function() {

    let sendFirmwareFlag = false;

    $('[data-action="descr"]').on('click', function() {
        $(this).toggleClass('active');
        $(this).next().slideToggle();
    });


    $('#req-software-btn').on('click', function() {
        if (sendFirmwareFlag) {
            alert('Запрос уже отправлен.');
            return false;
        }
        let email = $('#req-software-email').val();
        if (!email.length) {
            alert('Пожалуйста, введите e-mail.');
            return false;
        }
        let type = $('#req-software-type').val();
        if (!type.length) {
            alert('Пожалуйста, выберите тип запроса.');
            return false;
        }
        $.ajax({
            type: 'POST',
            url: document.location.href + '?ajax=request-software',
            data: `email=${email}&type=${type}`,
            cache: false,
            dataType: 'json',
            success: function(resp) {
                alert(resp.message);
                if (!resp.error_flag) {
                    sendFirmwareFlag = true;
                }
            },
            error: function(jqXHR) {
                console.log('Ошибка сервера');
                console.log(jqXHR.responseText);
            }
        });
        return false;
    });


});