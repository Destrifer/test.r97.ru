<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/functions.php');
header("Content-type: text/html; charset=utf-8");
$uploaddir = $_SERVER['DOCUMENT_ROOT'].'/_new-codebase/uploads/temp/';

$arr=Array();

function ResizeImage($image_from,$image_to, $fitwidth=180,$fitheight=450,$quality=75) {
 global $php_inc;
 $os=$originalsize=getimagesize($image_from);
 // ���� ����� JPEG ��� �������, �� ������� ���� ��������� - ������ �� ������
 if($originalsize[2]!=2 && $originalsize[2]!=3 && $originalsize[2]!=6 && ($originalsize[2]<9
   or $originalsize[2]>12)) {
 return false;
 }
 if($originalsize[0]>$fitwidth or $originalsize[1]>$fitheight) {
 $h=getimagesize($image_from);
 if(($h[0]/$fitwidth)>($h[1]/$fitheight))
 {
 $fitheight=$h[1]*$fitwidth/$h[0];
 }else{
 $fitwidth=$h[0]*$fitheight/$h[1];
 }
 if($os[2]==2 or ($os[2]>=9 && $os[2]<=12))$i = ImageCreateFromJPEG($image_from);
 if($os[2]==3)$i=ImageCreateFromPng($image_from);
 $o = ImageCreateTrueColor($fitwidth, $fitheight);
 imagecopyresampled($o, $i, 0, 0, 0, 0, $fitwidth, $fitheight, $h[0], $h[1]);
 imagejpeg($o, $image_to, $quality);
 chmod($image_to,0777);
 imagedestroy($o);
 imagedestroy($i);
 return 2;
 }
 if($originalsize[0]<=$fitwidth && $originalsize[1]<=$fitheight) {
 $i = ImageCreateFromJPEG($image_from);
 imagejpeg($i, $image_to, $quality);
 chmod($image_to,0777);
 return 1;
 }
 }


for($a=0;$a<count($_FILES['uploadfile']['name']);$a++)
{
	$parts = explode(".", basename($_FILES['uploadfile']['name'][$a]));

	if($parts[count($parts)-1]!='JPG' && $parts[count($parts)-1]!='PNG'  && $parts[count($parts)-1]!='jpg' && $parts[count($parts)-1]!='png' && $parts[count($parts)-1]!='jpeg' && $parts[count($parts)-1]!='gif')
	{
		$arr[]='false';
	}else{
		$name = time().rand(10000,90000000).".".$parts[count($parts)-1];
		$file = $uploaddir.$name;
		if (move_uploaded_file($_FILES['uploadfile']['tmp_name'][$a], $file)) {
			$filename = '/_new-codebase/uploads/temp/'.$name;


		$arr[]=$filename;
		} else {
			echo 'error';
		}
	}
}

echo json_encode($arr);

?>