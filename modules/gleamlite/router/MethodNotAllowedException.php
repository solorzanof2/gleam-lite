<?php

namespace gleamlite\router;

use Exception;

/**
 * An exception derivation which represents that a route hasn't been found
 *
 * @package Router
 */
class MethodNotAllowedException extends \Exception
{
  public function __construct(Exception $previous = null)
  {
    parent::__construct('405 Method Not Allowed', 405, $previous);
  }

  public function __toString()
  {
    $class = __CLASS__;
    return "{$class}: [{$this->getCode()}]: {$this->getMessage()};";
  }
}
