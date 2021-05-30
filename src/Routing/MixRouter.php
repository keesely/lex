<?php
/**
 * 
 * @fileName MixRouter.php
 * @category PHP
 * @package keesely/lex
 * @author Kee Guo <chinboy2012@gmail.com> 
 * @since 29/05/2021
 * @version 1.0.1 2021.05.29
 * */
namespace Lex\Routing;

class MixRouter {

  protected $_reflect;

  protected $_controller;

  public function __construct($controller) {
    $this->_controller = $controller;
  }

  public function setRoutes($router, $prefix, $controller) {
    $routes = $this->_controller::$routes ?? $this->parseRoutes($controller);
    $data = [];
    foreach ($routes as $route => $action) {
      $point  = strpos($route, ':');
      $method = substr($route, 0, $point);
      $uri    = substr($route, $point+1);
      $method = strtoupper($method);
      $router->addRoute($method, $prefix . $uri, $controller . '@' . $action);
    }
    return $router;
  }

  protected function parseSetRoute($method, $action, &$routes) {
    $uri = substr($action, strlen($method));
    $closure = $this->_reflect->getMethod($action);
    if (256 != $closure->getModifiers()) return false;
    $paraments = []; 
    foreach ($closure->getParameters() as $var) {
      if ($var->getType()) continue;
      $paraments[] = $var->name;
    }
    $uri = preg_replace_callback('([A-Z]+)', function($m) {
      return '/' . strtolower($m[0]);
    }, $uri);

    $i = 0;
    $uri = preg_replace_callback('/by/Usi', function ($m) use($paraments, &$i) {
      $m = $paraments[$i] ?? $i;
      $i++;
      return '{'.$m.'}';
    }, $uri);
    $uri = $uri ?: '/';

    $routes[$method.':'.$uri] = $action;
  }

  protected function parseRoutes() {
    $routes = [];
    $methods = get_class_methods($this->_controller);

    $this->_reflect = new \ReflectionClass($this->_controller);
    foreach ($methods as $m) {
      if ('index'   == $m) $routes['get:/'] = $m;
      if ('create'  == $m) $routes['post:/'] = $m;
      if ('show'    == $m) $routes['get:/{id}'] = $m;
      if ('update'  == $m) $routes['put:/{id}'] = $m;
      if ('destroy' == $m) $routes['delete:/{id}'] = $m;

      if (0 === strpos($m, 'Get'))    $this->parseSetRoute('Get'   , $m, $routes);
      if (0 === strpos($m, 'Post'))   $this->parseSetRoute('Post'  , $m, $routes);
      if (0 === strpos($m, 'Put'))    $this->parseSetRoute('Put'   , $m, $routes);
      if (0 === strpos($m, 'Patch'))  $this->parseSetRoute('Patch' , $m, $routes);
      if (0 === strpos($m, 'Delete')) $this->parseSetRoute('Delete', $m, $routes);
    }
    return $routes;
  }
}
