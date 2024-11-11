document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const $notif = $('#form-notif');
    const $notifCats = $('#form-notif-cats');
    let blockFlag = false;


    const errorCb = function (jqXHR) {
        console.error('Ошибка сервера');
        console.error(jqXHR.responseText);
    };


    $('.select2').select2();


    $('#staff-form').on('submit', function (event) {
        event.preventDefault();
        if (blockFlag) {
            return;
        }
        blockFlag = true;
        const data = new FormData(this);
        const submitBtn = this.querySelector('[type="submit"]');
        submitBtn.innerText = 'Сохранение...';
        $.ajax({
            type: 'POST',
            url: '?ajax=save',
            dataType: 'json',
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            success: function (resp) {
                submitBtn.innerText = 'Сохранить';
                if (resp.error_flag == 1) {
                    $notif.addClass('error').html(resp.message).fadeIn();
                    return;
                }
                $notif.fadeOut();
                location.href = '/users/';
            },
            complete: function () {
                blockFlag = false;
            },
            error: errorCb
        });
    });


    $('#user-cats-form').on('submit', function (event) {
        event.preventDefault();
        if (blockFlag) {
            return;
        }
        blockFlag = true;
        const data = new FormData(this);
        const submitBtn = this.querySelector('[type="submit"]');
        submitBtn.innerText = 'Сохранение...';
        $.ajax({
            type: 'POST',
            url: '?ajax=save-user-cats',
            dataType: 'json',
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            success: function (resp) {
                submitBtn.innerText = 'Сохранить';
                if (resp.error_flag == 1) {
                    $notifCats.addClass('error');
                }
                $notifCats.html(resp.message).fadeIn();   
            },
            complete: function () {
                blockFlag = false;
            },
            error: errorCb
        });
    });

});