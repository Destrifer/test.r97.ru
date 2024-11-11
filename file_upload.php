<?php

// This is a simplified example, which doesn't cover security of uploaded images.
// This example just demonstrate the logic behind the process.


// files storage folder
$dir = $_SERVER['DOCUMENT_ROOT'].'/file/upload/';

$_FILES['file']['type'] = strtolower($_FILES['file']['type']);

    $path = $_FILES['file']['name'];
$ext = pathinfo($path, PATHINFO_EXTENSION);
    // setting file's mysterious name
    $filename = md5(date('YmdHis')).'.'.$ext;
    $file = $dir.$filename;

    // copying
    copy($_FILES['file']['tmp_name'], $file);

    // displaying file
  $array = array(
    'filelink' => '/file/upload/'.$filename
  );

  echo stripslashes(json_encode($array));


?>