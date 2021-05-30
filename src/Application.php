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

  protected static $_configureRegisted = false;
  protected static $_routes = [];

  public function __construct(string $basePath = null) {
    if ($basePath) {
      $this->basePath = $basePath;
      $this->app_path = realpath($basePath . '/app');
      $this->__init();

      if ('local' === env('APP_ENV')) {
        $this->loadDevelpment();
      }
    }
  }

  protected function __init() {
    (new LoadEnvironmentVariables($this->basePath))->bootstrap();
    date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

    $this->app = new Laravel\Lumen\Application($this->basePath);

    $this->useStoragePath($this->basePath . DIRECTORY_SEPARATOR . 'storage');
    $this->registerConfigure();

    $this->app->singleton('app', function() {
      return new \Lex\Facades\AppManage();
    });
    $this->withFacades(true, [
      \Lex\Facades\App::class => 'App'
    ]);

    $this->app->withFacades(true, array_flip(config('app.aliases', [])));

    if ($providers = config('app.providers')) {
      $this->register($providers);
    }

    if ($middleware = config('app.middleware')) {
      $this->middleware($middleware);
    }

    if ($routeMiddleware = config('app.routeMiddleware')) {
      $this->routeMiddleware($routeMiddleware);
    }

    return $this->app;
  }

  protected function loadDevelpment() {
    // developer - console - open Illuminate\Contracts\Console\Kernel
    $this->app->bind(
      \Illuminate\Database\ConnectionResolverInterface::class, 
      \Illuminate\Database\ConnectionResolver::class
    );
    $this->app->register(
      \Lex\Providers\LumenCommandsServiceProvider::class
    );
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

  protected function registerRouterBindings() {
    $this->singleton('router', function () {
      return Route::getInstance();
    });
    $this->app->withAliases([
      \Illuminate\Support\Facades\Route::class => 'Route',
    ]);
  }

  protected function registerConfigure() {
    $this->configure('app');
    $defaults = [
      'database', 
      'filesystems',
      'logging',
      'cache',
      'bootstrap',
    ];

    $configure = config('app.configure', []);
    if (!self::$_configureRegisted) {
      $configure = array_merge($defaults, $configure);
      self::$_configureRegisted = true;
    }

    if ($configure) {
      $this->configure(...$configure);
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
    $this->app->router = Route::getInstance($this->app);
    $this->registerRouterBindings();
    $this->router->group([
      'namespace' => config('app.namespace', 'App\Http\Controllers')
    ], function ($router) {
      if (!count(self::$_routes)) {
        if ($route_dir = realpath($this->basePath . '/routes')) {
          foreach (scandir($route_dir) as $route) {
            if ('.php' == substr($route, strlen($route) - 4)) {
              self::$_routes[] = $route;
              require $route_dir . '/' . $route;
            }
          }
        }
      }
    });

    return $this->app;
  }

  public function withCookie() {
    $this->app->register(\Illuminate\Cookie\CookieServiceProvider::class);
    $this->app->bind(\Illuminate\Contracts\Cookie\QueueingFactory::class, 'cookie');
    $this->app->middleware([
      \Illuminate\Cookie\Middleware\EncryptCookies::class,
      \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    ]);
    return $this;
  }

  public function withSession() {
    $this->configure('session');
    $this->app->register(
      \Lex\Providers\LumenCommandsServiceProvider::class,
      \Illuminate\Session\SessionServiceProvider::class
    );
    $this->app->bind(\Illuminate\Session\SessionManager::class, function ($app) {
      return new \Illuminate\Session\SessionManager($app);
    });
    $this->app->middleware(\Illuminate\Session\Middleware\StartSession::class);
    return $this;
  }

  public function withAuth() {
    $this->configure('auth');
    $this->app->bind(\Illuminate\Auth\AuthManager::class, function ($app) {
      return new \Illuminate\Auth\AuthManager($app);
    });
    return $this;
  }

  public function withFilesystem() {
    $this->configure('filesystems');
    $this->app->bind(\Illuminate\Contracts\Filesystem\Factory::class, function ($app) {
      return new \Illuminate\Filesystem\FilesystemManager($app);
    });
    $this->app->singleton('filesystem', function ($app) {
      return $app->loadComponent(
        'filesystems',
        \Illuminate\Filesystem\FilesystemServiceProvider::class,
        'filesystem'
      );
    });
    return $this;
  }

  public function withCache() {
    $this->configure('cache');
    $this->app->bind(\Illuminate\Cache\CacheManager::class, function ($app) {
      return new \Illuminate\Cache\CacheManager($app);
    });
    return $this;
  }

  public function withResponse() {
    $this->app->singleton(\Illuminate\Contracts\Routing\ResponseFactory::class, function ($app) {
      return new \Illuminate\Routing\ResponseFactory(
        $app['Illuminate\Contracts\View\Factory'], 
        $app['Illuminate\Routing\Redirector']
      );
    });
  }

  public function with(...$with) {
    foreach($with as $method) {
      $method = 'with' . ucfirst($method);
      if (method_exists($this, $method)) {
        $this->$method();
      }
    }
  }

  public function run($request = null) {
    return $this->loadRouter()->run($request);
  }
}

