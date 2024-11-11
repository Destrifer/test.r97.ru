<?php

namespace models\repaircard;

class Acceptance extends \models\_Model
{

    private static $db = null;

    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }

    public static function getClientTypes()
    {
        return ['' => 'Выберите вариант', 2 => 'Магазин', 1 => 'Потребитель'];
    }

    public static function getModels()
    {
        $c = array_column(self::$db->exec('SELECT `id`, `name` FROM `models` WHERE `is_deleted` = 0 ORDER BY `name` ASC'), 'name', 'id');
        $c[''] = 'Выберите модель';
        return $c;
    }

    public static function getWarrantyCards()
    {
        return ['' => 'Выберите вариант', 'Гарантийный талон' => 'Гарантийный талон', 'Гарантийный талон+Чек' => 'Гарантийный талон+Чек', 'Документы отсутствуют' => 'Документы отсутствуют', 'Чек' => 'Чек'];
    }

    public static function getAcceptStatuses()
    {
        return ['' => 'Выберите вариант', 1 => 'Гарантийный', 6 => 'Платный', 5 => 'Условно-гарантийный'];
    }

    public static function getShipStatuses()
    {
        return ['' => 'Выберите вариант', 2 => 'Клиентский', 3 => 'Повторный', 1 => 'Предторговый'];
    }

    public static function getContents()
    {
        return [
            'ПОЛНАЯ' => 'ПОЛНАЯ', 'КОР' => 'КОР', 'АПП' => 'АПП', 'ГАР ТАЛ' => 'ГАР ТАЛ',
            'ЧЕК' => 'ЧЕК', 'ПДУ' => 'ПДУ', 'НОЖКИ' => 'НОЖКИ', 'ПОДСТАВКА' => 'ПОДСТАВКА'
        ];
    }

    public static function getExterior()
    {
        return ['НОВЫЙ' => 'НОВЫЙ', 'Б/У' => 'Б/У', 'ЦАРАПИНЫ' => 'ЦАРАПИНЫ'];
    }
}

Acceptance::init();
