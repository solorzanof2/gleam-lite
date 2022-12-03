<?php

namespace emerus;

use emerus\core\GeneralExceptionFactory;
use emerus\core\MigrationKernel;
use Exception;

class MigrationManager
{

  private $kernel;
  
  public function __construct(string $migrationPath)
  {
    $this->kernel = new MigrationKernel($migrationPath);
  }

  public static function getInstance(string $route): MigrationManager
  {
    return new self($route);
  }
  
  public function initialize(): void
  {
    try {
      $this->kernel->initialize();
      $this->kernel->start();
    }
    catch (Exception $error) {
      var_dump($error->getMessage());
      throw GeneralExceptionFactory::genericException($error->getMessage());
    }
  }
}