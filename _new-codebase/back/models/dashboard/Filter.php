<?php

namespace models\dashboard;

use models\staff\Staff;
use models\User;
use program\core;

class Filter extends \models\_Model
{

    private static $filter = [
        'outside' => ['name' => 'Выезд', 'type' => 'select', 'val' => ''],
        'sell_date' => ['name' => 'Дата: продажа', 'type' => 'date_interval', 'val' => ''],
        'begin_date' => ['name' => 'Дата: начало ремонта', 'type' => 'date_interval', 'val' => ''],
        'out_date' => ['name' => 'Дата: выдача', 'type' => 'date_interval', 'val' => ''],
        'receive_date' => ['name' => 'Дата: прием', 'type' => 'date_interval', 'val' => ''],
        'finish_date' => ['name' => 'Дата: конец ремонта', 'type' => 'date_interval', 'val' => ''],
        'provider' => ['name' => 'Завод', 'type' => 'select', 'val' => ''],
        'plant' => ['name' => 'Завод-сборщик', 'type' => 'select', 'val' => ''],
        'order' => ['name' => 'Заказ', 'type' => 'text', 'val' => ''],
        'defect_cl' => ['name' => 'Заявленная неисправность', 'type' => 'text', 'val' => ''],
        'exc_service' => ['name' => 'Кроме СЦ', 'type' => 'select_search', 'val' => 0],
        'master' => ['name' => 'Мастер', 'type' => 'select', 'val' => ''],
        'model' => ['name' => 'Модель', 'type' => 'select_search', 'val' => 0],
        'service' => ['name' => 'Наименование СЦ', 'type' => 'select_search', 'val' => 0],
        'id' => ['name' => 'Номер карточки', 'type' => 'text', 'val' => ''],
        'client_type' => ['name' => 'От кого поступил', 'type' => 'select', 'val' => ''],
        'client' => ['name' => 'Принят от', 'type' => 'text', 'val' => ''],
        'serial' => ['name' => 'Серийный номер', 'type' => 'text', 'val' => ''],
        'issue' => ['name' => 'Фактическая неисправность', 'type' => 'select_search', 'val' => 0],
        'status' => ['name' => 'Статус', 'type' => 'select', 'val' => ''],
        'ship_status' => ['name' => 'Статус поступления', 'type' => 'select', 'val' => ''],
        'recept_status' => ['name' => 'Статус приёма', 'type' => 'select', 'val' => ''],
        'country' => ['name' => 'Страна', 'type' => 'select', 'val' => 0],
        'color' => ['name' => 'Цвет', 'type' => 'select', 'val' => ''],
    ];

    public static function getFilter()
    {
        if (!User::hasRole('service')) {
            return self::$filter;
        }
        $f = self::$filter;
        unset($f['country']);
        unset($f['service']);
        unset($f['exc_service']);
        unset($f['plant']);
        unset($f['order']);
        unset($f['provider']);
        return $f;
    }

    public static function getActiveFilter()
    {
        $f = [];
        foreach (self::$filter as $uri => $fl) {
            if (empty(core\App::$URLParams[$uri])) {
                continue;
            }
            $fl['val'] = core\App::$URLParams[$uri];
            $fl['uri'] = $uri;
            $fl['values'] = self::getFilterInputValue($uri);
            $f[$uri] = $fl;
        }
        return $f;
    }

    public static function getFilterByURI($filterURI)
    {
        if (isset(self::$filter[$filterURI])) {
            $f = self::$filter[$filterURI];
            $f['uri'] = $filterURI;
            return $f;
        }
        return [];
    }

    /* Возможные значения для выбора по ключу фильтра */
    public static function getFilterInputValue($filterURI)
    {
        $modelID = (!empty(core\App::$URLParams['model'])) ? core\App::$URLParams['model'] : 0;
        switch ($filterURI) {
            case 'service':
            case 'exc_service':
                return \models\Services::getServicesList();
            case 'country':
                return Data::$countries;
            case 'provider':
                return \models\Providers::getProvidersList($modelID);
            case 'plant':
                return \models\Plants::getPlantsList();
            case 'model':
                return \models\Models::getModelsList();
            case 'issue': // симптом
                return \models\Issues::getIssuesList();
            case 'recept_status': // статус приема (гарантийный и др.)
                return Data::$receptStatuses;
            case 'ship_status': // статус поступления 
                return [2 => 'Клиентский', 3 => 'Повторный', 1 => 'Предторговый'];
            case 'status':
                $statuses = \models\ParamsDict::getParamsBySectionID(1);
                return array_column($statuses, 'name', 'name');
            case 'client_type':
                return [1 => 'Потребитель', 2 => 'Магазин'];
            case 'outside':
                return [1 => 'Да', 2 => 'Нет'];
            case 'color':
                return [
                    'blue' => 'Голубой',
                    'darkblue' => 'Синий',
                    'yellow' => 'Жёлтый',
                    'red' => 'Красный',
                    'brown' => 'Коричневый',
                    'darkgreen' => 'Зелёный',
                    'gray' => 'Серый',
                    'purple' => 'Фиолетовый'
                ];
            case 'master':
                return array_column(Staff::getStaffList(['is_active' => 1]), 'full_name', 'id');
            default:
                return '';
        }
    }
}


Filter::init();
