<?php
/**
 * 
 * @fileName Route.php
 * @category PHP
 * @package keesely/lex
 * @author Kee Guo <chinboy2012@gmail.com> 
 * @since 30/05/2021
 * @version 1.0.1 2021.05.29
 * */
namespace Lex\Routing;

class Route {

  private static $_route;

  private function __construct() {
  }

  public static function getInstance($app = null) {
    if (!self::$_route) {
      self::$_route = new Router($app);
    }
    //class_alias(__class__, 'Route');
    return self::$_route;
  }

  public static function __callStatic($method, $args) {
    return self::$_route->$method(...$args);
  }

  public static function App() {
    return self::$_route->app;
  }
}
