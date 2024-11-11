<?php

namespace program\adapters;

class S3
{

    public static $error = '';


    /**
     * Загружает файл в хранилище
     * 
     * @param string $pathFrom Путь к локальному файлу
     * @param string $pathTo Путь для загрузки на S3
     * 
     * @return string URL файла на S3 
     */
    public static function upload($pathFrom, $pathTo)
    {
        self::$error = '';
        try {
            $res = DigitalOcean::uploadFile($pathFrom, $pathTo);
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return '';
        }
        return $res;
    }


    public static function delete($path)
    {
        DigitalOcean::delete($path);
    }


    public static function isAvailable()
    {
        $curlInit = curl_init(DigitalOcean::URL);
        curl_setopt($curlInit, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curlInit, CURLOPT_HEADER, true);
        curl_setopt($curlInit, CURLOPT_NOBODY, true);
        curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curlInit);
        curl_close($curlInit);
        return (bool)$response;
    }
}
