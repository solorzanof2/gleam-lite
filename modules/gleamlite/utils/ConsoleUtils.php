<?php

namespace gleamlite\utils;

class ConsoleUtils
{

  public static function printOut($data, string $origin = null): void
  {
    if (!is_null($origin)) {
      echo "-- <small>${origin}</small> -- <br />";
    }
    var_dump($data);
    die;
  }
  
}