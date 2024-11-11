<?php

namespace models;


/** 
 * v. 0.1
 * 2020-09-16
 */

class Payments extends _Model
{
    const TABLE = 'pay_billing';
    private static $db = null;

    public static function init()
    {
        self::$db = _Base::getDB();
    }


    public static function updatePaymentSum($paymentID, $paymentBrand)
    {
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `id` = ?', [$paymentID]);
        if (!$rows) {
            return 0;
        }
        $sum = get_service_summ($rows[0]['service_id'], str_pad($rows[0]['month'], 2, '0', STR_PAD_LEFT), $rows[0]['year'], strtoupper($paymentBrand));
        if ($sum == 0 || $sum == $rows[0]['sum']) {
            return 0;
        }
        self::$db->exec('UPDATE `' . self::TABLE . '` SET `sum` = ? WHERE `id` = ?', [$sum, $paymentID]);
        return $sum;
    }
}


Payments::init();
