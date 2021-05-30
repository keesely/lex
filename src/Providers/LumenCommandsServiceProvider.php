<?php
namespace Lex\Providers;

use Illuminate\Support\ServiceProvider;

class LumenCommandsServiceProvider extends ServiceProvider
{
  public function register() {
    $this->commands([
      \Lex\Commands\ConsoleMakeCommand::class,
      \Lex\Commands\CastMakeCommand::class,
      \Lex\Commands\ControllerMakeCommand::class,
      \Lex\Commands\ExceptionMakeCommand::class,
      \Lex\Commands\JobMakeCommand::class,
      \Lex\Commands\KeyGenerateCommand::class,
      \Lex\Commands\ModelMakeCommand::class,
      \Lex\Commands\MiddlewareMakeCommand::class,
      \Lex\Commands\MigrateDBCommand::class,
      \Lex\Commands\VendorPublishCommand::class,
    ]);
  }
}
