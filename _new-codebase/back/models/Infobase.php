<?php

namespace models;

use program\core;
use program\adapters\DigitalOcean;
use program\core\FS;


class Infobase extends _Model
{
    public static $message = '';
    public static $errors = [];
    private static $db = null;


    public static function init()
    {
        self::$db = _Base::getDB();
    }


    public static function save($serialID, $fileID, $catID, $name, $descr)
    {
        $ret = ['id' => '', 'name' => '', 'filename' => '', 'url' => '', 'size' => 0, 'upload_date' => '', 'descr' => '', 'parent_id' => 0];
        /* Добавляется новый файл */
        if (!$fileID) {
            $fileID = self::$db->exec('INSERT INTO `infobase` (`cat_id`, `serial_id`) VALUES (?, ?)', [$catID, $serialID]);
            if (!$fileID) {
                self::$message = 'Не удалось добавить файл в базу: ' .  self::$db->getErrorInfo();
                return;
            }
        } else {
            /* Файл обновляется */
            $rows = self::$db->exec('SELECT * FROM `infobase` WHERE `id` = ?', [$fileID]);
            if (!$rows) {
                self::$message = 'Файл #' . $fileID . ' не найден.';
                return;
            }
            $ret['url'] = $rows[0]['url'];
            $ret['upload_date'] = $rows[0]['upload_date'];
            $ret['size'] = $rows[0]['size'];
            $ret['parent_id'] = $rows[0]['parent_id'];
        }
        $ret['id'] = $fileID;
        $ret['name'] = trim($name);
        $ret['descr'] = trim($descr);
        $file = new core\File('', 0);
        if ($file->exists()) {
            $fn = str_replace(' ', '_', $file->name);
            $fn = preg_replace('/[^a-zа-яё\d-_]/iu', '-', $fn);
            $fn = trim(preg_replace('/-{2,}/', '-', $fn), '-_');
            $file->setPath('/_new-codebase/uploads/temp/', core\Text::translit($fn));
            $url = DigitalOcean::uploadFile($file->path, 'infobase/' . core\FS::getVolByID($serialID) . '/' . $serialID . '/' . self::getDir($catID) . '/' . $file->name . '.' . strtolower($file->ext));
            if ($url) {
                if (!empty($ret['url'])) {
                    DigitalOcean::delete($ret['url']);
                }
                $ret['url'] = $url;
                $ret['upload_date'] = date('Y-m-d H:i:s');
                $ret['size'] = $file->size;
                $ret['parent_id'] = 0; // загруженный файл является самостоятельным, а не ссылкой на другой
            } else {
                self::$message = 'Не удалось загрузить файл.';
                return;
            }
        }
        self::$db->exec(
            'UPDATE `infobase` SET `size` = ?, `url` = ?, `name` = ?, `upload_date` = ?, `descr` = ?, `parent_id` = ? WHERE `id` = ?',
            [$ret['size'], $ret['url'], $ret['name'], $ret['upload_date'], $ret['descr'], $ret['parent_id'], $ret['id']]
        );
        if (!empty($ret['url'])) {
            $ret['filename'] = basename($ret['url']);
            $ret['size'] = round($file->size / 1024 / 1024, 2) . ' мб';
            $ret['upload_date'] = core\Time::format($ret['upload_date'], 'd.m.Y H:i');
        }
        return $ret;
    }


