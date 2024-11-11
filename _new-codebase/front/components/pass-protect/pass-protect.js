document.addEventListener('DOMContentLoaded', function() {


    init();


    function init() {
        if (sessionStorage.getItem('passProtect')) {
            unblock();
        }
    }

    $('body').on('keydown', '#pass-protect-input', function(event) {
        if (event.keyCode === 13) {
            login();
        }
    });


    $('body').on('click', '[data-action]', function(event) {
        event.preventDefault();
        switch (this.dataset.action) {
            case 'pass-protect-login':
                login();
                break;

            case 'pass-protect-open':
                if (!this.classList.contains('disabled')) {
                    showLoginWindow();
                }
                break;

            case 'pass-protect-close':
                closeLoginWindow();
                break;
        }
    });


    function showLoginWindow() {
        $.fancybox.open(`<div style="width: 500px">
            <div style="margin-bottom: 24px"><input type="text" id="pass-protect-input" class="form__text" placeholder="Введите пароль..."></div>
            <div style="display: flex; justify-content: space-between">
                <button data-action="pass-protect-close" class="form__btn form__btn_secondary">Отмена</button>
                <button class="form__btn" data-action="pass-protect-login">Войти</button>
            </div>
        </div>`);
    }


    function closeLoginWindow() {
        $.fancybox.close();
    }


    function login() {
        const password = $('#pass-protect-input').val();
        $.ajax({
            type: 'POST',
            url: '/ajax.php?type=check-password',
            data: `password=${password}`,
            success: function(resp) {
                if (+resp['error_flag']) {
                    alert(resp['message']);
                    return;
                }
                sessionStorage.setItem('passProtect', 1);
                closeLoginWindow();
                unblock();
            },
        });
    }


    $(document).on('page:update', init);


    function unblock() {
        $('[data-pass-protect-input]').prop('readonly', false);
        $('[data-action="pass-protect-open"]').addClass('disabled');
        document.dispatchEvent(new CustomEvent('passprotect:unblock'));
    }


});