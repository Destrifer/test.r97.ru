<?php

namespace models\dashboard;

use models\Counters;
use program\core;
use \models\User;


class UI extends \models\_Model
{

    private static $db = null;
    private static $tabs = [
        'questions' => ['uri' => 'questions', 'name' => 'Есть вопросы', 'need_cnt' => 1],
        'oncheck' => ['uri' => 'oncheck', 'name' => 'На проверке', 'need_cnt' => 1],
        'needparts' => ['uri' => 'needparts', 'name' => 'Нужны запчасти', 'need_cnt' => 1],
        'softreq' => ['uri' => 'softreq', 'name' => 'Запрос документов и ПО', 'need_cnt' => 1],
        'factorytesler' => ['uri' => 'factorytesler', 'name' => 'Запрос у Tesler', 'need_cnt' => 1],
        'factoryroch' => ['uri' => 'factoryroch', 'name' => 'Запрос у Roch', 'need_cnt' => 1],
        'all' => ['uri' => 'all', 'name' => 'Все', 'need_cnt' => 0],
        'accepted' => ['uri' => 'accepted', 'name' => 'Принят', 'need_cnt' => 0],
        'outside' => ['uri' => 'outside', 'name' => 'Выезд/Демонтаж/Монтаж', 'need_cnt' => 1],
        'approvedact' => ['uri' => 'approvedact', 'name' => 'Одобрен акт', 'need_cnt' => 1],
        'inprocess' => ['uri' => 'inprocess', 'name' => 'В обработке', 'need_cnt' => 1],
        'inprocess2' => ['uri' => 'inprocess2', 'name' => 'В обработке', 'need_cnt' => 1],
        'waittesler' => ['uri' => 'waittesler', 'name' => 'Ждем з/ч Tesler', 'need_cnt' => 1],
        'waitroch' => ['uri' => 'waitroch', 'name' => 'Ждем з/ч Roch', 'need_cnt' => 1],
        'factory' => ['uri' => 'factory', 'name' => 'Заказ на заводе', 'need_cnt' => 1],
        'partsintransit' => ['uri' => 'partsintransit', 'name' => 'Запчасти в пути', 'need_cnt' => 1],
        'cancelled' => ['uri' => 'cancelled', 'name' => 'Отклоненные', 'need_cnt' => 0],
        'approve' => ['uri' => 'approve', 'name' => 'Подтвержденные', 'need_cnt' => 0],
        'inprogress' => ['uri' => 'inprogress', 'name' => 'В работе', 'need_cnt' => 0],
        'ready' => ['uri' => 'ready', 'name' => 'Готов', 'need_cnt' => 0],
        'issued' => ['uri' => 'issued', 'name' => 'Выдан', 'need_cnt' => 0],
        'markdown' => ['uri' => 'markdown', 'name' => 'Уценка', 'need_cnt' => 0],
        'disposed' => ['uri' => 'disposed', 'name' => 'Утилизирован', 'need_cnt' => 0],
        'deleted' => ['uri' => 'deleted', 'name' => 'Удаленные', 'need_cnt' => 0]
    ];
    private static $curTab = '';


    public static function init()
    {
        self::$db = \models\_Base::getDB();
        self::$curTab = (!empty(core\App::$URLParams['tab'])) ? core\App::$URLParams['tab'] : '';
    }


    public static function goToDefaultTab()
    {
        if (!empty(self::$curTab)) {
            return;
        }
        if (User::hasRole('taker')) {
            header('Location: /dashboard/?tab=' . self::$tabs['accepted']['uri']);
            exit;
        }
        if (User::hasRole('service', 'admin', 'slave-admin')) {
            header('Location: /dashboard/?tab=' . self::$tabs['all']['uri']);
            exit;
        }
        if (User::hasRole('master')) {
            header('Location: /dashboard/?tab=' . self::$tabs['accepted']['uri']);
            exit;
        }
    }


