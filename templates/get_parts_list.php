<?php
function content_list() {
  global $db;
  $content_list = '';
  $where = ($_POST['payed'] == 1) ? '' : 'and `model_id` = '.$_GET['model_id'];
$sql = mysqli_query($db, 'SELECT * FROM `parts` where `count` > 0 and `parent_id` = \'\' '.$where.'  order by id asc;');
if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
      $model = get_model_by_id($row['model_id']);
      $imgs = json_decode($row['imgs']);
      $parts_blacklist[] = $row['id'];
      $content_list .= '<tr>
      <td>'.cat_by_id($row['cat'])['name'].'</td>
      <td >'.$model.'</td>
      <td ></td>
      <td>'.$row['serial'].'</td>
      <td><input class="editable" style="width:200px;" type="text" name="place" value="'.$row['place'].'" data-id="'.$row['id'].'"></td>
      <td>'.$row['codepre'].$row['id'].'</td>
      <td>'.$row['count'].'</td>
      <td>'.$row['list'].'</td>
     <td>'.$row['part'].'</td>
      <td align="center" class="linkz" >
      <a style="background: url(http://cdn.onlinewebfonts.com/svg/img_225522.png) no-repeat center center;  background-size: cover;     width: 19px;" class="t-1 show_img" title="Изображение"href="#" ><span class="tooltip_content"><img style="max-height:700px;max-width:700px;display:none;" src="https://crm.r97.ru/resizer.php?src=https://crm.r97.ru'.$imgs['0'].'&h=500&w=500&zc=3&q=70" /></span></a>';
      $content_list .= '<a class="t-3" title="Просмотреть карточку" href="/edit-parts/'.$row['id'].'/" ></a>';
      $content_list .= '&nbsp;&nbsp;&nbsp;<input check_num="number" max="'.$row['count'].'"  style="width:70px;    padding: 0 10px;    height: 30px;" type="number" name="count" value="1">&nbsp;&nbsp;<a style="transform: rotate(805deg);float:right;margin-right:15px;margin-top:5px;" data-part-id="'.$row['id'].'" data-count="1" class="t-2 add_part_to_job" title="Выбрать" href="#"></a>';
      $content_list .= '</td>
      </tr>';
      }
      }

/* ДОЧЕРНИЕ */

if ($_POST['payed'] != 1) {

$sql = mysqli_query($db, 'SELECT * FROM `parts` WHERE `id` IN ( SELECT MAX(id) FROM parts where `count` > 0 '.$where.' AND `parent_id` != 0 AND `parent_id` != \'\' GROUP BY parent_id ) ORDER BY id desc');
if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
      if (!in_array($row['parent_id'], $parts_blacklist)) {
      if (get_parent_count($row['parent_id']) > 0) {
      $part_info = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `parts` WHERE `id` = '.$row['parent_id']));
      if ($part_info['place'] == '') {
        continue;
      }
      $model = get_model_by_id($row['model_id']);
      $parent_names = get_parents($row['id']);
      $imgs = json_decode($row['imgs']);
      $content_list .= '<tr>
      <td>'.cat_by_id($row['cat'])['name'].'</td>
      <td >'.$model.'</td>
      <td><table>'.$parent_names.'</table></td>
      <td>'.$row['serial'].'</td>
      <td><input class="editable" style="width:200px;" type="text" name="place" value="'.$part_info['place'].'" data-id="'.$part_info['id'].'"></td>
      <td>'.$row['codepre'].$part_info['id'].'</td>
      <td>'.$row['count'].'</td>
      <td>'.$row['list'].'</td>
     <td>'.$row['part'].'</td>
      <td align="center" class="linkz" >
      <a style="background: url(http://cdn.onlinewebfonts.com/svg/img_225522.png) no-repeat center center;  background-size: cover;     width: 19px;" class="t-1 show_img" title="Изображение"href="#" ><span class="tooltip_content"><img style="max-height:700px;max-width:700px;display:none;" src="https://crm.r97.ru/resizer.php?src=https://crm.r97.ru'.$imgs['0'].'&h=500&w=500&zc=3&q=70" /></span></a>';
  $content_list .= '<a class="t-3" title="Просмотреть карточку" href="/edit-parts/'.$row['id'].'/" ></a>';
  $content_list .= '&nbsp;&nbsp;&nbsp;<input check_num="number" style="width:70px;    padding: 0 10px;    height: 30px;" type="number" name="count" max="'.$row['count'].'" value="1">&nbsp;&nbsp;<a style="transform: rotate(805deg);float:right;margin-right:15px;margin-top:5px;" data-part-id="'.$row['id'].'" data-count="1" class="t-2 add_part_to_job" title="Выбрать" href="#"></a>';
  $content_list .= '</td></tr>';
      }
      }
      }
      }

}

    return $content_list;
}

function cat_by_id($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `cats` where `id` = \''.$id.'\' LIMIT 1;');
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
      }
    return $content;
}

function get_model_by_id($id) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT `name` FROM `models` WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\'');
return mysqli_fetch_array($sql)['name'];
}

