<?php
//require_once($_SERVER['DOCUMENT_ROOT'].'/includes/functions.php');
header("Content-type: text/html; charset=utf-8");
$uploaddir = $_SERVER['DOCUMENT_ROOT'].'/img/upload/';

$parts = explode(".", basename($_FILES['uploadfile']['name']));
$name = time().".".$parts[count($parts)-1];
$file = $uploaddir.$name;
if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $file)) {
$filename = '/img/upload/'.$name;

/*$size = getimagesize($filename);
if (($size['0'] > 1600) or ($size['1'] > 1200)) {
Resizeimage($file, $file, 1600, 1200, 90);
} */

  echo $filename;
} else {
	echo 'error';
}


?>