<?php

use program\core;

define('VER', '3.6');

if (!empty(core\App::$URLParams['ajax'])) {
  switch (core\App::$URLParams['ajax']) {
    case 'save':
      try {
        $res = models\Infobase::save($_POST['serial_id'], $_POST['file_id'], $_POST['cat_id'], $_POST['name'], $_POST['descr']);
        if (!models\Infobase::$message) {
          echo json_encode($res);
        } else {
          echo json_encode(['error' => models\Infobase::$message]);
        }
      } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
      }
      break;

    case 'search':
      echo json_encode(models\Models::search($_POST['request']));
      break;

    case 'load-serials':
      echo json_encode(models\Serials::getSerials($_POST['model_id']));
      break;

    case 'load-cur-data':
      $serial = models\Serials::getSerialByID($_POST['serial_id']);
      $serials = models\Infobase::getFileSerials($_POST['file_id']);
      echo json_encode(['serial' => $serial, 'serials' => $serials]);
      break;

    case 'save-models':
      models\Infobase::copyToSerials($_POST['file_id'], $_POST['serial_id']);
      echo json_encode([]);
      break;

    case 'del-file':
      try {
        models\Infobase::delFile($_POST['file_id']);
        echo json_encode([]);
      } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
      }
      break;
  }
  exit;
}

function content_list()
{
  global $db;
  $content_list = '';
  if (!\models\User::hasRole('admin')) {
    return '';
  }
  $sql = mysqli_query($db, 'SELECT m.`id`, m.`name`, c.`name` AS cat_name FROM `models` m LEFT JOIN `cats` c ON c.`id` = m.`cat` ORDER BY m.`id` DESC');
  while ($row = mysqli_fetch_assoc($sql)) {
    $content_list .= '<tr>
      <td >' . $row['id'] . '</td>
      <td>' . $row['name'] . '</td>
      <td>' . $row['cat_name'] . '</td> 
      <td style="max-width: 600px">' . getFilesHTML(models\Infobase::getFilesByModelID($row['id'])) . '</td>
      <td class="linkz"><a class="t-3" title="Редактировать" href="/infobase/?action=edit&model-id=' . $row['id'] . '" ></a></td>
      </tr>';
  }
  return $content_list;
}

if (!empty(core\App::$URLParams['action'])) {
  switch (core\App::$URLParams['action']) {
    case 'edit':
      $serials = models\Serials::getSerials(core\App::$URLParams['model-id']);
      $model = models\Models::getModelByID(core\App::$URLParams['model-id']);
      break;
  }
}


?>
<!doctype html>
<html>

<head>
  <meta charset=utf-8>
  <title>Техническая документация - Панель управления</title>
  <link href="/css/fonts.css" rel="stylesheet" />
  <link href="/css/style.css" rel="stylesheet" />
  <script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"></script>
  <script src="/js/jquery-ui.min.js"></script>
  <script src="/js/jquery.placeholder.min.js"></script>
  <script src="/js/jquery.formstyler.min.js"></script>

  <script src="/notifier/js/index.js"></script>
  <link href="/notifier/css/style.css" rel="stylesheet">
  <link href="/_new-codebase/front/vendor/animate.min.css" rel="stylesheet" />
  <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
  <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
  <link href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" rel="stylesheet" />

  <script src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
  <link href="/css/datatables.css" rel="stylesheet">
  <script>
    $(document).ready(function() {

      var groupColumn = 2;

      $('#table_content').dataTable({
        stateSave: false,
        "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>',
        "pageLength": <?= $config['page_limit']; ?>,
        "columnDefs": [{
          "visible": false,
          "targets": groupColumn
        }],
        "order": [
          [groupColumn, 'asc']
        ],
        "drawCallback": function(settings) {
          var api = this.api();
          var rows = api.rows({
            page: 'current'
          }).nodes();
          var last = null;
          api.column(groupColumn, {
            page: 'current'
          }).data().each(function(group, i) {
            if (last != group) {
              $(rows).eq(i).before(
                '<tr class="group"><td colspan="9">' + group + '</td></tr>'
              );
              last = group;
            }
          });
        },
        "oLanguage": {
          "sLengthMenu": "Показывать _MENU_ записей на страницу",
          "sZeroRecords": "Записей нет.",
          "sInfo": "Показано от _START_ до _END_ из _TOTAL_ записей",
          "sInfoEmpty": "Записей нет.",

          "oPaginate": {
            "sFirst": "Первая",
            "sLast": "Последняя",
            "sNext": "Следующая",
            "sPrevious": "Предыдущая",
          },
          "sSearch": "Поиск",
          "sInfoFiltered": "(отфильтровано из _MAX_ записи/(ей)"
        }
      });

    });
  </script>

  <!-- New codebase -->
  <link href="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.css" rel="stylesheet">
  <link href="/_new-codebase/front/modules/infobase/models-select.css?v=<?= VER; ?>" rel="stylesheet">
  <link href="/_new-codebase/front/modules/infobase/infobase.css?v=<?= VER; ?>" rel="stylesheet">