    public static function getCols()
    {
        $cols = Settings::getCurrentCols(User::getData('id'), self::$curTab);
        if (!$cols) {
            $cols = Settings::getAvailableCols(User::getData('role'), []);
        }
        return $cols;
    }


    public static function getPagination()
    {
        return Data::getPagination();
    }


    public static function getTabs($userRole)
    {
        $tabs = [];
        switch ($userRole) {
            case 'admin':
                $t = [
                    'questions', 'oncheck', 'needparts', 'softreq',
                    'factorytesler', 'factoryroch', 'outside', 'approvedact', 'inprocess', 'waittesler', 'waitroch', 'factory', 'partsintransit',
                    'cancelled', 'approve', 'inprogress', 'accepted', 'deleted', 'all'
                ];
                break;
            case 'taker':
                $t = ['all', 'accepted', 'outside', 'inprogress', 'ready', 'issued'];
                self::$tabs['accepted']['need_cnt'] = 1;
                self::$tabs['inprogress']['need_cnt'] = 1;
                self::$tabs['ready']['need_cnt'] = 1;
                break;
            case 'slave-admin':
                $t = ['all', 'accepted', 'outside', 'inprogress', 'ready', 'issued', 'markdown', 'disposed'];
                self::$tabs['accepted']['need_cnt'] = 1;
                self::$tabs['inprogress']['need_cnt'] = 1;
                break;
            case 'master':
                $t = ['accepted', 'inprogress', 'ready', 'issued', 'all'];
                self::$tabs['accepted']['need_cnt'] = 1;
                self::$tabs['inprogress']['need_cnt'] = 1;
                break;
            case 'service':
                $t = ['accepted', 'outside', 'inprogress', 'needparts', 'softreq', 'questions', 'inprocess2', 'approvedact', 'partsintransit', 'oncheck', 'approve', 'cancelled', 'all'];
                foreach ($t as $uri) {
                    if (in_array($uri, ['all', 'cancelled', 'approve'])) {
                        continue;
                    }
                    self::$tabs[$uri]['need_cnt'] = 1;
                }
                break;
        }
        foreach ($t as $uri) {
            $tab = self::$tabs[$uri];
            $tab['active_flag'] = self::$curTab == $tab['uri'];
            $tab['cnt'] = self::getTabCnt($uri);
            $tab['cnt_color'] = ($userRole == 'service' && in_array($uri, ['questions', 'inprogress', 'accepted', 'partsintransit', 'approvedact'])) ? 'red' : '';
            $tab['special_cnt'] = 0;
            $tab['url'] = '/dashboard/' . ((!empty($tab['uri'])) ? '?tab=' . $tab['uri'] : '');
            $tabs[$uri] = $tab;
        }
        if ($userRole == 'service') {
            $tabs['approve']['special_cnt'] = Counters::getTotalCount('approved', User::getData('id'));
        }
        return $tabs;
    }


    private static function getTabCnt($uri)
    {
        if (self::$tabs[$uri]['need_cnt']) {
            $where = DBExtractor::$where;
            /* Временное, для ИП Кулиджанов */
            if ($uri == 'outside' && !User::hasRole('service', 'slave-admin')) {
                $where[] = '`status_admin` = "Запрос на выезд"';
                $where[] = '`service_id` != 33';
            } else {
                $where[] = DBExtractor::getTabWhere($uri);
            }
            if (User::hasRole('taker') && $uri == 'ready') {
                $where[] = '`client_type` = 1'; // Потребитель
            }
            /*  */
            $rows = self::$db->exec('SELECT COUNT(*) AS cnt FROM `repairs` WHERE ' . implode(' AND ', $where));
            return $rows[0]['cnt'];
        }
        return 0;
    }


