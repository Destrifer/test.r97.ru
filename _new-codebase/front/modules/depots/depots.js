/* global $ */

document.addEventListener('DOMContentLoaded', function() {


    let blockFlag = false;
    const $notif = $('#form-notif');


    const errorCb = function(jqXHR) {
        console.error('Ошибка сервера');
        console.error(jqXHR.responseText);
    };


    $('body').on('click', '[data-action]', function(event) {
        event.preventDefault();
        switch (this.dataset.action) {
            case 'del-depot':
                delDepot($(this).closest('[data-name="depot-row"]'));
                break;
        }
    });


    $('#depot-form').on('submit', function() {
        if (blockFlag) {
            return;
        }
        blockFlag = true;
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
            success: function(resp) {
                submitBtn.innerText = 'Сохранить';
                if (resp.errorFlag == 1) {
                    $notif.addClass('error').html(resp.message).fadeIn();
                    return;
                }
                if (resp.redir_url) {
                    location.href = resp.redir_url;
                }
                $notif.removeClass('error').html(resp.message).fadeIn();
                setTimeout(() => {
                    $notif.fadeOut();
                }, 3000);
            },
            complete: function() {
                blockFlag = false;
            },
            error: errorCb
        });
        return false;
    });


    function delDepot($depotRow) {
        if (!confirm('Удалить склад?')) {
            return;
        }
        $.ajax({
            type: 'POST',
            url: '?',
            data: `ajax=del-depot&depot_id=${$depotRow.data('depot-id')}`,
            complete: function() {
                $depotRow.fadeOut();
            }
        });
    }


    $('.select2').select2();


    $('#depots-table').dataTable({
        stateSave: false,
        'dom': '<"top"flp<"clear">>rt<"bottom"ip<"clear">>',
        'pageLength': 50,
        'responsive': true,
        'oLanguage': {
            'sLengthMenu': 'Показывать _MENU_ записей на страницу',
            'sZeroRecords': 'Записей нет.',
            'sInfo': 'Показано от _START_ до _END_ из _TOTAL_ записей',
            'sInfoEmpty': 'Записей нет.',
            'oPaginate': {
                'sFirst': 'Первая',
                'sLast': 'Последняя',
                'sNext': 'Следующая',
                'sPrevious': 'Предыдущая',
            },
            'sSearch': 'Поиск',
            'sInfoFiltered': '(отфильтровано из _MAX_ записи/(ей)'
        }
    });

});