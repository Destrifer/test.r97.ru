<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Вход - Панель управления</title>
<link href="/css/fonts.css" rel="stylesheet" />
<link href="/css/style.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="date/jquery.datetimepicker.css"/>
<link rel="stylesheet" href="/redactor/redactor.css" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="/js/jquery-ui.min.js"></script>
<script src="/js/jquery.placeholder.min.js"></script>
<script src="/js/jquery.formstyler.min.js"></script>
<script src="/js/main.js"></script>
</head>

<body>

<div class="viewport-wrapper">


<div class="site-header">
  <div class="wrapper">

    <div class="logo">
      <a href="/dashboard/"><img src="/i/logo.png?v=1" alt=""/></a>
      <span>Сервис</span>
    </div>


  </div>
</div><!-- .site-header -->


<div class="wrapper">

           <form id="send" method="POST" action="/login/">
            <div class="adm-form">
                    <?php if (isset($message)) { echo '<p style="color:red">'.$message.'</p>'; } ?>
                    <div class="item" style="display:block;width:100%;">
              <div class="level">Логин:</div>
              <div class="value">
                <input type="text" value="<?= $_POST['login'] ?? ''; ?>" name="login" required style="width:auto" />
              </div>
            </div>

             <div class="item" style="display:block;width:100%;">
              <div class="level">Пароль:</div>
              <div class="value">
                  <input type="password" value="<?= $_POST['password'] ?? ''; ?>" name="password" required style="width:auto" />
              </div>
            </div>

            <div class="item" style="display:block;width:100%;">
              <div class="value">
                  <label><input type="checkbox" value="1" <?= ((isset($_POST['is_remember'])) ? 'checked' : ''); ?> name="is_remember"> Запомнить</label>
              </div>
            </div>

                <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="token" value="<?= $token; ?>" />
              <input type="hidden" name="action" value="login" />
              <button type="submit" >Вход</button><br>
            </div>
                         <br> <a href="/registration/">Регистрация</a> / <a href="/recover-password/">Восстановить пароль</a>
            </div>
        </div>

      </form>



        </div>
  </div>
</body>
</html>