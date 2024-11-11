<?php

use program\core\App;
?>

<!doctype html>
<html>

<head>
  <meta charset=utf-8>
  <title>Регистрация - Панель управления</title>
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
  </style>
  <link href="/_new-codebase/front/templates/main/css/grid.css" rel="stylesheet" />
  <link href="/_new-codebase/front/templates/main/css/form.css" rel="stylesheet" />
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

    <h2 style="margin-bottom: 1em">Регистрация</h2>

      <form id="send" method="POST" action="/registration/">
        <section class="container gutters">
          <div class="row">

            <div class="col-6">
              <div class="form__cell">
                <label class="form__label form__label_required">Логин:</label>
                <input type="text" name="login" required value="<?= $_POST['login'] ?? ''; ?>" class="form__text">
              </div>
            </div>

            <div class="col-6">
              <div class="form__cell">
                <label class="form__label form__label_required">Ваше имя или название СЦ:</label>
                <input type="text" name="nickname" required value="<?= $_POST['nickname'] ?? ''; ?>" class="form__text">
              </div>
            </div>

            <div class="col-6">
              <div class="form__cell">
                <label class="form__label form__label_required">E-mail для связи:</label>
                <input type="email" name="email" required value="<?= $_POST['email'] ?? ''; ?>" class="form__text">
              </div>
            </div>

            <div class="col-6">
              <div class="form__cell">
                <label class="form__label form__label_required">Телефон:</label>
                <input type="text" name="phone" required value="<?= $_POST['phone'] ?? ''; ?>" class="form__text">
              </div>
            </div>

            <div class="col-6">
              <div class="form__cell">
                <label class="form__label form__label_required">Пароль:</label>
                <input type="password" name="password" required value="<?= $_POST['password'] ?? ''; ?>" class="form__text">
              </div>
            </div>

            <div class="col-6">
              <div class="form__cell">
                <label class="form__label form__label_required">Подтверждение пароля:</label>
                <input type="password" name="password_repeat" required value="<?= $_POST['password_repeat'] ?? ''; ?>" class="form__text">
              </div>
            </div>

          </div>

          <div class="row">

            <div class="col-12">
              <div class="form__field form__field_center form__field_final">
                <input type="hidden" name="token" value="<?= $token; ?>" />
                <input type="hidden" name="action" value="registration" />
                <button type="submit" class="form__btn">Регистрация</button>
              </div>

              <div class="form__field form__field_center">
                <?php if (!empty($message)) : ?>
                  <div class="form__notif" id="form-notif"><?= $message; ?></div>
                <?php endif; ?>
              </div>

              <div class="form__field form__field_center">
                <a href="/">Вход</a> / <a href="/recover-password/">Восстановить пароль</a>
              </div>
            </div>

          </div>
        </section>
      </form>

    </div>
  </div>
</body>

</html>