<?php


namespace program\core;


class Query
{

    public $params = [];
    private $table = '';


    public function __construct($table)
    {
        $this->table = $table;
    }


    public function insert(array $data)
    {
        $this->params = [];
        $q = 'INSERT INTO `' . $this->table . '` (`' . implode('`, `', array_keys($data)) . '`) 
        VALUES (' . implode(", ", array_fill(0, count($data), '?')) . ')';
        $this->params = array_values($data);
        return $q;
    }


    public function update(array $data, $id)
    {
        $this->params = [];
        $q = 'UPDATE `' . $this->table . '` SET `' . implode('` = ?, `', array_keys($data)) . '` = ?  
        WHERE `id` = ' . filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        $this->params = array_values($data);
        return $q;
    }
}
