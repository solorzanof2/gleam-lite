<?php

namespace gleamlite\router;

use Exception;

/**
 * An exception derivation which represents that a route hasn't been found
 *
 * @package Router
 */
class RouteNotFoundException extends \Exception
{
  public function __construct(string $message, int $code = 0, Exception $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }

  public function __toString()
  {
    $class = __CLASS__;
    return "{$class}: [{$this->getCode()}]: {$this->getMessage()};";
  }
}
