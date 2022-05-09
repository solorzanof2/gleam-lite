<?php

namespace gleamlite\http;

use ArrayObject;

class RequestBody extends ArrayObject
{

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
