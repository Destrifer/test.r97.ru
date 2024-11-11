<?php

namespace models;

use program\adapters\DigitalOcean;

/** 
 * v. 0.1
 * 2021-06-23
 */

class Photos extends _Model
{

    private static $db = null;
    const TABLE_MAIN = 'repairs_photo';
    const TABLE_EXTRA = 'photos';

    public static function init()
    {
        self::$db = _Base::getDB();
    }


    public static function getPhotos($repairID)
    {
        $res = ['main' => [], 'extra' => []];
        $res['main'] = self::$db->exec('SELECT * FROM `' . self::TABLE_MAIN . '` WHERE `repair_id` = ?', [$repairID]);
        $res['extra'] = self::$db->exec('SELECT * FROM `' . self::TABLE_EXTRA . '` WHERE `repair_id` = ?', [$repairID]);
        return $res;
    }


    public static function getPhotosCnt($repairID)
    {
        $rows1 = self::$db->exec('SELECT COUNT(*) AS cnt FROM `' . self::TABLE_MAIN . '` WHERE `repair_id` = ' . $repairID);
        $rows2 = self::$db->exec('SELECT COUNT(*) AS cnt FROM `' . self::TABLE_EXTRA . '` WHERE `repair_id` = ' . $repairID);
        return $rows1[0]['cnt'] + $rows2[0]['cnt'];
    }


    /**
     * Поворачивает фото в заданном направлении
     * 
     * @param string $photoPath Ссылка на Digital Ocean или путь к фото на сервере
     * @param string $direction Направление (left | right)
     * 
     * @return array Путь к измененному изображению, доп. информация
     */
    public static function rotatePhoto($photoPath, $direction)
    {
        $ext = strtolower(pathinfo($photoPath)['extension']);
        $filename = md5(time());
        $pathSrc = '_new-codebase/uploads/temp/' . $filename . '.' . $ext; // путь к исходному файлу
        $pathRotated = '_new-codebase/uploads/temp/' . $filename . '-rotated.' . $ext; // путь к измененному файлу
        $rotateDeg = ($direction == 'left') ? 90 : -90;
        $digitalOceanFlag = false;
        /* Если фото на сервере Digital Ocean, то копируем на свой */
        if (mb_strpos($photoPath, 'digitalocean') !== false) {
            $digitalOceanFlag = true;
            $f = fopen($_SERVER['DOCUMENT_ROOT'] . '/' . $pathSrc, 'w');
            fwrite($f, self::getImageFromUrl($photoPath));
            fclose($f);
        } else {
            $pathSrc = trim($photoPath, ' /');
        }
        $source = self::getImageSource($pathSrc, $ext);
        if (!$source) {
            return ['path' => '', 'ext' => '', 'error_flag' => 1, 'message' => 'Неверный тип файла: ' . $ext . ', либо ошибка при обработке данных.'];
        }
        $rotatedSource = imagerotate($source, $rotateDeg, 0);
        $f = fopen($_SERVER['DOCUMENT_ROOT'] . '/' . $pathRotated, 'w');
        if ($ext == 'png') {
            imagepng($rotatedSource, $f, 9);
        } else {
            imagejpeg($rotatedSource, $f, 100);
        }
        imagedestroy($source);
        imagedestroy($rotatedSource);
        if (!is_file($_SERVER['DOCUMENT_ROOT'] . '/' . $pathRotated)) {
            return ['path' => '', 'ext' => '', 'error_flag' => 1, 'message' => 'К сожалению, при обработке произошла ошибка.'];
        }
        if ($digitalOceanFlag) {
            try {
                DigitalOcean::delete(parse_url($photoPath)['path']); // удаление старого фото
            } catch (\Exception $e) {
                return ['path' => '', 'ext' => '', 'error_flag' => 1, 'message' => $e->getMessage()];
            }
        }
        return ['path' => '/' . $pathRotated, 'ext' => $ext, 'error_flag' => 0];
    }


    private static function getImageSource($path, $ext)
    {
        $path = trim($path, ' /');
        switch ($ext) {
            case 'png':
                $fn = 'imagecreatefrompng';
                break;
            case 'jpeg':
            case 'jpg':
                $fn = 'imagecreatefromjpeg';
                break;
            default:
                return '';
        }
        $source = $fn($_SERVER['DOCUMENT_ROOT'] . '/' . $path);
        if (!$source) {
            /* Ошибка с exif в некоторых файлах */
            $img = new \Imagick($_SERVER['DOCUMENT_ROOT'] . '/' . $path);
            $img->stripImage();
            $img->writeImage($_SERVER['DOCUMENT_ROOT'] . '/' . $path);
            $img->clear();
            $img->destroy();
            /* Попытка заново получить source */
            $source = $fn($_SERVER['DOCUMENT_ROOT'] . '/' . $path);
        }
        return $source;
    }


    private static function getImageFromUrl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}


Photos::init();
