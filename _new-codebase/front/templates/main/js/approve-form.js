document.addEventListener('DOMContentLoaded', function() {


    $('[data-approve-form="approve-btn"]').on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        if (!confirm('Подтверждаете выбор?')) {
            return false;
        }
        const $form = $(this).closest('[data-approve-form="form"]');
        const $checked = $('[name="is_approved"]:checked', $form);
        if (!$checked.length) {
            alert('Пожалуйста, выберите "Одобрить" или "Отклонить".');
            return;
        }
        const isApproved = $checked.val();
        const comment = $('[data-input="approve-form-comment"]', $form).val();
        const repairID = $('[data-input="repair-id"]', $form).val();
        const action = this.dataset.action;
        $.ajax({
            type: 'POST',
            url: '/repair-api.php',
            dataType: 'json',
            data: `action=${action}&repair_id=${repairID}&comment=${comment}&is_approved=${isApproved}`,
            success: function(resp) {
                alert(resp.message);
                if(resp.new_status){
                    $('#summary-status-select').val(resp.new_status);
                }
            }
        });
    });


    $('[data-input="is-approved"]').on('change', function() {
        const $form = $(this).closest('[data-approve-form="form"]');
        const isApproved = +this.value;
        const $commentElem = $('[data-approve-form="approve-comment"]', $form);
        if (isApproved) {
            $commentElem.slideUp();
        } else {
            $commentElem.slideDown(300);
        }
    });



});