</head>

<body>

  <main class="viewport-wrapper">

    <header class="site-header">
      <div class="wrapper">

        <div class="logo">
          <a href="/dashboard/"><img src="/i/logo.png" alt="" /></a>
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
    </header><!-- .site-header -->

    <div class="wrapper">

      <?= top_menu_admin(); ?>

      <nav class="adm-tab">

        <?= menu_dash(); ?>

      </nav><!-- .adm-tab -->

      <?php if (empty(core\App::$URLParams['action'])) : ?>

        <h2>Техническая документация</h2>

        <section class="adm-catalog">

          <table id="table_content" class="display" cellspacing="0" width="100%">
            <thead>
              <tr>
                <th align="left">№</th>
                <th align="left">Модель</th>
                <th align="left"></th>
                <th align="left">Загруженные файлы</th>
                <th align="left"></th>
              </tr>
            </thead>

            <tbody>
              <?= content_list(); ?>
            </tbody>
          </table>

        </section>

      <?php elseif (core\App::$URLParams['action'] == 'edit') : ?>

        <h2 style="margin-bottom: 16px;">Модель <a href="/edit-model/<?= $model['id']; ?>/" target="_blank"><?= $model['name']; ?></a></h2>
        <h3 style="font-weight: 400;font-size: 21px;"><a href="/infobase/" style="text-decoration: none">← Вернуться ко всем моделям</a></h3>

        <div class="infobase__search">
          <input type="search" class="infobase__search-input" data-input="search" placeholder="Поиск...">
        </div>

        <?php
        displayFileRow([]); // template 
        ?>

        <section id="infobase">

          <?php foreach ($serials as $serial) : ?>
            <div class="serial" data-serial="<?= $serial['id']; ?>">
              <header class="serial__header">
                <h3 class="serial__title" data-search="serial"><?= $serial['serial']; ?></h3>
                <p class="serial__subtitle">Завод:
                  <b data-search="provider"><?= ((empty($serial['provider'])) ? '- не указан -' : $serial['provider']); ?></b>,
                  заказ: <b data-search="order"><?= ((empty($serial['order'])) ? '- не указан -' : $serial['order']); ?></b>
                </p>
              </header>

              <section class="serial__body">

                <?php $files = models\Infobase::getFiles(1, $serial['id']); ?>

                <h4 class="serial__title2"><span class="serial__title2-text">Прошивки <?= (($files) ? '(' . count($files) . ' шт.)' : '') ?></span>
                  <div class="serial__btn serial__btn_add" data-target="firmware" data-action="add-file">+ Добавить</div>
                </h4>

                <div class="serial__files-table" data-cat="1" data-name="firmware">
                  <?php
                  foreach ($files as $f) {
                    displayFileRow($f);
                  }
                  ?>
                </div>

                <?php $files = models\Infobase::getFiles(2, $serial['id']); ?>

                <h4 class="serial__title2"><span class="serial__title2-text">Схемы <?= (($files) ? '(' . count($files) . ' шт.)' : '') ?></span>
                  <div class="serial__btn serial__btn_add" data-target="schemes" data-action="add-file">+ Добавить</div>
                </h4>

                <div class="serial__files-table" data-cat="2" data-name="schemes">
                  <?php
                  foreach ($files as $f) {
                    displayFileRow($f);
                  }
                  ?>
                </div>

                <?php $files = models\Infobase::getFiles(3, $serial['id']); ?>

                <h4 class="serial__title2"><span class="serial__title2-text">Документы <?= (($files) ? '(' . count($files) . ' шт.)' : '') ?></span>
                  <div class="serial__btn serial__btn_add" data-target="bullt" data-action="add-file">+ Добавить</div>
                </h4>

                <div class="serial__files-table" data-cat="3" data-name="bullt">
                  <?php
                  foreach ($files as $f) {
                    displayFileRow($f);
                  }
                  ?>
                </div>

              </section>
            </div>
          <?php endforeach; ?>
        </section>
      <?php endif; ?>


    </div>
  </main>

  <div id="select-models-modal" style="width: 85%; display: none">
    <section id="models-select">
      <h3 style="text-align: center;margin-bottom: 16px;font-weight: 400">Модель <a href="/edit-model/<?= $model['id']; ?>/" target="_blank" data-name="model"><?= $model['name']; ?></a>, завод: <b data-name="provider">--</b>, заказ: <b data-name="order">--</b></h3>
      <div class="models-select">
        <div class="models-select__col">
          <input type="search" id="models-search" data-default="<?= $model['name']; ?>" class="models-select__search-input" placeholder="Модель...">
          <ul class="models-select__result" id="models-search-result"></ul>
        </div>
        <div class="models-select__col">
          <input type="search" id="serials-search" class="models-select__search-input" placeholder="Поиск...">
          <ul class="models-select__result" id="serials-search-result"></ul>
          <label style="padding:16px 11px;display:block;border-top: solid 4px #78af01;"><input type="checkbox" id="select-all-flag"> Выбрать всё</label>
        </div>
      </div>

      <div id="selected-models" class="models-select__selected"></div>

      <div class="models-select__btns">
        <div class="models-select__btn models-select__btn_cancel" data-select-models-action="cancel">Отмена</div>
        <div class="models-select__btn models-select__btn_save" data-select-models-action="save">Сохранить</div>
      </div>

      <input type="hidden" id="cur-file-id">
      <input type="hidden" id="cur-serial-id">
    </section>
  </div>

  <!-- New codebase -->
  <script src='/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.js'></script>
  <script src="/_new-codebase/front/modules/infobase/models-select.js?v=<?= VER; ?>"></script>
  <script src="/_new-codebase/front/modules/infobase/infobase.js?v=<?= VER; ?>"></script>
