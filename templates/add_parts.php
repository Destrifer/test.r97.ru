<?php

# Сохраняем:
if ($_POST['send'] == 1) {



mysqli_query($db, 'INSERT INTO `parts` (
`cat`,
`model_id`,
`serial`,
`group`,
`list`,
`desc`,
`type`,
`weight`,
`price`,
`part`,
`brand`,
`codepre`,
`count`
) VALUES (
\''.mysqli_real_escape_string($db, $_POST['cat']).'\',
\''.mysqli_real_escape_string($db, $_POST['model_id']).'\',
\''.mysqli_real_escape_string($db, $_POST['serial']).'\',
\''.mysqli_real_escape_string($db, str_replace("'", '', $_POST['group'])).'\',
\''.mysqli_real_escape_string($db, $_POST['list']).'\',
\''.mysqli_real_escape_string($db, $_POST['desc']).'\',
\''.mysqli_real_escape_string($db, $_POST['type']).'\',
\''.mysqli_real_escape_string($db, $_POST['weight']).'\',
\''.mysqli_real_escape_string($db, $_POST['price']).'\',
\''.mysqli_real_escape_string($db, $_POST['part']).'\',
\''.mysqli_real_escape_string($db, $_POST['brand']).'\',
\''.mysqli_real_escape_string($db, $_POST['codepre']).'\',
\''.mysqli_real_escape_string($db, $_POST['count']).'\'
);') or mysqli_error($db);
$parent_id = mysqli_insert_id($db);

admin_log_add('Добавлена новая запчасть '.$_POST['list']);

$count = count($_POST['model_add_id']);
$count_arrays = 0;
while($count_arrays < $count) {
mysqli_query($db, 'INSERT INTO `parts` (
`cat`,
`model_id`,
`serial`,
`group`,
`list`,
`desc`,
`type`,
`weight`,
`price`,
`part`,
`brand`,
`codepre`,
`count`,
`parent_id`
) VALUES (
\''.mysqli_real_escape_string($db, $_POST['cat']).'\',
\''.mysqli_real_escape_string($db, $_POST['model_add_id'][$count_arrays]).'\',
\''.mysqli_real_escape_string($db, $_POST['serial_add'][$count_arrays]).'\',
\''.mysqli_real_escape_string($db, str_replace("'", '', $_POST['group'])).'\',
\''.mysqli_real_escape_string($db, $_POST['list']).'\',
\''.mysqli_real_escape_string($db, $_POST['desc']).'\',
\''.mysqli_real_escape_string($db, $_POST['type']).'\',
\''.mysqli_real_escape_string($db, $_POST['weight']).'\',
\''.mysqli_real_escape_string($db, $_POST['price']).'\',
\''.mysqli_real_escape_string($db, $_POST['part']).'\',
\''.mysqli_real_escape_string($db, $_POST['brand']).'\',
\''.mysqli_real_escape_string($db, $_POST['codepre']).'\',
\''.mysqli_real_escape_string($db, $_POST['count']).'\',
\''.mysqli_real_escape_string($db, $parent_id).'\'
);') or mysqli_error($db);
$count_arrays++;
}

header('Location: '.$config['url'].'parts/');
}

function models($cat_id) {
  global $db;
$content = array();
$sql = mysqli_query($db, 'SELECT * FROM `models` ;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }
      }
    return $content;
}


function cat($cat_id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `cats` where `name` != \'\';');

      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }
      }
    return $content;
}

function groups($cat_id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `groups` where `name` != \'\' order by `name` asc;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['name']) {
      $content .= '<option selected value="'.$row['name'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['name'].'">'.$row['name'].'</option>';
      }
      }
    return $content;
}

