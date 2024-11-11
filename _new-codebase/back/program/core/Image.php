<?php


namespace program\core;


class Image
{
  /**
   * Returns image's size
   *
   *
   * @example array('width' => 100, 'height' => 200, 'max' => 200)
   * @param string path
   * @return array
   */
  public static function getSize($path)
  {
    if (!is_file($path)) {
      return [];
    }
    $s = getimagesize($path);
    if (!$s) {
      return [];
    }
    return array('width' => $s[0], 'height' => $s[1], 'max' => max($s[0], $s[1]));
  }


  public static function convert($path, $newExt)
  {
    $curExt = pathinfo($path, PATHINFO_EXTENSION);
    if (!$curExt) {
      return '';
    }
    if ($curExt == $newExt) {
      return $path;
    }
    $path = ltrim($path, '/');
    if (!is_file($_SERVER["DOCUMENT_ROOT"] . '/' . $path)) {
      return '';
    }
    if ($curExt == 'jpeg' && $newExt == 'jpg') {
      $newPath = str_replace('.jpeg', '.jpg', $path);
      $r = rename($_SERVER["DOCUMENT_ROOT"] . '/' . $path, $_SERVER["DOCUMENT_ROOT"] . $newPath);
      if (!$r) {
        return false;
      }
      unlink($_SERVER["DOCUMENT_ROOT"] . '/' . $path);
      return $newPath;
    }
    if ($newExt == 'jpg') {
      $fn = 'imagejpeg';
      $qty = 100;
    } else {
      $fn = 'imagepng';
      $qty = 9;
    }
    $newPath = str_replace('.'.$curExt, '.'.$newExt, $path);
    $r = $fn(imagecreatefromstring(file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/' . $path)), $_SERVER["DOCUMENT_ROOT"] . '/' . $newPath, $qty);
    if (!$r) {
      return false;
    }
    unlink($_SERVER["DOCUMENT_ROOT"] . '/' . $path);
    return $newPath;
  }


  public static function resize($srcFile, $destFile, $width = 0, $height = 0)
  {
    $srcFile = $_SERVER["DOCUMENT_ROOT"] . ltrim($srcFile, '/');
    $destFile = $_SERVER["DOCUMENT_ROOT"] . ltrim($destFile, '/');
    if (!file_exists($srcFile)) {
      throw new \Exception('Исходный файл не существует: ' . $srcFile);
    }
    $size = getimagesize($srcFile);
    if ($size === false) {
      return false;
    }
    if (!$width) {
      $width = $size[0];
    }
    if (!$height) {
      $height = $size[1];
    }
    // если реальная ширина и высота рисунка меньше, чем размеры до которых надо уменьшить,
    // тогда уменьшаемые размеры станут равны реальным размерам, чтобы не произошло увеличение
    if ($size[0] < $width && $size[1] < $height) {
      $width = $size[0];
      $height = $size[1];
    }
    // выбираем соответствующую imagecreatefrom-функцию.
    $format = strtolower(substr($size['mime'], strpos($size['mime'], '/') + 1));
    $icfunc = "imagecreatefrom" . $format;
    if (!function_exists($icfunc)) {
      return false;
    }
    $x_ratio = $width / $size[0];
    $y_ratio = $height / $size[1];
    $ratio = min($x_ratio, $y_ratio);
    $use_x_ratio = ($x_ratio == $ratio);
    $new_width = $use_x_ratio ? $width : floor($size[0] * $ratio);
    $new_height = !$use_x_ratio ? $height : floor($size[1] * $ratio);
    $new_left = $use_x_ratio ? 0 : floor(($width - $new_width) / 2);
    $new_top = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
    $isrcFile = $icfunc($srcFile);
    $new_left = 0;
    $new_top = 0;
    $idestFile = imagecreatetruecolor($new_width, $new_height);
    imagefill($idestFile, 0, 0, 0xffffff);
    imagecopyresampled($idestFile, $isrcFile, $new_left, $new_top, 0, 0, $new_width, $new_height, $size[0], $size[1]);
    imagejpeg($idestFile, $destFile, 100);
    imagedestroy($isrcFile);
    imagedestroy($idestFile);
    return true;
  }


  public static function addWatermark($srcFile, $wmFile)
  {
    $srcFile = $_SERVER["DOCUMENT_ROOT"] . '/' . ltrim($srcFile, '/');
    $wmFile = $_SERVER["DOCUMENT_ROOT"] . '/' . ltrim($wmFile, '/');
    $imgSize = getimagesize($srcFile);
    $wmSize = getimagesize($wmFile);
    if ($imgSize === false || $wmSize === false) {
      throw new \Exception('Файл изображения или wm отсутствует.');
    }
    $format = strtolower(substr($imgSize['mime'], strpos($imgSize['mime'], '/') + 1));
    $icfunc = "imagecreatefrom" . $format;
    if (!function_exists($icfunc)) {
      throw new \Exception("Функции $icfunc не существует.");
    }
    $img = array('width' => $imgSize[0], 'height' => $imgSize[1], 'src' => $icfunc($srcFile));
    $wm = array('width' => $wmSize[0], 'height' => $wmSize[1], 'src' => imagecreatefrompng($wmFile));
    //
    if ($wm['height'] < $img['height'] && $wm['width'] < $img['width']) {
      $x = 30;
      $y = 30;
      imagecopy($img['src'], $wm['src'], $x, $y, 0, 0, $wm['width'], $wm['height']);
      $icfunc = "image" . $format;
      $icfunc($img['src'], $srcFile, 100);
    }
    imagedestroy($img['src']);
    imagedestroy($wm['src']);
  }
}
