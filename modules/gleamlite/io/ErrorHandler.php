<?php

namespace gleamlite\io;

use ErrorException;
use gleamlite\platform\Timer;

class ErrorHandler {

  private static $instance;

  private $severities = [
    1 => 'ERROR',
    2 => 'WARNING',
    4 => 'ERROR',
    8 => 'NOTICE',
    64 => 'ERROR',
    256 => 'ERROR',
    512 => 'WARNING',
    1024 => 'NOTICE',
    4096 => 'ERROR'
  ];
  
  public function __construct()
  {
    set_error_handler([$this, 'errorHandler']);
    set_exception_handler([$this, 'exceptionHandler']);
    register_shutdown_function([$this, 'shutdownHandler']);
  }

  public static function configure(): void
  {
    if (self::$instance) {
      return;
    }
    self::$instance = new self();
  }

  public function errorHandler($type = false, $message = false, $file = false, $line = false): bool
  {
    $this->exceptionHandler(new ErrorException($message, 0, $type, $file, $line));
    return true;
  }

  public function exceptionHandler($error): bool
  {
    $severity = 'NOTICE';
    if ($error instanceof ErrorException) {
      $severity = $this->severities[$error->getSeverity()];
    }

    $message = PHP_EOL;
    $message .= "---------------------------------------------------------------------------------------------------" . PHP_EOL;
    $message .= "TYPE: [{$severity}]".PHP_EOL;
    $message .= "MESSAGE: {$error->getMessage()}".PHP_EOL;
    $message .= $error->getTraceAsString().PHP_EOL;
    $message .= "FILE: {$error->getFile()}".PHP_EOL;
    $message .= "LINE: {$error->getLine()}".PHP_EOL;
    $message .= "---------------------------------------------------------------------------------------------------";

    $equivalences = [
      'NOTICE' => Logger::Info,
      'WARNING' => Logger::Warning,
      'ERROR' => Logger::Error,
    ];

    Logger::write($message, $equivalences[$severity]);

    header('HTTP/1.1 500 Internal Server Error');
    header("Content-Type: application/json");
    echo json_encode([
      'status' => 'API_ERROR',
      'response' => 'An unhandled error occurred'
    ]);

    die;
  }

  public function shutdownHandler(): bool
  {
    $error = error_get_last();
    if (!$error) {
      Logger::write('REPORT '.Timer::stop());
      return false;
    }

    list('type' => $type, 'message' => $message, 'file' => $file, 'line' => $line) = $error;
    $this->errorHandler($type, $message, $file, $line);
    return true;
  }
}