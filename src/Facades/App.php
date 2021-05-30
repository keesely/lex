<?php
/**
 * 
 * @fileName App.php
 * @category PHP
 * @package keesely/lex
 * @author Kee Guo <chinboy2012@gmail.com> 
 * @since 30/05/2021
 * @version 1.0.1 2021.05.30
 * */
namespace Lex\Facades;

use Illuminate\Support\Facades\Facade;

class App extends Facade {
  
  protected static function getFacadeAccessor() {
    return 'app';
  }
}
