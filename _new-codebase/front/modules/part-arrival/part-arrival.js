document.addEventListener('DOMContentLoaded', function () {

    let isBlocked = false;
    const $tpl = $('#part-tpl').clone();
    const $notif = $('#form-notif');

    initDatepicker($('[data-datetimepicker]'));
    initSelect2($('.select2'));
    initSelect2WithTPL($('.select2-tpl'));


    $('body').on('click', '[data-action]', function (event) {
        event.preventDefault();
        switch (this.dataset.action) {

            case 'add-part':
                addPart();
                break;

            case 'del-part':
                if (confirm('Удалить данные?')) {
                    delPart($(this));
                }
                break;
        }
    });


    $('#part-arrival-form').on('submit', function () {
        if (isBlocked) {
            return;
        }
        isBlocked = true;
        const data = new FormData(this);
        const submitBtn = this.querySelector('[type="submit"]');
        submitBtn.innerText = 'Сохранение...';
        $.ajax({
            type: 'POST',
            url: '?ajax',
            dataType: 'json',
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            success: function (resp) {
                submitBtn.innerText = 'Сохранить';
                if (+resp.is_error == 1) {
                    $notif.addClass('error').html(resp.message).fadeIn();
                    return;
                }
                location.href = '/parts-arrivals/';
            },
            complete: function () {
                isBlocked = false;
            }
        });
        return false;
    });


    function addPart() {
        const $newBlock = $tpl.clone();
        $newBlock.css('display', 'none');
        $('#triggers').before($newBlock);
        $newBlock.fadeIn();
        $('[data-action="del-part"]', $newBlock).show();
        initSelect2WithTPL($('.select2-tpl', $newBlock));
        return $newBlock;
    }


    function delPart($trigger) {
        $trigger.closest('[data-part-row]').remove();
    }


    function initDatepicker($elems) {
        $elems.datepicker({
            language: 'ru',
            autoClose: true,
            timepicker: true
        });
    }


    function initSelect2($elems) {
        $elems.select2({
            language: 'ru'
        });
    }


    function initSelect2WithTPL($elems) {
        $elems.select2({
            language: 'ru',
            templateResult: function (state) {
                const parts = state.text.split('::');
                if (parts.length == 1) {
                    return state.text;
                }
                let html = ` 
                <span class="s2-name">${parts[0]}</span>
                <span class="s2-info">${parts[1]}</span>`;
                return $(html);
            }
        });
    }
});