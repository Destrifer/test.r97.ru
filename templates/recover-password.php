<!doctype html>
<html>

<head>
  <meta charset=utf-8>
  <title>Восстановление пароля - Панель управления</title>
  <link href="/css/fonts.css" rel="stylesheet" />
  <link href="/css/style-without-forms.css" rel="stylesheet" />
  <script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js" ></script>
  <script src="/js/jquery-ui.min.js"></script>
  <script src="/js/jquery.placeholder.min.js"></script>
  <script src="/js/jquery.formstyler.min.js"></script>
  <script src="/js/main.js"></script>
  <script src="/_new-codebase/front/vendor/jquery.inputmask.bundle.min.js"></script>

  <!-- New codebase -->
  <style>
    * {
      box-sizing: border-box;
    }

    .recover-submit {
      height: 50px !important;
      border-radius: 0 !important;
    }

    #show-message-form {
      text-decoration: none;
      border-bottom: dotted 1px #77ad07;
    }
  </style>
  <link href="/_new-codebase/front/templates/main/css/grid.css" rel="stylesheet" />
  <link href="/_new-codebase/front/templates/main/css/form.css" rel="stylesheet" />
  <link href="/_new-codebase/front/templates/main/css/notice.css" rel="stylesheet" />
</head>

<body>

  <div class="viewport-wrapper">


    <div class="site-header">
      <div class="wrapper">

        <div class="logo">
          <a href="/"><img src="/i/logo.png" alt="" /></a>
          <span>Сервис</span>
        </div>


      </div>
    </div><!-- .site-header -->


    <div class="wrapper" style="max-width: 850px; padding-top: 2vw">

      <h2 style="margin-bottom: 1em">Восстановление пароля</h2>

      <form method="POST" action="/recover-password/">
        <section class="container gutters">
          <div class="row">
            <div class="col-8">
              <div class="form__cell">
                <label class="form__label">Логин или e-mail, под которым вы регистрировались:</label>
                <input type="text" name="request" required value="<?= $_POST['request'] ?? ''; ?>" class="form__text">
              </div>
            </div>

            <div class="col-4">
              <div class="form__cell form__cell_flex">
                <input type="hidden" name="token" value="<?= $token; ?>" />
                <input type="hidden" name="action" value="recover-password" />
                <button type="submit" class="form__btn recover-submit">Восстановить</button>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-12">
              <div class="form__field form__field_center">
                <?php if (!empty($message)) : ?>
                  <div class="form__notif" id="form-notif"><?= $message; ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </section>
      </form>

      <div class="notice notice" style="margin-bottom: 1em">
        <p>
          Если вы не помните логин, либо не получили письмо,
          отправьте сообщение администратору через форму ниже.
        </p>
      </div>

      <div style="margin-bottom: 1em">
        <p style="text-align: center">
          <a href="#" id="show-message-form">Показать форму</a>
        </p>
      </div>


      <form method="POST" style="display: none" id="message-form" action="/recover-password/">
        <section class="container gutters">
          <div class="row">
            <div class="col-12">
              <div class="form__cell">
                <label class="form__label">Ваш e-mail:</label>
                <input type="email" name="email" required value="<?= $_POST['email'] ?? ''; ?>" class="form__text">
              </div>
            </div>

            <div class="col-12">
              <div class="form__cell">
                <label class="form__label">Сообщение:</label>
                <textarea name="message" rows="5" class="form__text" placeholder="Полезная информация для восстановления доступа, например, название СЦ."><?= $_POST['message'] ?? ''; ?></textarea>
              </div>
            </div>

            <div class="col-12">
              <div class="form__cell form__cell_flex">
                <input type="hidden" name="token" value="<?= $token; ?>" />
                <input type="hidden" name="action" value="send-message" />
                <button type="submit" class="form__btn recover-submit">Отправить</button>
              </div>
            </div>
          </div>
        </section>
      </form>

      <div style="text-align: center; margin-top: 4em">
                <a href="/">Вход</a> / <a href="/registration/">Регистрация</a>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('show-message-form').addEventListener('click', function(event) {
        event.preventDefault();
        $('#message-form').slideDown();
        this.remove();
      });
    });
  </script>
</body>

</html>