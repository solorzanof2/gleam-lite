<?php

namespace gleamlite\utils;

class ObjectUtils
{

  public static function isNotEmpty($object): bool
  {
    return !(empty($object));
  }

  public static function isNotNull($object): bool
  {
    return !(is_null($object));
  }

}