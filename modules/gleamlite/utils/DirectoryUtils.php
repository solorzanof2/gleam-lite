<?php

namespace gleamlite\utils;

class DirectoryUtils
{

  public static function createUploadFolder($directory): ?string
  {
    $now = date('Y/m');
    $fullpath = "{$directory}{$now}";
    if (!is_dir($fullpath)) {
      if (self::createFolder($fullpath)) {
        return "{$fullpath}/";
      }
      return null;
    }
    return "{$fullpath}/";
  }

  public static function createFolder($directory): bool
  {
    if (mkdir($directory, 0777, true)) {
      chmod($directory, 0777);
      return true;
    }
    return false;
  }
  
}