<?php

namespace models;

use program\core;

/** 
 * Повторные ремонты
 * 2022-03-22
 */

class Repeats extends _Model
{
    public static $message = '';
    private static $db = null;
    private static $problems = [];


    public static function init()
    {
        self::$db = _Base::getDB();
    }


    public static function getRepeats(array $filter)
    {
        $data = [];
        $whereDatesSQL = '';
        if (!empty($filter['date1']) && !empty($filter['date2'])) {
            $whereDatesSQL = ' AND `approve_date` BETWEEN "' . $filter['date1'] . '" AND "' . $filter['date2'] . '"';
        }
        $models = self::getModels($whereDatesSQL);
        /* Запросы под вид статистики */
        $queries = [
            'markdown' => '`problem_id` IN (' . self::getProblemsIDs('markdown') . ') AND `repair_final` = 2',
            'returns' => '`problem_id` IN (' . self::getProblemsIDs('returns') . ') AND (`repair_final` = 3 OR `repair_final` = 1)',
            'discard' => '`problem_id` IN (' . self::getProblemsIDs('discard') . ') AND `repair_final` = 2'
        ];
        /* Группировка моделей по категориям */
        foreach ($models as $row) {
            if (!isset($data[$row['cat_id']])) {
                $data[$row['cat_id']] = [
                    'cat' => $row['cat'],
                    'cat_id' => $row['cat_id'],
                    'stat' => [
                        'markdown' => ['cnt' => 0, 'sum' => 0],
                        'returns' => ['cnt' => 0, 'sum' => 0],
                        'discard' => ['cnt' => 0, 'sum' => 0],
                        'total' => ['cnt' => 0, 'sum' => 0]
                    ],
                    'models' => []
                ];
            }
            $data[$row['cat_id']]['models'][] = ['model' => $row['model'], 'model_id' => $row['model_id']];
        }
        /* Получение статистики по каждой модели и категории в целом */
        foreach ($data as $k => $group) {
            for ($i = 0, $cnt = count($group['models']); $i < $cnt; $i++) {
                foreach ($queries as $name => $query) {
                    $rowsStat = self::$db->exec('SELECT COUNT(*) AS cnt, (SUM(total_price) + SUM(transport_cost) + SUM(parts_cost)) as sum 
                FROM `repairs` 
                WHERE `id` IN (
                    SELECT `id` 
                    FROM `repairs` 
                    WHERE ' . $query . ' 
                    ' . $whereDatesSQL . ' 
                    GROUP BY `serial`) 
                AND `deleted` = 0 AND `anrp_use` = 1 AND `doubled` = 1 AND `service_id` = 33 AND `model_id` = ' . $group['models'][$i]['model_id']);
                    $data[$k]['models'][$i][$name] = $rowsStat[0];
                    $data[$k]['stat'][$name]['cnt'] += $rowsStat[0]['cnt'];
                    $data[$k]['stat'][$name]['sum'] += $rowsStat[0]['sum'];
                    $data[$k]['stat']['total']['cnt'] += $rowsStat[0]['cnt'];
                    $data[$k]['stat']['total']['sum'] += $rowsStat[0]['sum'];
                }
                $data[$k]['models'][$i]['total'] = [
                    'cnt' => $data[$k]['models'][$i]['markdown']['cnt'] + $data[$k]['models'][$i]['returns']['cnt'] + $data[$k]['models'][$i]['discard']['cnt'],
                    'sum' => $data[$k]['models'][$i]['markdown']['sum'] + $data[$k]['models'][$i]['returns']['sum'] + $data[$k]['models'][$i]['discard']['sum']
                ];
            }
        }
        return $data;
    }


    private static function getModels($whereDatesSQL)
    {
        return self::$db->exec('SELECT 
        m.`model_id`, m.`name` AS model, m.`cat_id`, c.`name` AS cat
        FROM `models_users` m 
        LEFT JOIN `cats` c ON c.`id` = m.`cat_id` 
        WHERE m.`model_id` IN (
            SELECT `model_id` 
            FROM `repairs` 
            WHERE `deleted` = 0 AND `service_id` = 33 AND `doubled` = 1 AND `anrp_use` = 1 ' . $whereDatesSQL . ' 
        ) 
        AND m.`service` = "Да" AND m.`service_id` = 33 ORDER BY c.`name`');
    }


    private static function getProblemsIDs($type)
    {
        if (!self::$problems) {
            self::$problems = ['repair' => [], 'diag' => [], 'nonrepair' => []];
            $rows = self::$db->exec('SELECT `id`, `work_type` FROM `details_problem`');
            foreach ($rows as $row) {
                if (empty($row['work_type'])) {
                    continue;
                }
                self::$problems[$row['work_type']][] = $row['id'];
            }
        }
        switch ($type) {
            case 'markdown':
            case 'returns':
                return implode(',', array_merge(self::$problems['nonrepair'], self::$problems['diag']));
            case 'discard':
                return implode(',', self::$problems['repair']);
            default:
                throw new \Exception('Wrong problem type: ' . $type);
        }
    }
}


Repeats::init();
