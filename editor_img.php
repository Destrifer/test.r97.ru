<?php
# Подключаем  конфиг:
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/configuration.php');
# Подключаем функции:
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/functions.php');


      $rand = rand(99999,(int) 99999999999999999999);
      //echo $rand;
      move_uploaded_file($_FILES['file']['tmp_name'], 'img/report/'.$rand.$_FILES['file']['name']);
      echo 'http://service.harper.ru/img/report/'.$rand.$_FILES['file']['name'];

if ($_POST['content_type'] == 0 && $_POST['content_id']) {

mysqli_query($db, 'INSERT INTO `photos` (
`repair_id`,
`url`
) VALUES (
\''.mysqli_real_escape_string($db, $_POST['content_id']).'\',
\''.mysqli_real_escape_string($db, 'http://service.harper.ru/img/report/'.$rand.$_FILES['file']['name']).'\'
);');

}

if ($_POST['content_type'] == 2 && $_POST['content_id']) {

mysqli_query($db, 'INSERT INTO `repairs_photo` (
`photo_id`,
`repair_id`,
`url`
) VALUES (
\''.mysqli_real_escape_string($db, 2).'\',
\''.mysqli_real_escape_string($db, $_POST['content_id']).'\',
\''.mysqli_real_escape_string($db, 'http://service.harper.ru/img/report/'.$rand.$_FILES['file']['name']).'\'
);');

}

if ($_POST['content_type'] == 3 && $_POST['content_id']) {

mysqli_query($db, 'INSERT INTO `repairs_photo` (
`photo_id`,
`repair_id`,
`url`
) VALUES (
\''.mysqli_real_escape_string($db, 3).'\',
\''.mysqli_real_escape_string($db, $_POST['content_id']).'\',
\''.mysqli_real_escape_string($db, 'http://service.harper.ru/img/report/'.$rand.$_FILES['file']['name']).'\'
);');

}

if ($_POST['content_type'] == 1 && $_POST['content_id']) {

mysqli_query($db, 'INSERT INTO `repairs_photo` (
`photo_id`,
`repair_id`,
`url`
) VALUES (
\''.mysqli_real_escape_string($db, 1).'\',
\''.mysqli_real_escape_string($db, $_POST['content_id']).'\',
\''.mysqli_real_escape_string($db, 'http://service.harper.ru/img/report/'.$rand.$_FILES['file']['name']).'\'
);');

}

if ($_POST['content_type'] == 4 && $_POST['content_id']) {

mysqli_query($db, 'INSERT INTO `repairs_photo` (
`photo_id`,
`repair_id`,
`url`
) VALUES (
\''.mysqli_real_escape_string($db, 4).'\',
\''.mysqli_real_escape_string($db, $_POST['content_id']).'\',
\''.mysqli_real_escape_string($db, 'http://service.harper.ru/img/report/'.$rand.$_FILES['file']['name']).'\'
);');

}

if ($_POST['content_type'] == 5 && $_POST['content_id']) {

mysqli_query($db, 'INSERT INTO `repairs_photo` (
`photo_id`,
`repair_id`,
`url`
) VALUES (
\''.mysqli_real_escape_string($db, 5).'\',
\''.mysqli_real_escape_string($db, $_POST['content_id']).'\',
\''.mysqli_real_escape_string($db, 'http://service.harper.ru/img/report/'.$rand.$_FILES['file']['name']).'\'
);');

}

?>