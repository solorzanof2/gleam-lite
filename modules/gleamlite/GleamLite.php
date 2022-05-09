<?php

namespace gleamlite;

use Exception;
use gleamlite\http\Dispatcher;
use gleamlite\io\ErrorHandler;
use gleamlite\io\LoggerHandler;
use gleamlite\platform\Env;
use gleamlite\platform\Timer;

class GleamLite {

  static function start(array $configuration): void
  {
    Timer::start();
    
    $provider = $configuration['provider'];
    date_default_timezone_set($provider['timezone']);

    // activate logger;
    LoggerHandler::configure();
    LoggerHandler::getLogger()->info('START Process '.$_SERVER['REQUEST_URI']);

    // activate exception handler;
    ErrorHandler::configure();

    // activate environment variables handler;
    Env::configure($configuration['environment']);

    // check for database;
    $databaseConfiguration = null;
    foreach ($configuration['resources'] as $resource) {
      if ($resource['type'] == 'database') { // FIXME this way is taking only one database;
        $databaseConfiguration = $resource;
        break;
      }
    }

    if ($databaseConfiguration) {
      self::configureDatabase($databaseConfiguration);
    }

    $basePath = $provider['basePath'];
    $functions = $configuration['functions'];
    $apiKey = $provider['apiKey'] ?? null;

    // activate dispatcher and also dispatch;
    $dispatcher = Dispatcher::getInstance($basePath, $functions, $apiKey);
    $dispatcher->preflightHandler($provider['origin']);
    $dispatcher->doDispatch();

    LoggerHandler::getLogger()->info('END Process');
    die;
  }

  private static function configureDatabase(array $configuration): void {
    //requiring database handler file;
    $databaseHandlerPath = __ROOT__.$configuration['handler'].__PHP;
    if (!file_exists($databaseHandlerPath)) {
      throw new Exception("Database Handler Not Found, requiring {$databaseHandlerPath}");
    }

    # making available the database variables;
    $db = $configuration['properties'];

    require_once($databaseHandlerPath);
  }
  
}