    public static function getTabSort()
    {
        switch (self::$curTab) {
            case 'all':
                return ['field' => 'receive_date', 'dir' => 'desc'];
            case 'questions':
            case 'oncheck':
            case 'needparts':
            case 'softreq':
            case 'factorytesler':
            case 'factoryroch':
            case 'outside':
            case 'approvedact':
            case 'inprocess':
            case 'factory':
            case 'partsintransit':
            case 'accepted':
            case 'inprogress':
            case 'inprogress2':
            case 'outside':
                return ['field' => 'receive_date', 'dir' => 'asc'];
            case 'ready':
                return ['field' => 'ready_date', 'dir' => 'desc'];
            case 'issued':
                return ['field' => 'out_date', 'dir' => 'desc'];
            case 'disposed':
            case 'markdown':
                return ['field' => 'ready_date', 'dir' => 'desc'];
            case 'cancelled':
                return ['field' => 'create_date', 'dir' => 'desc'];
            case 'waittesler':
            case 'waitroch':
                return ['field' => 'receive_date', 'dir' => 'asc'];
            default:
                return ['field' => 'receive_date', 'dir' => 'desc'];
        }
    }


    public static function getColorsCaptions($userRole)
    {
        switch ($userRole) {
            case 'admin':
                return [
                    'brown' => 'Использована деталь РСЦ. Требуется компенсация.',
                    'darkblue' => 'Не распознана модель или ошибка при загрузке.',
                    'yellow' => 'Не заполнены поля (серийный, модель, неисправность и др.).',
                    'purple' => 'Неверный серийный № или модель.',
                    'blue' => 'Ранее выдан АНРП региональным СЦ.',
                    'darkgreen' => 'Платный ремонт.',
                    'gray' => 'Истёк срок гарантии. Проверить дату продажи.',
                    'red' => 'Повторный ремонт. Проверить предыдущий.',
                ];
            case 'slave-admin':
            case 'taker':
                return [
                    'brown' => 'Использована деталь РСЦ. Требуется компенсация.',
                    'darkblue' => 'Не распознана модель или ошибка при загрузке.',
                    'yellow' => 'Не заполнены поля (серийный, модель, неисправность и др.).',
                    'purple' => 'Неверный серийный № или модель.',
                    'blue' => 'Ранее выдан АНРП региональным СЦ.',
                    'darkgreen' => 'Платный ремонт.',
                    'gray' => 'Истёк срок гарантии. Можно выбрать вручную.',
                    'red' => 'Повторный ремонт. Проверить предыдущий.',
                ];
            case 'service':
                return [
                    'brown' => 'Использована собственная деталь. Будет компенсирована.',
                    'yellow' => 'Не заполнены поля (серийный, модель, неисправность и др.).',
                    'purple' => 'Неверный серийный № или модель, либо его нет в базе номеров. Необходимо обратиться к админу.',
                    'darkgreen' => 'Платный ремонт. Не компенсируется сервису.',
                    'gray' => 'Истёк срок гарантии. Можно обратиться к админу или исправить на Платный.',
                    'red' => 'Повторный ремонт. Проверить предыдущий или обратиться к админу за дополнительной информацией.',
                    'blue' => 'Подтверждённые карточки готовые к выдаче. Цвет и уведомление снимается при входе в карточку.'
                ];
            case 'master':
                return [
                    'brown' => 'Использована деталь РСЦ.',
                    'darkblue' => 'Не распознана модель или ошибка при загрузке. Исправить или обратиться к приёмщику.',
                    'yellow' => 'Не заполнены поля (серийный, модель, неисправность и др.). Исправить или обратиться к приёмщику.',
                    'purple' => 'Неверный серийный № или модель. Исправить или обратиться к приёмщику.',
                    'blue' => 'Ранее выдан АНРП региональным СЦ.',
                    'darkgreen' => 'Платный ремонт.',
                    'gray' => 'Истёк срок гарантии. Обратиться к приёмщику.',
                    'red' => 'Повторный ремонт. Проверить предыдущий.',
                ];
        }
    }
}


UI::init();
