<?php

use program\core;

if(!empty(core\App::$URLParams['action'])){
  switch(core\App::$URLParams['action']){
    case 'save':
      models\services\Settings::saveGroups();
      header('Location: /services-settings/');
      exit;
    break;

  }
}

$groups = models\services\Settings::getGroups();
?>
<!doctype html>
<html>

<head>
  <meta charset=utf-8>
  <title>Настройки сервисов - Панель управления</title>
  <link href="/css/fonts.css" rel="stylesheet" />
  <link href="/css/style.css" rel="stylesheet" />
  <script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js" ></script>
  <script src="/js/jquery-ui.min.js"></script>
  <script src="/js/jquery.placeholder.min.js"></script>
  <script src="/js/jquery.formstyler.min.js"></script>
  <script src="/js/main.js"></script>

  <script src="/notifier/js/index.js"></script>
  <link rel="stylesheet" type="text/css" href="/notifier/css/style.css">
  <link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
  <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
  <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
  <link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />
  <link href="/_new-codebase/front/templates/main/css/settings.css" rel="stylesheet"/>

</head>

<body>

  <div class="viewport-wrapper">

    <header class="site-header">
      <div class="wrapper">

        <div class="logo">
          <a href="/dashboard/"><img src="<?= $config['url']; ?>i/logo.png" alt="" /></a>
          <span>Сервис</span>
        </div>

        <div class="not-container">
          <button style="position:relative;    margin-left: 120px;   margin-top: 15px;" type="button" class="button-default show-notifications js-show-notifications animated swing">
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="30" height="32" viewBox="0 0 30 32">
              <defs>
                <g id="icon-bell">
                  <path class="path1" d="M15.143 30.286q0-0.286-0.286-0.286-1.054 0-1.813-0.759t-0.759-1.813q0-0.286-0.286-0.286t-0.286 0.286q0 1.304 0.92 2.223t2.223 0.92q0.286 0 0.286-0.286zM3.268 25.143h23.179q-2.929-3.232-4.402-7.348t-1.473-8.652q0-4.571-5.714-4.571t-5.714 4.571q0 4.536-1.473 8.652t-4.402 7.348zM29.714 25.143q0 0.929-0.679 1.607t-1.607 0.679h-8q0 1.893-1.339 3.232t-3.232 1.339-3.232-1.339-1.339-3.232h-8q-0.929 0-1.607-0.679t-0.679-1.607q3.393-2.875 5.125-7.098t1.732-8.902q0-2.946 1.714-4.679t4.714-2.089q-0.143-0.321-0.143-0.661 0-0.714 0.5-1.214t1.214-0.5 1.214 0.5 0.5 1.214q0 0.339-0.143 0.661 3 0.357 4.714 2.089t1.714 4.679q0 4.679 1.732 8.902t5.125 7.098z" />
                </g>
              </defs>
              <g fill="#000000">
                <use xlink:href="#icon-bell" transform="translate(0 0)"></use>
              </g>
            </svg>

            <div class="notifications-count js-count"></div>

          </button>
        </div>

        <div class="logout">

          <a href="/logout/">Выйти, <?= \models\User::getData('login'); ?></a>
        </div>

      </div>
    </header>

    <main class="wrapper">

      <?= top_menu_admin(); ?>

      <nav class="adm-tab"><?= menu_dash(); ?></nav>

      <br>
      <h2 style="text-align: center;">Настройки сервисов</h2>
      <br>

      <form action="?action=save" method="POST">

      <?php 
      foreach($groups as $group) {
        groupHTML($group);
      }
      ?>

        <div class="adm-finish">
          <div class="save">
            <button type="submit">Сохранить</button>
          </div>
        </div>

      </form>

    </main>
  </div>
</body>

</html>

<?php
function groupHTML(array $group){
  ?>

  <section class="param-section">
  <h3 class="param-title"><?= $group['name']; ?></h3>
  <div class="param-row">
    <div class="param-col param-col__name">АНРП:</div>
    <div class="param-col param-col__val">
      <select class="nomenu" name="country[<?= $group['country_id']; ?>][anrp_value]">
        <option value="1" <?= (($group['settings']['anrp_value'] == 1) ? 'selected' : ''); ?>>Оставлен на ответственное хранение</option>
        <option value="2" <?= (($group['settings']['anrp_value'] == 2) ? 'selected' : ''); ?>>Выдан на руки клиенту</option>
      </select>
    </div>
  </div>
</section>

<?php
}