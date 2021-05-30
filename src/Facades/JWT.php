<?php
/**
 * 
 * @fileName JWT.php
 * @category PHP
 * @package void
 * @author Kee Guo <chinboy2012@gmail.com> 
 * @since 29/05/2018
 * @version JWT.php 2018.05.29
 * */
namespace Lex\Facades;

use Illuminate\Support\Facades\Facade;

class JWT extends Facade {

  protected static function getFacadeAccessor() {
    return 'jwt';
  }
}