</body>

</html>


<?php

function displayFileRow(array $row)
{
  if (!$row) {
    $id = 'id="row-tpl"';
    $row = ['id' => 0, 'name' => '', 'url' => '', 'filename' => '(загрузите файл)', 'size' => '--', 'upload_date' => '--', 'descr' => ''];
  } else {
    $id = '';
  }
?>
  <div class="file-row <?= ((empty($row['url'])) ? 'file-row_empty' : ''); ?>" <?= $id; ?> data-file-id="<?= $row['id']; ?>">

    <div class="file-col" style="width:65%">
      <span class="file-info-item file-info-item_filename">Имя файла: <b class="file-info-val" style="margin-right: 18px" data-name="filename"><?= $row['filename']; ?></b>
        <div class="file-info-tip"></div>
      </span>
      <span class="file-info-item">Название файла: <b class="file-info-val" data-name="name"><?= ((!$row['name']) ? '--' : $row['name']); ?></b></span>
      <span class="file-info-item">Дата загрузки: <b class="file-info-val" data-name="upload-date"><?= $row['upload_date']; ?></b></span>
    </div>

    <div class="serial__btns-panel">
      <div class="serial__btns-panel-row">
        <a href="<?= $row['url']; ?>" style="margin-right: 14px;<?= ((empty($row['url'])) ? 'display: none' : ''); ?>" target="_blank" class="serial__btn serial__btn_download" data-name="download-file">Скачать (<span class="file-info-val" data-name="size"><?= $row['size']; ?></span>)</a>
        <div class="serial__btn serial__btn_del" data-action="del-file">Удалить</div>
      </div>
      <div class="serial__btns-panel-row">
        <div class="serial__btn serial__btn_edit" data-action="edit-file">Добавить / редактировать</div>
        <div class="serial__btn serial__btn_models" style="margin-left:14px;" data-action="select-models">Модели</div>
      </div>
    </div>

    <div style="display:none" data-edit-serial-modal>
      <div class="file-edit-row">
        <p>Название файла:</p>
        <textarea class="file-name-input" data-input="name" maxlength="128"><?= $row['name']; ?></textarea>
      </div>
      <div class="file-edit-row">
        <p>Подробное описание:</p>
        <textarea class="file-descr-input" data-input="descr"><?= $row['descr']; ?></textarea>
      </div>
      <div class="file-edit-row">
        <label><input type="file" data-input="upload-file"></label>
      </div>
      <div class="file-edit-row">
        <div class="serial__btn serial__btn_save" data-action="save">Сохранить</div>
      </div>
    </div>


    <div style="width: 100%">
      <div class="serial__descr-link" data-action="descr">Подробное описание</div>
      <div style="display: none" class="serial__descr">
        <div class="serial__descr-text" data-name="descr"><?= (($row['descr']) ? $row['descr'] : '<i>(описание отсутствует)</i>'); ?></div>
      </div>
    </div>

  </div>
<?php
}


function getFilesHTML(array $infobase)
{
  if (!$infobase) {
    return '';
  }
  ob_start();
  foreach ($infobase as $cat) {
    if (empty($cat['items'])) {
      continue;
    }
    echo '<div>
    <h3 style="margin-bottom: 11px">' . $cat['name'] . '</h3>';
    foreach ($cat['items'] as $item) {
      echo '<div style="margin-bottom: 11px;word-break: break-all;"><a href="' . $item['url'] . '" title="' . $item['filename'] . '">' . $item['name'] . '</a> <div style="display: none;">' . $item['filename'] . '</div></div>';
    }
    echo '</div>';
  }
  return ob_get_clean();
}
