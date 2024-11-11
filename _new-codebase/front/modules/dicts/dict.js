/* global $ */

document.addEventListener('DOMContentLoaded', function() {


    const $valueBlockTpl = $('#value-block-tpl').detach().removeAttr('id');


    $('body').on('click', '[data-action]', function(event) {
        event.preventDefault();
        switch (this.dataset.action) {
            case 'add-value-block':
                addValueBlock(this);
                break;
            case 'save-dict':
                save();
                break;
            case 'del-dict':
                if (confirm('Удалить словарь? Чтобы подтвердить удаление, нажмите кнопку "Сохранить".')) {
                    delDict(this);
                }
                break;
            case 'del-value-block':
                if (confirm('Удалить параметр?')) {
                    delValueBlock(this);
                }
                break;
        }
    });


    function save() {
        $.ajax({
            type: 'POST',
            url: '?ajax=save',
            processData: false,
            contentType: false,
            data: new FormData(document.getElementById('dict-form')),
            dataType: 'json',
            cache: false,
            success: function(resp) {
                if (+resp.error_flag) {
                    alert(resp.message);
                } else {
                    location.href = '/dicts/';
                }
            },
            error: function ajaxError(jqXHR) {
                console.log('Ошибка сервера');
                console.log(jqXHR.responseText);
            }
        });
    }


    function delDict(btn) {
        $(btn.closest('[data-dict-item]')).css({ 'opacity': '.5', 'pointer-events': 'none' });
        $('#del-flag').val(1);
    }


    function delValueBlock(btn) {
        $(btn.closest('[data-value-block]')).fadeOut(300, function() {
            this.remove();
            orderParams();
        });
    }


    function addValueBlock(btn) {
        const $item = $valueBlockTpl.clone();
        btn.insertAdjacentElement('beforeBegin', $item[0]);
        orderParams();
    }


    function orderParams() {
        const items = document.querySelectorAll('[data-value-block]');
        if (!items.length) {
            return;
        }
        let n = 0;
        items.forEach((item) => {
            item.querySelector('[data-input="name"]').setAttribute('name', `value[${n}][name]`);
            item.querySelector('[data-input="val"]').setAttribute('name', `value[${n}][val]`);
            item.querySelector('[data-input="description"]').setAttribute('name', `value[${n}][description]`);
            n++;
        });
    }

});