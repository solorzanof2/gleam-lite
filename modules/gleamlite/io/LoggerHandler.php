<?php

namespace gleamlite\io;

class LoggerHandler
{

  private static $instance;
  
  private static $defaultProperties = [
    'dateFormat' => 'd-M-y H:i:s',
    'enable' => true,
    'errorsLogger' => true,
    'errorsVerbose' => true,
    'traceEnable' => true,
    'showSql' => true,
    'filename' => ''
  ];

  private $properties;

  private $classname;

  private function __construct(Properties $properties, string $classname = '')
  {
    $this->properties = $properties;
    $this->classname = $classname;
  }

  //////////////////////
  // @Methods PUBLICS //
  //////////////////////

  public static function getLogger(string $classname = ''): LoggerHandler
  {
    self::$instance->setClassname($classname);
    return self::$instance;
  }

  public static function configure(): void
  {
    if (!self::$instance) {
      self::$instance = new self(new Properties(self::$defaultProperties));
    }
  }

  public function setClassname(string $classname = ''): void
  {
    $this->classname = $classname;
  }

  public function info(string $message): void
  {
    Logger::write($message, Logger::Info);
  }

  public function infoByClass(string $methodName, $message): void
  {
    $this->info($this->getMethodFormat($this->classname, $methodName, $message));
  }

  public function sql(string $methodName, string $message): void
  {
    if (!$this->properties->showSql()) {
      return;
    }
    $this->infoByClass($methodName, $message);
  }

  public function warning(string $message): void
  {
    Logger::write($message, Logger::Warning);
  }

  public function warningByClass(string $methodName, string $message): void
  {
    $this->warning($this->getMethodFormat($this->classname, $methodName, $message));
  }

  public function error(string $message): void
  {
    if (!$this->properties->errorsLogger()) {
      return;
    }

    Logger::write($message, Logger::Error);
  }

  public function errorByClass(string $methodName, string $message): void
  {
    $this->error($this->getMethodFormat($this->classname, $methodName, $message));
  }

  private function getMethodFormat(string $classname, string $methodName, string $message): string
  {
    return "[{$classname}] ({$methodName}) {$message}";
  }

  public function isTraceEnable(): bool
  {
    return $this->properties['isTraceEnable'];
  }
}
