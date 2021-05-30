<?php
/**
 * 
 * @fileName AppManage.php
 * @category PHP
 * @package void
 * @author Kee Guo <chinboy2012@gmail.com> 
 * @since 30/05/2021
 * @version AppManage.php 2021.05.30
 * */
namespace Lex\Facades;

class AppManage {

  protected $app;

  public function __construct() {
    $this->app = app();
  }

  public function __get($name) {
    return $this->app->$name ?? null;
  }

  public function __set($name, $value) {
    $this->app->$name = $value;
  }

  public function __call($method, $args) {
    if (method_exists($this->app, $method)) {
      return $this->app->$method(...$args);
    }
    return null;
  }

}
