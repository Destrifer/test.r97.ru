<?php

namespace models\store\dashboard;

use models\User;
use program\core\App;

/** 
 * Вкладки дашборда
 */

class Tabs extends \models\_Model
{

    const BASE_URL = '/dashboard/';
    private static $tabs = [
        'questions' => ['name' => 'Есть вопросы', 'need_cnt' => 0],
        'needparts' => ['name' => 'Нужны запчасти', 'need_cnt' => 0],
        'requesttesler' => ['name' => 'Запрос у Tesler', 'need_cnt' => 0],
        'requestroch' => ['name' => 'Запрос у Roch', 'need_cnt' => 0],
        'inprocess' => ['name' => 'В обработке', 'need_cnt' => 0],
        'waittesler' => ['name' => 'Ждем з/ч Tesler', 'need_cnt' => 0],
        'waitroch' => ['name' => 'Ждем з/ч Roch', 'need_cnt' => 0],
        'factory' => ['name' => 'Заказ на заводе', 'need_cnt' => 0],
        'partsintransit' => ['name' => 'Запчасти в пути', 'need_cnt' => 0]
    ];


    public static function getTabs()
    {
        $result = [];
        $curTab = App::$URL[1];
        $t = self::$tabs;
        foreach ($t as $uri => $tab) {
            $tab['active_flag'] = $curTab == $uri;
            $tab['cnt'] = 0;
            $tab['special_cnt'] = 0;
            $tab['url'] = self::BASE_URL . $uri . '/';
            $tab['uri'] = $uri;
            $result[] = $tab;
        }
        return $result;
    }
}

Tabs::init();
