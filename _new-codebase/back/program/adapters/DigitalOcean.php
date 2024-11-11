<?php


namespace program\adapters;

use program\core;

/* https://github.com/SociallyDev/Spaces-API/issues/6 */

class DigitalOcean
{
    const URL = 'https://fra1.digitaloceanspaces.com/';
    public static $vendorPath = 'includes/spaces/spaces.php';
    private static $space = null;


    public static function init()
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/' . self::$vendorPath;
        self::$space = new \SpacesConnect(core\App::$config['digocean_key'], core\App::$config['digocean_secret'], core\App::$config['digocean_name'], core\App::$config['digocean_region']);
    }


    public static function uploadFile($path, $dest)
    {
        $up = self::$space->UploadFile(ltrim($path, '/'), 'public', str_replace(['(', ')', ' '], '_', trim($dest, '/ ')));
        return $up['ObjectURL'];
    }


    public static function upload($filesKey, $dest, $index = -1)
    {
        $dest = trim($dest, '/ ');
        $file = new core\File('', $filesKey, $index);
        $filename = self::makeFilename();
        $file->setPath(core\App::$config['dir_uploads'] . '/temp', $filename);
        $up = self::$space->UploadFile(ltrim($file->path, '/'), 'public', $dest . '/' . date('mY') . '/' . $filename . '.' . strtolower($file->ext));
        return $up['ObjectURL'];
    }

    public static function makeFilename()
    {
        return date('d-Hi-s') . rand(1, 99999999);
    }

    public static function delete($path)
    {
        $p = parse_url(trim($path));
        self::$space->DeleteObject(ltrim($p['path'], '/'));
    }
}


DigitalOcean::init();
