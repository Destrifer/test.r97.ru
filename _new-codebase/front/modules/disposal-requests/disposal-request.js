document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    const $notif = $('#form-notif');
    let blockFlag = false;


    const errorCb = function(jqXHR) {
        console.error('Ошибка сервера');
        console.error(jqXHR.responseText);
    };


    $('[data-input="dispose-flag"]').on('change', function() {
        const numInput = this.closest('[data-part]').querySelector('[data-input="disposed-num"]');
        const commentInput = this.closest('[data-part]').querySelector('[data-input="comment"]');
        if (!this.checked) {
            numInput.setAttribute('readonly', true);
            numInput.value = 0;
            commentInput.removeAttribute('readonly');
        } else {
            numInput.removeAttribute('readonly');
            commentInput.setAttribute('readonly', true);
        }
    });


    $('#disposal-request-form').on('submit', function(event) {
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
            success: function(resp) {
                submitBtn.innerText = 'Сохранить';
                if (resp.error_flag == 1) {
                    $notif.addClass('error').html(resp.message).fadeIn();
                    return;
                }
                $notif.removeClass('error').html(resp.message).fadeIn(); 
                setTimeout(() => $notif.fadeOut(), 5000);
            },
            complete: function() {
                blockFlag = false;
            },
            error: errorCb
        });
    });


});