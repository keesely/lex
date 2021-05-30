<?php
/**
 * 
 * @fileName JWTServiceProvider.php
 * @category PHP
 * @package void
 * @author Kee Guo <chinboy2012@gmail.com> 
 * @since 25/06/2018
 * @version JWTServiceProvider.php 2018.06.25
 * */

namespace Lex\Providers;

use Illuminate\Support\ServiceProvider;

class JWTServiceProvider extends ServiceProvider {
 
  /**
   * Register any application services.
   *
   * @return void
   */
  public function register() {
    $this->app->singleton('jwt', function () {
      return new \Lex\Facades\JWTManage();
    });
  }

  /**
   * Boot the authentication services for the application.
   *
   * @return void
   */
  public function boot () {
    // ...
  }
}
