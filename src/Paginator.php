<?php
/**
 * 
 * @fileName /Paginator.php
 * @category PHP
 * @package Kee\LumenExtra
 * @author Kee Guo <chinboy2012@gmail.com> 
 * @since 14/09/2017
 * @version /Paginator.php 2017.09.14
 * */
namespace Lex;
use \Exception;

class Paginator {

  public $count = 0;
  protected $_current = 0;
  protected $_url = false;
  protected $_keyword = 'page';
  protected $_search = array();
  protected $_items = [
    'pageCount' => 0,
    'current' => 1,
    'previous' => 0,
    'next' => 0,
    'last' => 0,
    'perPage' => 20,
    'total' => 0,
    'pagesInRange' => array()
  ];

  public function __construct ($current, $perPage, $count) {
    if (!is_int($current)) throw new Exception('$Page is not a valid integer');
    if (!is_int($perPage)) throw new Exception('$perPage is not a valid integer');

    $this->_getItems(intval($current), intval($perPage), $count)
      ->_getPageCount()
      ->_getPages()
      ->_getPagesInRange();
  }

  protected function _getItems ($current, $perPage, $count) {
    $this->current = intval($current);
    $this->perPage = intval($perPage);
    $this->total = intval($count);
    $this->_current = (0 < $current) ? (intval($current) - 1) * intval($perPage) : 0;
    $this->count = $count;
    return $this;
  }

  protected function _getPageCount () {
    $this->pageCount = (0 <= $this->count) ? ceil($this->count/$this->perPage) : 0;
    $pagesInRange = array();
    for ($i=1; $i<=$this->pageCount; $i++) $pagesInRange[] = $i;
    $this->pagesInRange = $pagesInRange;
    return $this;
  }

  protected function _getPages () {
    $this->previous = (0 >= $this->current) ? null : ($this->current-1);
    $this->next = (count($this->pagesInRange) <= $this->current) ? count($this->pagesInRange) : ($this->current+1);
    $this->last = $this->pageCount;
    if ($this->previous > $this->pageCount) $this->previous = $this->pageCount;
    if (null == $this->previous) unset($this->previous);
    if ($this->next == $this->current) unset($this->next);
    return $this;
  }

  protected function _getPagesInRange () {
    $prev=2;//前面几位
    $next=3;//后面几位
    $total=$prev+$next+1;//总位数
    if(count($this->pagesInRange)<=$total) return $this;
    $range = $this->pagesInRange;
    $pagesInRange=array();
    $min=$this->current-$prev;
    $max=$this->current+$next;
    $a=$min-min($range);
    $b=max($range)-$max;
    $min_array=min($range);
    $max_array=max($range);
    if($a<=0){
      for($i=1;$i<=$total;$i++){
        $pagesInRange[]=$min_array;
        $min_array++;
      }
      $this->pagesInRange = $pagesInRange;
      return $this;
    }
    if($b<=0){
      for($i=$total;$i>=1;$i--) $pagesInRange[]=$max_array-$i+1;
      $this->pagesInRange = $pagesInRange;
      return $this;
    }
    for($i=-$prev;$i<=$next;$i++) $pagesInRange[]=$this->current+$i;
    $this->pagesInRange = $pagesInRange;
    return $this;
  }

  public function __get ($name) {
    if (!isset($this->_items[$name])) return NULL;
    return $this->_items[$name];
  }

  public function __set ($name, $value) {
    if (!isset($this->_items[$name])) return $value;
    return $this->_items[$name] = $value;
  }

  protected function _setURL () {
    if ($this->_search) {
      if (!$this->_url) $this->_url = '';
      $this->_url .= (false !== strpos($this->_url, '?'))
        ? (((false !== strpos($this->_url, '&')) ? '' : '&') . http_build_query($this->_search))
        : '?' . http_build_query($this->_search);
    }
    if ($this->_url && $this->_items['pagesInRange']) {
      $keywork = $this->_keyword ?: 'page';
      foreach ($this->_items['pagesInRange'] as &$range) {
        if (false !== strpos($this->_url, '?')) {
          $range = $this->_url . '&'.$keywork.'=' . $range;
        }
        else $range = $this->_url . '?'.$keywork.'=' . $range;
      }
    }
    return $this;
  }

  public function setURL ($url, $keywork = 'page') {
    $this->_url = $url;
    $this->_keyword = $keywork ?: 'page';
    return $this;
  }

  public function setSearch (array $search) {
    $this->_search = $search;
    return $this;
  }

  public function toArray (array $style = NULL) {
    $this->_setURL();
    if (!$style) return $this->_items;
    $data = [];
    foreach ($style as $as => $key) {
      $data[$as] = $this->$key;
    } 
    return $data;
  }
}
