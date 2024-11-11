<?php


function content_list()
{
  global $db;
  $content_list = '';
  if (\models\User::hasRole('admin', 'slave-admin', 'taker', 'master')) {
    $sql = mysqli_query($db, 'SELECT * FROM `models` WHERE `is_deleted` = 0;');
    if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
        $cat = cat_by_id($row['cat']);
        $true2 = $cat['travel'] == 1 ? 'Да' : 'Нет';
        $dism = $cat['install_flag'] ? 'Да' : 'Нет';
        $content_list .= '<tr>
      <td >' . $row['id'] . '</td>
      <td style="width:150px;">' . $row['model_id'] . '</td>
      <td>' . $row['brand'] . '</td>
      <td>' . $row['name'] . '</td>
      <td>' . cat_by_id($row['cat'])['name'] . '</td>

      <td style="width:150px"><input class="editable" style="width:100px;" type="text" name="price_usd" value="' . $row['price_usd'] . '" data-id="' . $row['id'] . '" > $</td></td>
      <td>' . $row['service'] . '</td>
      <td>' . $true2 . '</td>
      <td>' . $dism  . '</td>
      <td align="center" class="linkz" >
      <a class="t-3" title="Редактировать карточку" href="/edit-model/' . $row['id'] . '/" ></a>
      ' . ((!\models\User::hasRole('master', 'taker')) ? '<a class="t-5" title="Удалить карточку" onclick=\'return confirm("Вы уверены, что хотите удалить #' . $row['id'] . '?")\'  style="float:right" href="/del-model/' . $row['id'] . '/"></a></td>' : '') . '</tr>';
      }
    }
    return $content_list;
  }
}

function cat_by_id($id)
{
  global $db;
  $sql = mysqli_query($db, 'SELECT * FROM `cats` where `id` = \'' . $id . '\' LIMIT 1;');
  while ($row = mysqli_fetch_array($sql)) {
    $content = $row;
  }
  return $content;
}

?>
<!doctype html>
<html>

<head>
  <meta charset=utf-8>
  <title>Модели - Панель управления</title>
  <link href="/css/fonts.css" rel="stylesheet" />
  <link href="/css/style.css" rel="stylesheet" />
  <script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"></script>
  <script src="/js/jquery-ui.min.js"></script>
  <script src="/js/jquery.placeholder.min.js"></script>
  <script src="/js/jquery.formstyler.min.js"></script>
  <script src="/js/main.js"></script>

  <script src="/notifier/js/index.js"></script>
  <link rel="stylesheet" href="/notifier/css/style.css">
  <link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
  <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
  <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
  <link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

  <script src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
  <link rel="stylesheet" href="/css/datatables.css">

  <script>
    // Таблица
    $(document).ready(function() {

      $('#table_content').dataTable({
        stateSave: false,
        "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>',
        "pageLength": <?= $config['page_limit']; ?>,
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
        },
        initComplete: function() {
          this.api().columns().every(function() {
            var column = this;
            if (column.selector.cols == 5) {
              var select = $('<select><option value=""></option></select>')
                .appendTo($(column.footer()).empty())
                .on('change', function() {
                  var val = $.fn.dataTable.util.escapeRegex(
                    $(this).val()
                  );

                  column
                    .search(val ? '^' + val + '$' : '', true, false)
                    .draw();
                });

              column.data().unique().sort().each(function(d, j) {
                select.append('<option value="' + d + '">' + d + '</option>')
              });
            }
          });
        }
      });

      $(document).on('change', 'input.editable', function() {
        var id = $(this).data('id');
        var type_field = $(this).attr('name');
        var value = $(this).val();
        if (value) {

          $.get("/ajax.php?type=update_price_model&value=" + value + "&id=" + id, function(data) {

            //$('select[name=parts_parts]').html(data.html).selectmenu( "refresh" );
            //$('input[name="serial_parts_hidden"]').val(value);


          });

        }


        return false;
      });

    });
  </script>
  <style>
thead th {
    padding: 10px 12px !important;
}
    </style>
</head>

<body>

  <div class="viewport-wrapper">

    <div class="site-header">
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
    </div><!-- .site-header -->

    <div class="wrapper">

      <?= top_menu_admin(); ?>

      <div class="adm-tab">

        <?= menu_dash(); ?>

      </div><!-- .adm-tab -->
      <br>
      <h2>Обслуживаемые модели</h2>

      <div class="adm-catalog">

        <div class="add">
          <a style="width: auto;padding-left: 7px;padding-right: 7px;margin-right:16px" href="/add-model/" class="button">Добавить модель</a>
        </div>

        <br>
        <table id="table_content" class="display" cellspacing="0" width="100%">
          <tfoot>
            <th align="left">№</th>
            <th align="left">Код</th>
            <th align="left">Бренд</th>
            <th align="left">Товар</th>
            <th align="left">Категория</th>
            <th align="left" style="width:150px">Цена ($)</th>
            <th align="left">Обслуживание</th>
            <th align="left">Выезд</th>
            <th align="left">Демонтаж/монтаж</th>
            <th align="center">Операции</th>
          </tfoot>
          <thead>
            <tr>
              <th align="left">№</th>
              <th align="left">Код</th>
              <th align="left">Бренд</th>
              <th align="left">Товар</th>
              <th align="left">Категория</th>
              <th align="left" style="width:150px">Цена ($)</th>
              <th align="left">Обслуживание</th>
              <th align="left">Выезд</th>
              <th align="left">Демонтаж/монтаж</th>
              <th align="center">Операции</th>
            </tr>
          </thead>

          <tbody>
            <?= content_list(); ?>
          </tbody>
        </table>



      </div>


    </div>
  </div>
</body>

</html>