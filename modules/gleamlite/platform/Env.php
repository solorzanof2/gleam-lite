<?php

namespace gleamlite\platform;

use ArrayObject;
use gleamlite\core\Singleton;

class Env extends ArrayObject
{
  private static $properties;
  
  use Singleton;

  public function __construct()
  {
    foreach (self::$properties as $property => $value) {
      $this[$property] = $value;
    }
  }
  
  public static function configure(array $variables = []): void
  {
    if (empty($variables)) {
      return;
    }

    self::$properties = $variables;
    self::getInstance();
  }

  public function __get(string $property)
  {
    if ($this->offsetExists($property)) {
      return $this[$property];
    }
    return null;
  }

  public function __set(string $property, $value)
  {
    $this[$property] = $value;
  }
  
}