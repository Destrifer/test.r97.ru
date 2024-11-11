<?php

namespace program\core;

/** 
 * v. 1
 * 2020-07-07
 * */

class DB
{
  public $isSuccess = false;
  public $lastInsertedID = 0;
  private $db = null;
  private $stm;
  private $prs = array();
  private $set = array();
  private $fn = 'preconnect';


  public function __construct($dbHost, $dbName, $dbUser, $dbPass, $dbDriver)
  {
    $this->set = array('hst' => $dbHost, 'nm' => $dbName, 'usr' => $dbUser, 'psw' => $dbPass, 'drv' => $dbDriver);
  }


  public function exec($dbQuery, array $dbQueryParams = array())
  {
    $f = $this->fn;
    return $this->$f($dbQuery, $dbQueryParams);
  }


  private function preconnect($qry, $prs)
  {
    $this->connect();
    return $this->run($qry, $prs);
  }


  private function connect()
  {
    $this->fn = 'run';
    $this->db = new \PDO('mysql:host=' . $this->set['hst'] . ';dbname=' . $this->set['nm'], $this->set['usr'], $this->set['psw'], $this->set['drv']);
  }


  private function run($qry, $prs)
  {
    $this->prepare($qry);
    $this->prs = $prs;
    $this->execute();
    $f = substr(ltrim($qry, '('), 0, 3);
    return $this->$f();
  }


  private function prepare($qry)
  {
    $this->stm = $this->db->prepare($qry);
    $this->stm->setFetchMode(\PDO::FETCH_ASSOC);
  }


  private function execute()
  {
    $this->stm->execute($this->prs);
    if ($this->stm->errorCode() == '00000') {
      $this->isSuccess = true;
      return;
    }
    $this->isSuccess = false;
  }


  /**
   * Транзакция
   * 
   * @param string $action begin | rollback | commit
   * 
   * @return void
   */
  public function transact($action)
  {
    if (!$this->db) {
      $this->connect();
    }
    $action = strtolower($action);
    switch ($action) {

      case 'begin':
        $this->db->beginTransaction();
        break;

      case 'rollback':
        $this->db->rollBack();
        break;

      case 'commit':
        $this->db->commit();
        break;

      default:
        throw new \Exception('Undefined type of transaction "' . $action . '".');
    }
  }


  private function UPD()
  {
    return $this->isSuccess;
  }


  private function DEL()
  {
    return $this->isSuccess;
  }


  private function CRE()
  {
    return $this->isSuccess;
  }


  private function SEL()
  {
    return $this->stm->fetchAll();
  }


  private function INS()
  {
    $this->lastInsertedID = $this->db->lastInsertId();
    return $this->lastInsertedID;
  }


  private function EXP()
  {
    echo '<pre>';
    print_r($this->stm->fetchAll());
    echo '</pre>';
    return true;
  }


  public function getErrorInfo()
  {
    $a = $this->stm->errorInfo();
    return 'SQLSTATE: ' . $a[0] . '; Driver-specific error: ' . $a[1] . '; Driver-specific message: ' . $a[2] . '.';
  }


  public function getErrorCode()
  {
    return $this->stm->errorCode();
  }


  public function hasError()
  {
    return $this->stm->errorCode() != '00000';
  }
}
