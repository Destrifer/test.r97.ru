<?php


namespace program\core;

/**
 * v. 1
 * 2020-07-03
 */

class Paginator
{
  public $totalItemsCnt;
  public $qtyOnPage;
  public $curPageNum;
  public $qtyVisibleNums = 6;
  protected $pagesQty;


  public function __construct($totalItemsCnt = 0, $qtyOnPage = 20, $curPageNum = 1)
  {
    $this->totalItemsCnt = $totalItemsCnt;
    $this->qtyOnPage = $qtyOnPage;
    $this->curPageNum = ($curPageNum == 0) ? 1 : $curPageNum;
    $this->pagesQty = ceil($this->totalItemsCnt / $this->qtyOnPage);
  }


  public function getOffset()
  {
    if ($this->curPageNum == 1) {
      return 0;
    }
    return ($this->curPageNum - 1) * $this->qtyOnPage;
  }

  public function getLimit()
  {
    if ($this->curPageNum == 1) {
      return $this->qtyOnPage;
    }
    return (($this->curPageNum - 1) * $this->qtyOnPage) . ', ' . $this->qtyOnPage;
  }

  public function getPagination($url = '', $urlParamName = 'page')
  {
    $url = (!$url) ? $_SERVER['REQUEST_URI'] : $url;
    $url = trim(preg_replace('/' . $urlParamName . '=[\d]+[\?|&]?/', '', $url), '&?');
    $urlChar = (strpos($url, '?') === false) ? '?' : '&';
    $nums = [];
    if ($this->pagesQty == 0 || $this->pagesQty == 1) {
      return $nums;
    }
    if ($this->pagesQty < $this->qtyVisibleNums) {
      $nums = range(1, $this->pagesQty);
    } elseif ($this->curPageNum == 1) {
      $nums = $this->addDotToEnd(range(1, $this->qtyVisibleNums));
    } else {
      $isStart = false;
      $isEnd = false;
      $half = round($this->qtyVisibleNums / 2);
      $s = $this->curPageNum - $half - 1;
      $f = $this->curPageNum + $half;
      if ($s <= 0) {
        $isStart = true;
        $f += abs($s);
        $s = 1;
      }
      if ($f >= $this->pagesQty) {
        $isEnd = true;
        $s -= $f - $this->pagesQty;
        if($s < 1){
          $s = 1;
        }
        $f = $this->pagesQty;
      }
      $nums = range($s, $f);
      if (!$isStart && $nums[0] >= 2) {
        $nums = $this->addDotToStart($nums);
      }
      if (!$isEnd) {
        $nums = $this->addDotToEnd($nums);
      }
    }
    $pagination = [];
    foreach ($nums as $num) {
      $item = ['url' => '', 'value' => $num, 'active_flag' => false];
      if ($num != '...') {
        $item['url'] = $url . $urlChar . $urlParamName . '=' . $num;
      }
      if ($num == 1) {
        $item['url'] = $url;
      }
      if ($this->curPageNum == $num) {
        $item['active_flag'] = true;
      }
      $pagination[] = $item;
    }
    return $pagination;
  }


  public function getPagesQty()
  {
    return $this->pagesQty;
  }


  private function addDotToStart(array $nums)
  {
    array_unshift($nums, 1);
    $nums[1] = '...';
    return $nums;
  }


  private function addDotToEnd(array $nums)
  {
    $r = $this->pagesQty - 2;
    if (end($nums) <= $r) {
      $nums[] = '...';
    }
    $nums[] = $this->pagesQty;
    return $nums;
  }
}
