<?php

namespace models\parts;

use models\parts\Batches;

class Balance extends \models\_Model
{

    const TABLE = 'parts2_balance';
    public static $error = '';
    private static $db = null;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    /**
     * Получает общее кол-во запчасти
     * 
     * @param int $partID Запчасть
     * 
     * @return int Кол-во
     */
    public static function total($partID)
    {
        $rows = self::$db->exec('SELECT SUM(`qty`) AS sum FROM `' . self::TABLE . '` WHERE `part_id` = ' . $partID);
        return !empty($rows[0]['sum']) ? $rows[0]['sum'] : 0;
    }


    /**
     * Получает запись с данными баланса
     * 
     * @param int $partID Запчасть
     * @param int $depotID Склад
     * 
     * @return array Запись с данными
     */
    public static function get($partID, $depotID)
    {
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `part_id` = ' . $partID . ' AND `depot_id` = ' . $depotID);
        return $rows ? $rows[0] : [];
    }


    /**
     * Получает записи с данными баланса по всем складам
     * 
     * @param int $partID Запчасть
     * 
     * @return array Строки с данными
     */
    public static function getAll($partID)
    {
        return self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `part_id` = ' . $partID);
    }


    /**
     * Проверяет, достаточно ли запчастей на складе
     * 
     * @param int $depotID Склад
     * @param int $partID Запчасть
     * @param int $qty Требуемое кол-во
     * 
     * @return bool Достаточно или нет
     */
    public static function isEnough($depotID, $partID, $qty)
    {
        if (!$qty) {
            return true;
        }
        $rows = self::$db->exec('SELECT `qty` FROM `' . self::TABLE . '` WHERE `depot_id` = ? AND `part_id` = ?', [$depotID, $partID]);
        if (!$rows || $rows[0]['qty'] < $qty) {
            return false;
        }
        return true;
    }


    /**
     * Возвращает кол-во запчасти на складе (если указан)
     * 
     * @param int $partID Запчасть
     * @param int $depotID Склад (необязательный)
     * 
     * @return int Кол-во запчастей
     */
    public static function count($partID, $depotID = 0)
    {
        $depotSQL = (!$depotID) ? '' : '`depot_id` = ' . $depotID . ' AND ';
        $rows = self::$db->exec('SELECT SUM(`qty`) AS sum FROM `' . self::TABLE . '` WHERE ' . $depotSQL . ' `part_id` = ' . $partID);
        return (!empty($rows[0]['sum'])) ? $rows[0]['sum'] : 0;
    }


    /**
     * Удаляет информацию из баланса
     * 
     * @param int $partID Запчасть
     * @param int $qty Требуемое кол-во
     * @param int $depotID Склад
     * @param string $receiveDate Дата получения
     * 
     * @return bool Успешно или нет
     */
    public static function delete($partID, $qty, $depotID, $receiveDate)
    {
        if (!$qty) {
            return true;
        }
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `depot_id` = ? AND `part_id` = ?', [$depotID, $partID]);
        if (!$rows) {
            return true;
        }
        $newQty = $rows[0]['qty'] - $qty;
        if ($newQty < 0) {
            self::$error = 'Количество запчастей недостаточно для проведения операции.';
            return false;
        }
        Batches::delete($partID, $qty, $depotID, $receiveDate);
        $r = self::$db->exec('UPDATE `' . self::TABLE . '` SET `qty` = ' . $newQty . ' WHERE `id` = ' . $rows[0]['id']);
        if (!$r) {
            self::$error = self::$db->getErrorInfo();
        }
        return $r;
    }


    /**
     * Забирает запчасть со склада
     * 
     * @param int $partID Запчасть
     * @param int $qty Требуемое кол-во
     * @param int $depotID Склад
     * 
     * @return bool Успешно или нет
     */
    public static function take($partID, $qty, $depotID)
    {
        if (!$qty) {
            return true;
        }
        if (!Batches::take($partID, $qty, $depotID)) {
            return false;
        }
        $b = self::get($partID, $depotID);
        if (empty($b['qty'])) {
            return false;
        }
        $newNum = $b['qty'] - $qty;
        $newDisposalNum = $b['disposal_num'] - $qty;
        $newNum = ($newNum < 0) ? 0 : $newNum;
        $newDisposalNum = ($newDisposalNum < 0) ? 0 : $newDisposalNum;
        return self::$db->exec('UPDATE `' . self::TABLE . '` SET `qty` = ' . $newNum . ', `disposal_num` = ' . $newDisposalNum . ' WHERE `id` = ' . $b['id']);
    }


    /**
     * Добавляет запчасть на склад
     * 
     * @param int $partID Запчасть
     * @param int $qty Добавляемое кол-во
     * @param int $depotID Склад назначения
     * @param bool $useFirstFlag Использовать данную партию первой
     * 
     * @return bool Успешно или нет
     */
    public static function add($partID, $qty, $depotID, $useFirstFlag = false)
    {
        $qty = ($qty) ? $qty : 0;
        if (!Batches::add($partID, $qty, $depotID, $useFirstFlag)) {
            return false;
        }
        $rows = self::$db->exec('SELECT `id` FROM `' . self::TABLE . '` WHERE `part_id` = ? AND `depot_id` = ?', [$partID, $depotID]);
        if (!$rows) {
            $r = self::$db->exec('INSERT INTO `' . self::TABLE . '` (`part_id`, `depot_id`, `qty`) VALUES (?, ?, ?)', [$partID, $depotID, $qty]);
        } else {
            $r = self::$db->exec('UPDATE `' . self::TABLE . '` SET `qty` = `qty` + ' . $qty . ' WHERE `id` = ?', [$rows[0]['id']]);
        }
        if (!$r) {
            self::$error = self::$db->getErrorInfo();
        }
        return $r;
    }


    /**
     * Сохраняет место запчасти на складе
     * 
     * @param string $place Запчасть
     * @param int $partID Запчасть
     * @param int $depotID Склад назначения
     * 
     * @return bool Успешно или нет
     */
    public static function setPlace($place, $partID, $depotID)
    {
        $place = trim($place);
        $rows = self::$db->exec('SELECT `id` FROM `' . self::TABLE . '` WHERE `part_id` = ? AND `depot_id` = ?', [$partID, $depotID]);
        if (!$rows) {
            return false;
        }
        return self::$db->exec('UPDATE `' . self::TABLE . '` SET `place` = ? WHERE `id` = ?', [$place, $rows[0]['id']]);
    }
}

Balance::init();