function serials_select($id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `serials` where `model_id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      if ($id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['serial'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['serial'].'</option>';
      }
      }
    return $content;
}

?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Панель управления</title>
<link href="<?=$config['url'];?>css/fonts.css" rel="stylesheet" />
<link href="<?=$config['url'];?>css/style.css" rel="stylesheet" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="<?=$config['url'];?>js/jquery-ui.min.js"></script>
<script src="<?=$config['url'];?>js/jquery.placeholder.min.js"></script>
<script src="<?=$config['url'];?>js/jquery.formstyler.min.js"></script>
<script src="<?=$config['url'];?>js/main.js"></script>

<script src="/_new-codebase/front/vendor/jquery-validation/jquery.validate.min.js"></script>
<script src="/_new-codebase/front/vendor/jquery-validation/additional-methods.min.js"></script>
<script src="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster-sideTip-shadow.min.css" />


<script src="<?=$config['url'];?>notifier/js/index.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$config['url'];?>notifier/css/style.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$config['url'];?>css/datatables.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />
<script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
<script src="/_new-codebase/front/vendor/select2/4.0.4/ru.js"></script>
<script >
// Таблица
$(document).ready(function() {
    $('#table_content').dataTable({
      stateSave:false,
      "pageLength": <?=$config['page_limit'];?>,
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
        }});

  $('ul.tabs li').click(function(){
    var tab_id = $(this).attr('data-tab');

    $('ul.tabs li').removeClass('current');
    $('.tab-content').removeClass('current');

    $(this).addClass('current');
    $("#"+tab_id).addClass('current');
  })

    $('.select2').select2();
    $.fn.select2.defaults.set('language', 'pt-RU');

    $('select[name=model_id]').on('change', function() {
        var value = $(this).val();
              if (value) {

                  $.get( "/ajax.php?type=get_cat&id="+value, function( data ) {
                  /*var obj = jQuery.parseJSON(data);
                  $('input[name=title]').val(obj.title);  */
                  $('select[name=cat]').html(data.html).selectmenu( "refresh" );
                  //$('select[name="cat"]').val(data.value);
                  $('select[name=serial]').html(data.html2).trigger('change.select2');
                  $('select[name=group]').html(data.html3).trigger('change.select2');
                  $('input[name=codepre]').val(data.pre);

                  });

              }
              return false;
    });

    $(document).on('change', 'select[name="model_add_id[]"]', function() {
        var value = $(this).val();
        var this_block = $(this).parent();
              if (value) {
                  $.get( "/ajax.php?type=get_cat&id="+value, function( data ) {
                  this_block.find($('select[name="serial_add[]"]')).html('<option>Выберите вариант</option>'+data.html2).trigger('change.select2');
                  });
              }
              return false;
    });

    $('select[name=group]').on('change', function() {
        var value = $(this).val();

              if (value) {

                  $.get( "/ajax.php?type=get_pre&id="+value, function( data ) {
                  /*var obj = jQuery.parseJSON(data);
                  $('input[name=title]').val(obj.title);  */
                  $('input[name=codepre]').val(data.pre);

                  });

              }
              return false;
    });

    var max_fields2      = 50; //maximum input boxes allowed
    var wrapper2         = $(".input_fields_wrap2"); //Fields wrapper
    var add_button2      = $(".add_field_button2"); //Add button ID

    var x2 = 1; //initlal text box count
    $(add_button2).click(function(e){ //on add input button click
        e.preventDefault();
        if(x2 < max_fields2){ //max input box allowed
            x2++; //text box increment
            $(wrapper2).append('<div class="value2"><select name="model_add_id[]" class="select3 nomenu"><option>Выберите вариант</option><?=models($content['model_id']);?></select> <select name="serial_add[]" class="select3 nomenu"><option>Выберите вариант</option></select><a href="#" class="remove_field del"></a></div>');
            $('.select3').select2();
        }
    });

    $(wrapper2).on("click",".remove_field", function(e){ //user click on remove text
        e.preventDefault(); $(this).parent('div').remove(); x2--;
    })

jQuery.extend(jQuery.validator.messages, {
    required: "Обязательно к заполнению!",
    require_from_group: "Пожалуйста, введите серийный номер или поставьте галочку, если его нет"
});



$(".repair_form").validate({
        ignore: "",
  rules: {
      cat: {
      required: true
      },
      group: {
      required: true
      }
  },
  highlight: function (element, errorClass) {
            $(element).addClass("input-validation-error");
  },
  errorClass: "field-validation-error",
  errorPlacement: function(error, element) {

      var ele = $(element),
      err = $(error),
      msg = err.text();
      if (msg != null && msg !== "") {
      ele.tooltipster('content', msg);
      ele.tooltipster('open'); //open only if the error message is not blank. By default jquery-validate will return a label with no actual text in it so we have to check the innerHTML.

      $('.error_valid').show();
      }

  },
  unhighlight: function(element, errorClass, validClass) {
      $(element).removeClass(errorClass).addClass(validClass).tooltipster('close');
      $(element).removeClass("input-validation-error");
      $('.error_valid').hide();
  }
});

} );

</script>
<style>
ul.tabs{
      margin: 0px;
      padding: 0px;
      list-style: none;
    }
    ul.tabs li{
      background: none;
      color: #222;
      display: inline-block;
      padding: 10px 15px;
      cursor: pointer;
    }

    ul.tabs li.current{
      background: #ededed;
      color: #222;
    }

    .tab-content{
      display: none;
      background: #ededed;
      padding: 15px;
    }

    .tab-content.current{
      display: inherit;
    }
</style>
</head>

<body>

<div class="viewport-wrapper">

<div class="site-header">
  <div class="wrapper">

    <div class="logo">
      <a href="/dashboard/"><img src="<?=$config['url'];?>i/logo.png" alt=""/></a>
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

      <a href="/logout/">Выйти, <?=\models\User::getData('login');?></a>
    </div>

  </div>
</div><!-- .site-header -->

<div class="wrapper">

<?=top_menu_admin();?>

  <div class="adm-tab">

<?=menu_dash();?>

  </div><!-- .adm-tab -->
           <br>
           <h2>Добавление запчасти</h2>

  <form id="send" method="POST" class="repair_form">
   <div class="adm-form" style="padding-top:0;">

                    <div class="item">
              <div class="level">Модель:</div>
              <div class="value">
                              <select name="model_id" class="select2 nomenu">
               <option>Выберите вариант</option>
               <?=models($content['model_id']);?>
              </select>
              </div>
            </div>

                    <div class="item">
              <div class="level">Категория:</div>
              <div class="value">
              <select name="cat">
               <option value="" selected>Выберите вариант</option>
               <?=cat();?>
              </select>
              </div>
            </div>


                  <div class="item">
              <div class="level">Серийный номер:</div>
              <div class="value">
                 <select name="serial" class="select2 nomenu">
               <option>Выберите вариант</option>
               <?=serials_select($content['serial']);?>
              </select>
              </div>
            </div>

                         <div class="item">
              <div class="level">Группа запчастей:</div>
              <div class="value">
                     <select name="group" class="select2 nomenu"> <option>Выберите вариант</option><?=groups();?></select>
              </div>
            </div>


                  <div class="item">
              <div class="level">Наименование запчасти:</div>
              <div class="value">
                <input type="text" name="list" value="<?=$content['list'];?>"  />
              </div>
            </div>

            <div class="item">
              <div class="level">Описание:</div>
              <div class="value">
                <input type="text" name="desc" value="<?=$content['desc'];?>"  />
              </div>
            </div>

            <div class="item">
              <div class="level">Принадлежность:</div>
              <div class="value">
              <select name="type">
               <option>Выберите вариант</option>
               <option value="БЛОЧНЫЙ ЭЛЕМЕНТ">БЛОЧНЫЙ ЭЛЕМЕНТ</option>
               <option value="АКСЕССУАР">АКСЕССУАР</option>
               <option value="КОМПОНЕНТНЫЙ ЭЛЕМЕНТ">КОМПОНЕНТНЫЙ ЭЛЕМЕНТ</option>
              </select>
              </div>
            </div>

            <div class="item">
              <div class="level">Вес запчасти:</div>
              <div class="value">
                <input type="text" name="weight" value="<?=$content['weight'];?>"  />
              </div>
            </div>

            <div class="item">
              <div class="level">Цена запчасти:</div>
              <div class="value">
                <input type="text" name="price" value="<?=$content['weight'];?>"  />
              </div>
            </div>

            <div class="item">
              <div class="level">Партномер:</div>
              <div class="value">
                <input type="text" name="part" value="<?=$content['part'];?>"  />
              </div>
            </div>

             <div class="item">
              <div class="level">Производитель:</div>
              <div class="value">
                <input type="text" name="brand" value="<?=$content['brand'];?>"  />
              </div>
            </div>

             <div class="item">
              <div class="level">Префикс кода запчасти:</div>
              <div class="value">
                <input type="text" name="codepre" placeholder="Например MB" value="<?=$content['codepre'];?>"  />
              </div>
            </div>

             <div class="item">
              <div class="level">Количество:</div>
              <div class="value">
                <input type="text" name="count" placeholder="" value="<?=$content['count'];?>"  />
              </div>
            </div>

             <hr>
             <h2>Дополнительные модели:</h2>

             <div class="field input_fields_wrap2" style="margin-top: 20px;">

              </div>

              <div class="add adm-add">
                <a href="#" class="add_field_button2"><u>Добавить еще</u></a>
              </div>

                <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="send" value="1" />
              <button type="submit" >Сохранить</button>
            </div>
            </div>
        </div>

      </form>




        </div>
  </div>
</body>
</html>