<?php
/**
 * 
 * @fileName lex/src/Application.php
 * @category PHP
 * @package keesely/lex
 * @author Kee Guo <chinboy2012@gmail.com> 
 * @since 29/05/2021
 * @version 1.0.1 2021.05.29
 * */
namespace Lex;

use Laravel;
use Laravel\Lumen\Bootstrap\LoadEnvironmentVariables;
use Lex\Support\Attribute;
use Lex\Routing\Route;
use Exception as ApplicationException;

class Application {

  use Attribute;

  protected $basePath = null;
  protected $app_path = null;
  protected $app = null;

  public function __construct(string $basePath = null) {
    if ($basePath) {
      $this->basePath = $basePath;
      $this->app_path = realpath($basePath . '/app');
      $this->__init();
    }
  }

  public function __init() {
    (new LoadEnvironmentVariables($this->basePath))->bootstrap();
    date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

    $this->app = new Laravel\Lumen\Application($this->basePath);

    $this->useStoragePath($this->basePath . DIRECTORY_SEPARATOR . 'storage');

    $this->configure('app', 'database');

    try {
      if ($configure = config('app.configure')) {
        $this->configure($configure);
      }

      if ($aliases = config('app.aliases')) {
        $this->app->withFacades(true, array_flip($aliases));
      }

      if ($providers = config('app.providers')) {
        $this->register($providers);
      }

      if ($middleware = config('app.middleware')) {
        $this->middleware($middleware);
      }

      if ($routeMiddleware = config('app.routeMiddleware')) {
        $this->routeMiddleware($routeMiddleware);
      }

      $this->app->router = Route::getInstance($this->app);

    } catch (ApplicationException $e) {
      return response($e);
    }

    return $this->app;
  }

  public function configure(...$configure) {
    foreach ($configure as $k => $config) {
      if ($path = $this->getConfigurationPath($config. '.php')) {
        $this->app->make('config')->set($config, require $path);
        unset($configure[$k]);
      }
    }
    if ($configure) {
      $this->app->configure(...$configure);
    }
    return $this;
  }

  protected function getApp() {
    return app();
  }

  protected function getConfigurationPath($name = null) {
    $name = 'config/' . $name;
    $config_dir = $this->app->basePath($name);
    return realpath($config_dir) ?: realpath(__DIR__ . '/../' . $name);
  }

  public function __call($name, $args) {
    if (!$this->app) throw new ApplicationException('System error', 500);
    return $this->app->$name(...$args);
  }

  public function __get($name) {
    return $this->app->$name ?? null;
  }

  public function loadRouter() {
    $this->router->group([
      'namespace' => config('app.namespace', 'App\Http\Controllers')
    ], function ($router) {
      $app = $router;
      if ($route_dir = realpath($this->basePath . '/routes')) {
        foreach (scandir($route_dir) as $route) {
          if ('.php' == substr($route, strlen($route) - 4)) {
            require $route_dir . '/' . $route;
          }
        }
      }
    });

    return $this;
  }

  public function run() {
    // developer - console - open Illuminate\Contracts\Console\Kernel
    if ('local' === env('APP_ENV')) {
      $this->app->bind(
        \Illuminate\Database\ConnectionResolverInterface::class, 
        \Illuminate\Database\ConnectionResolver::class
      );
      //$this->app->register(
        //\Lex\Providers\LumenCommandsServiceProvider::class
      //);
    }

    $this->loadRouter();
    return $this->app->run();
  }
}