    public static function copyToSerials($fileID, array $serialsIDs)
    {
        $file = self::getFile($fileID);
        foreach ($serialsIDs as $id) {
            self::$db->exec('INSERT INTO `infobase` (`name`, `url`, `cat_id`, `serial_id`, `size`, 
            `upload_date`, `descr`, `parent_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [
                $file['name'], $file['url'], $file['cat_id'], $id, $file['size'],
                $file['upload_date'], $file['descr'], $fileID
            ]);
        }
    }


    private static function getDir($catID)
    {
        $dirs = [1 => 'firmware', 2 => 'schemes', 3 => 'bulletin'];
        return $dirs[$catID];
    }


    public static function getFileSerials($fileID)
    {
        $curSerialID = 0;
        $rows = self::$db->exec('SELECT `serial_id`, `parent_id` FROM `infobase` WHERE `id` = ?', [$fileID]);
        $curSerialID = $rows[0]['serial_id'];
        $parentID = $rows[0]['parent_id'];
        $rows = self::$db->exec('SELECT `serial_id` FROM `infobase` WHERE `parent_id` IN (?, ?)', [$fileID, $parentID]);
        $ids = array_column($rows, 'serial_id');
        if ($curSerialID) {
            $ids[] = $curSerialID;
        }
        return $ids;
    }


    private static function getFile($fileID)
    {
        $rows = self::$db->exec('SELECT * FROM `infobase` WHERE `id` = ?', [$fileID]);
        if (!$rows) {
            return [];
        }
        return $rows[0];
    }


    public static function getFilesByModelID($modelID)
    {
        $rows = self::$db->exec('SELECT `id` FROM `serials` WHERE `model_id` = ?', [$modelID]);
        if (!$rows) {
            return [];
        }
        $rows = self::$db->exec('SELECT * FROM `infobase` WHERE `serial_id` IN (' . core\SQL::IN(array_column($rows, 'id'), false) . ') AND `parent_id` = 0 ORDER BY `id` DESC');
        return self::getFilesTree($rows);
    }


    public static function getFilesCnt($serialID)
    {
        if (!$serialID) {
            return 0;
        }
        $rows = self::$db->exec('SELECT COUNT(*) AS cnt FROM `infobase` WHERE `serial_id` = ? AND `url` != ""', [$serialID]);
        return ($rows) ? $rows[0]['cnt'] : 0;
    }


    public static function getFilesBySerialID($serialID)
    {
        $rows = self::$db->exec('SELECT * FROM `infobase` WHERE `serial_id` = ? AND `url` != "" ORDER BY `id` DESC', [$serialID]);
        if (!$rows) {
            return [];
        }
        return self::getFilesTree($rows);
    }


    private static function getFilesTree(array $rows)
    {
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $rows[$i]['filename'] = basename($rows[$i]['url']);
            $rows[$i]['size'] =  round($rows[$i]['size'] / 1024 / 1024, 2) . ' мб';
            $rows[$i]['upload_date'] =  core\Time::format($rows[$i]['upload_date'], 'd.m.Y H:i');
            $rows[$i]['ext'] = FS::getFileExt($rows[$i]['url']);
        }
        $rows = core\RowSet::groupBy('cat_id', $rows);
        $ret = [
            ['cat_id' => 1, 'name' => 'Software', 'items' => []],
            ['cat_id' => 2, 'name' => 'Scematic', 'items' => []],
            ['cat_id' => 3, 'name' => 'Documents', 'items' => []]
        ];
        for ($i = 0, $cnt = count($ret); $i < $cnt; $i++) {
            if (isset($rows[$ret[$i]['cat_id']])) {
                $ret[$i]['items'] = $rows[$ret[$i]['cat_id']];
            }
        }
        return $ret;
    }


    public static function getFiles($catID, $serialID)
    {
        $rows = self::$db->exec('SELECT * FROM `infobase` WHERE `cat_id` = ? AND `serial_id` = ? ORDER BY `id` DESC', [$catID, $serialID]);
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $rows[$i]['filename'] = basename($rows[$i]['url']);
            $rows[$i]['size'] =  round($rows[$i]['size'] / 1024 / 1024, 2) . ' мб';
            $rows[$i]['upload_date'] =  core\Time::format($rows[$i]['upload_date'], 'd.m.Y H:i');
        }
        return $rows;
    }


    public static function delFile($fileID)
    {
        if (!$fileID) {
            return;
        }
        $rows = self::$db->exec('SELECT `url`, `parent_id` FROM `infobase` WHERE `id` = ?', [$fileID]);
        if (!$rows) {
            return;
        }
        $r = self::$db->exec('DELETE FROM `infobase` WHERE `id` = ?', [$fileID]);
        if (empty($rows[0]['url'])) {
            return;
        }
        if (!$rows[0]['parent_id']) {
            $doubles = self::$db->exec('SELECT COUNT(*) AS cnt FROM `infobase` WHERE `parent_id` = ?', [$fileID]);
            if ($r && !$doubles[0]['cnt']) {
                DigitalOcean::delete($rows[0]['url']);
            }
        }
    }
}


Infobase::init();
