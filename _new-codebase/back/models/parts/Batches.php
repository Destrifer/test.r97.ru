<?php

namespace models\parts;

/* Партии запчастей, принятые в ремонтах, и сроки их утилизации */

class Batches extends \models\_Model
{

    const TABLE = 'parts2_batches';
    const DAYS_DISPOSAL = 90; // количество дней до утилизации
    private static $db = null;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function add($partID, $num, $depotID, $useFirstFlag = false)
    {
        if (!$num || $depotID == Depots::MAIN_DEPOT_ID) {
            return true;
        }
        $receiveDate = (!$useFirstFlag) ? date('Y-m-d') : '0000-00-00';
        $expDate = (!$useFirstFlag) ? date('Y-m-d', strtotime('+' . self::DAYS_DISPOSAL . ' days')) : '2100-01-01';
        return self::$db->exec('INSERT INTO `' . self::TABLE . '` (`part_id`, `num`, `depot_id`, `receive_date`, `exp_date`) VALUES (?, ?, ?, ?, ?)', [$partID, $num, $depotID, $receiveDate, $expDate]);
    }


    public static function getDisposalPartsCount($depotID)
    {
        $rows = self::$db->exec('SELECT COUNT(*) AS cnt FROM `' . Batches::TABLE . '` WHERE `depot_id` = ? AND `exp_date` <= "' . date('Y-m-d') . '"', [$depotID]);
        return ($rows) ? $rows[0]['cnt'] : 0;
    }


    /**
     * Возвращает готовое к утилизации количество определенной запчасти
     * 
     * @param int $partID Запчасть
     * @param int $depotID Склад
     * 
     * @return int Кол-во запчастей, готовое к утилизации
     */
    public static function getDisposalPartNum($partID, $depotID)
    {
        $rows = self::$db->exec('SELECT SUM(`num`) AS num FROM `' . Batches::TABLE . '` WHERE `depot_id` = ? AND `part_id` = ? AND `exp_date` <= "' . date('Y-m-d') . '"', [$depotID, $partID]);
        return (!empty($rows[0]['num'])) ? $rows[0]['num'] : 0;
    }


    /**
     * Удаляет информацию из партий
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
        $rows = self::$db->exec('SELECT * FROM `' . Batches::TABLE . '` WHERE `depot_id` = ? AND `part_id` = ? AND `receive_date` = ?', [$depotID, $partID, date('Y-m-d', strtotime($receiveDate))]);
        if (!$rows) {
            return true;
        }
        $newQty = $rows[0]['num'] - $qty;
        if ($newQty <= 0) {
            return self::$db->exec('DELETE FROM `' . Batches::TABLE . '` WHERE `id` = ?', [$rows[0]['id']]);
        }
        return self::$db->exec('UPDATE `' . Batches::TABLE . '` SET `num` = ? WHERE `id` = ?', [$newQty, $rows[0]['id']]);
    }


    public static function take($partID, $num, $depotID)
    {
        if (!$num || $depotID == Depots::MAIN_DEPOT_ID) {
            return true;
        }
        while ($num > 0) {
            $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `part_id` = ? AND `depot_id` = ? ORDER BY `receive_date` LIMIT 1', [$partID, $depotID]);
            if (!$rows) {
                return true; // партий нет - не проводить списание
            }
            $batchID = $rows[0]['id'];
            $batchNum = $rows[0]['num'];
            if ($batchNum <= $num) {
                /* Партия пустая и больше не нужна */
                $r = self::$db->exec('DELETE FROM `' . self::TABLE . '` WHERE `id` = ?', [$batchID]);
            } else {
                /* Партия уменьшена */
                $r = self::$db->exec('UPDATE `' . self::TABLE . '` SET `num` = ? WHERE `id` = ?', [($batchNum - $num), $batchID]);
            }
            if (!$r) {
                return false;
            }
            $num -= $batchNum;
        }
        return true;
    }
}

Batches::init();
