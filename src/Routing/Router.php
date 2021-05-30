<?php
/**
 * 
 * @fileName Router.php
 * @category PHP
 * @package keesely/lex
 * @author Kee Guo <chinboy2012@gmail.com> 
 * @since 29/05/2021
 * @version 1.0.1 2021.05.29
 * */
namespace Lex\Routing;

use Laravel\Lumen\Routing\Router as LumenRouter;
use Illuminate\Support\Arr;

class Router extends LumenRouter {
  
  static public $_routes = [];

  protected $domain = 0;

  protected $domainStack = [];

  protected function getGroupAttributes() :array {
    $attributes = [];
    if ($this->hasGroupStack()) {
      $attributes = $this->mergeWithLastGroup([]);
    }
    return $attributes;
  }

  protected function getControllerNamespace($controller) :string {
    $attributes = $this->getGroupAttributes();
    if ($namespace = $attributes['namespace'] ?? false) {
      $controller = '\\'.$namespace . '\\' . $controller;
    }
    return $controller;
  }

  public function controller ($uri, $action) {
    //$controller = $this->getControllerNamespace($action);
    $this->addRoute('GET'    , $uri         , $action . '@index');
    $this->addRoute('GET'    , $uri.'/{id}' , $action . '@show');
    $this->addRoute('POST'   , $uri         , $action . '@create');
    $this->addRoute('PUT'    , $uri.'/{id}' , $action . '@update');
    $this->addRoute('PATCH'  , $uri.'/{id}' , $action . '@patch');
    $this->addRoute('DELETE' , $uri.'/{id}' , $action . '@destroy');
    return $this;
  }

  public function any ($uri, $action) {
    $method = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];
    foreach ($method as $verb) {
      $this->addRoute($verb, $uri, $action);
    }
    return $this;
  }

  public function mix ($uri, $action) {
    $controller = $this->getControllerNamespace($action);
    if (class_exists($controller)) {
      (new MixRouter($controller))->setRoutes($this, $uri, $action);
    }
    return $this;
  }

  public function addRoute ($method, $uri, $action) {
    $action = $this->parseAction($action);

    $attributes = null;

    if ($this->hasGroupStack()) {
      $attributes = $this->mergeWithLastGroup([]);
    }

    if (isset($attributes) && is_array($attributes)) {
      if (isset($attributes['prefix'])) {
        $uri = trim($attributes['prefix'], '/').'/'.trim($uri, '/');
      }

      if (isset($attributes['suffix'])) {
        $uri = trim($uri, '/').rtrim($attributes['suffix'], '/');
      }

      $action = $this->mergeGroupAttributes($action, $attributes);
    }

    $uri = '/'.trim($uri, '/');

    if (isset($action['as'])) {
      $this->namedRoutes[$action['as']] = $uri;
    }
    if (!$this->domain) {
      $this->domain = 0;
    }

    if (is_array($method)) {
      foreach ($method as $verb) {
        self::$_routes[$this->domain][$verb.$uri] = 
          $this->routes[$verb.$uri] = ['method' => $verb, 'uri' => $uri, 'action' => $action];
      }
    } else {
      self::$_routes[$this->domain][$method.$uri] = 
        $this->routes[$method.$uri] = ['method' => $method, 'uri' => $uri, 'action' => $action];
    }
    return $this;
    //self::$_routes = $this->routes;
  }

  protected function _getRoutes () {
    $domain = $this->server_host()[1];
    $routes = self::$_routes; // ?: $this->routes;
    foreach ($routes as $host => $router) {
      if (preg_match('/^'.$host.'$/si', $domain)) {
        return $router;
      }
    }
    return $routes[0];
  }

  public function getRoutes () {
    return $this->routes = $this->_getRoutes();
  }

  public function domain ($domain, $action) {
    $std = new \FastRoute\RouteParser\Std($domain);
    $parse = $std->parse($domain);
    $domain = $this->server_host()[1];
    
    $regxstr = '';
    $dataKeys = [];
    foreach ($parse[0] as $regx) {
      if (is_array($regx)) {
        $regx[1] = str_replace('/', '.', $regx[1]);
        $regxstr .='('.$regx[1].')';
        $dataKeys[] = $regx[0];
      } else {
        $regxstr .= $regx;
      }
    }
    $data = false;
    preg_replace_callback("/^{$regxstr}$/si", function($match) use(&$data, $dataKeys) {
      $data = [];
      for ($i = 1; $i < count($match); $i++) {
        if (Arr::get($match, $i) && Arr::get($dataKeys, $i-1)) {
          $data[$dataKeys[$i-1]] = $match[$i];
        }
      }
    }, $domain);

    if (false !== $data) {
      $this->domainStack[$domain] = $data;
      $this->domain = $regxstr;

      call_user_func($action, $this);
      $this->domain = NULL;
    }
  }

  public function server_host () :array {
    $s = $_SERVER;
    $host = Arr::get($s, 'HTTP_HOST', Arr::get($s, 'SERVER_NAME'));
    $port = Arr::get($s, 'SERVER_PORT', 80);

    $https = Arr::get($s, 'HTTPS');
    $protocol = (!$https && $https !== 'off' || $port == 443) ? 'https' : 'http';
    return [$protocol, $host, $port];
  }

  public function getDomainParams () {
    return Arr::get($this->domainStack, $this->server_host()[1], []);
  }

  public function getDomainParam ($name, $default = null) {
    return Arr::get($this->getDomainParams(), $name, $default);
  }

  public function App() {
    return $this->app;
  }
}