function get_parents($id) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT `model_id`, `serial` FROM `parts` WHERE `parent_id` = \''.mysqli_real_escape_string($db, $id).'\' ');
if (mysqli_num_rows($sql) > 0) {

//$names .= '<table>';
while ($row = mysqli_fetch_array($sql)) {
$content['models_array'][$row['model_id']][] = $row['serial'];
}

//print_r($content['models_array']);

foreach ($content['models_array'] as $model_id => $serial) {
  $names .= '<tr>';
  $names .= '<td>'.get_model_by_id($model_id)."</td>";
  //$names .= '<td>'.get_serial_name($model_id, $serial).'</td>';
  $names .= '</tr>';
}




//$names .= '</table>';

}
return $names;
}

function get_provider_name($id)  {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT `name` FROM `providers` WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\'');
return mysqli_fetch_array($sql)['name'];
}

function get_parent_count($id)  {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT `count` FROM `parts` WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\'');
return mysqli_fetch_array($sql)['count'];
}

function get_serial_name($id, $currents = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `serials` where `model_id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $order = ($row['order']) ? ''.$row['order'] : '';
      if (in_array($row['serial'], $currents)) {
      $glue[] = get_provider_name($row['provider_id']).' ('.$order.')';
      } else {
      // $content .= get_provider_name($row['serial_provider']).''.$order.', ';
      }
      }
    $content = @implode(', ', $glue);
    return $content;
}

$model = get_model_by_id($_GET['model_id']); ;

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
<script  src="/_new-codebase/front/vendor/datatables/2.1.1/dataTables.responsive.min.js"></script>
<link rel="stylesheet" type="text/css" href="/_new-codebase/front/vendor/datatables/2.1.1/responsive.dataTables.min.css">
<script >
// Таблица
$(document).ready(function() {
    $('#table_content').dataTable({
      stateSave:false,
      "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>',
      "responsive": true,
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

    $(document).on('change', 'input.editable', function() {
        var id = $(this).data('id');
        var value = $(this).val();

                  $.get( "/ajax.php?type=update_place&value="+value+"&id="+id, function( data ) {

                 //$('select[name=parts_parts]').html(data.html).selectmenu( "refresh" );
                  //$('input[name="serial_parts_hidden"]').val(value);


                  });


        return false;
    });

$(document).ready(function() {

    $(document).on('change', 'input[name="count"]', function() {
        $(this).parent().find('.add_part_to_job').attr('data-count', $(this).val());
        return false;
    });

    $(document).on('click', 'a.add_part_to_job', function() {
        if ($(this).data('count') > 0) {
        parent.setSelectedPart($(this).data('part-id'), $(this).data('count')<?=(($_GET['no_parts'] == 1) ? ',1' : '');?>);
        parent.jQuery.fancybox.close();
        } else {
        $(this).parent().find('input[name="count"]').css('border', '1px solid red');
        }
        return false;
    });

});

$(document).ready(function(){
    $('input[check_num="number"]').on('keyup',function(){
        v = parseInt($(this).val());
        min = parseInt($(this).attr('min'));
        max = parseInt($(this).attr('max'));

        /*if (v < min){
            $(this).val(min);
        } else */if (v > max){
            $(this).val(max);
        }
    })
})

 $('.show_img').tooltipster({
                              trigger: 'hover',
                              position: 'top',
                              interactive:true,
                              animation: 'grow',
                              theme: 'tooltipster-shadow',
                              functionInit: function (instance, helper) {
                                $(helper.origin).find('.tooltip_content img').show();
                                //$(helper.origin).find('.tooltip_content img').attr('src', $(helper.origin).find('.tooltip_content img').data('src'));
                                var content = $(helper.origin).find('.tooltip_content').detach();
                                instance.content(content);
                              },
                              functionReady: function (instance, helper) {
                              //instance.content().find('img').attr('src', instance.content().find('img').data('src'));

                              //$(instance.origin).find('.tooltip_content img').attr('src', $(instance.origin).find('.tooltip_content img').data('src'));
                              }
                          });

    $(document).on('change', 'input[name="payed"]', function() {
        var form = $(this).parent().parent().parent().parent();
        $('#checkb').submit();
    });


} );

</script>
</head>

<body>

<div class="viewport-wrapper">


<div class="wrapper">


           <br>
           <h2>Запчасти <?=$model;?></h2>

  <div class="adm-catalog">

  <form method="POST" id="checkb">
    <table style="">
              <tr>
    <td style="padding: 20px 20px 20px 0px;; ">Показать все <input type="checkbox" value="1" name="payed" <?=($_POST['payed'] == '1') ? 'checked' : '';?>></td>
    </tr>
            </table>
        </form>

     <br>
  <table id="table_content" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th align="left" data-priority="1">Категория</th>
                <th align="left" data-priority="2" >Модель</th>
                <th align="left" data-priority="2" ></th>
                <th align="left" data-priority="8">Серийный номер</th>
                <th align="left" data-priority="1">Место хранения</th>
                <th align="left" data-priority="4">Код запчасти</th>
                <th align="left" data-priority="5">Количество</th>
                <th align="left" data-priority="6">Наименование запчасти</th>
                <th align="left" data-priority="7">Партномер</th>
                <th align="center" data-priority="3">Операции</th>           
            </tr>
        </thead>

        <tbody>
        <?=content_list();?>
        </tbody>
</table>


</div>


        </div>
  </div>
</div>
</body>
</html>