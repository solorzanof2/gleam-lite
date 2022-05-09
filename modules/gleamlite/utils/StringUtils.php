<?php

namespace gleamlite\utils;

class StringUtils
{

  public static function contains(string $haystack, string $needle): bool
  {
    return (strpos($haystack, $needle) > 0);
  }

  public static function startsWith(string $source, string $needle): bool
  {
    return (substr($source, 0, strlen($needle)) === $needle);
  }

  public static function endsWith(string $source, string $needle): bool
  {
    $length = strlen($needle);
    if ($length == 0) {
      return true;
    }
    return (substr($source, -$length) === $needle);
  }
  
  public static function equals(string $firstValue, string $secondValue): bool
  {
    return (strcasecmp(strtolower($firstValue), strtolower($secondValue)) == 0);
  }

  public static function notEquals(string $firstValue, string $secondValue): bool
  {
    return (!self::equals($firstValue, $secondValue));
  }

  public static function isNull(string $text = null): bool
  {
    $text = trim($text);
    return (is_null($text) || empty($text));
  }

  public static function isNotNull(string $text = null): bool
  {
    return (!self::isNull($text));
  }

  public static function specialCharsNormalizer(string $string): string
  {
    $search =  explode(",", "á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,ñ,Á,É,Í,Ó,Ú,À,È,Ì,Ò,Ù,Ä,Ë,Ï,Ö,Ü,Â,Ê,Î,Ô,Û,Ñ");
    $replace = explode(",", "a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,n,A,E,I,O,U,A,E,I,O,U,A,E,I,O,U,A,E,I,O,U,N");
    return str_replace($search, $replace, $string);
  }

  public static function filter(string $string): string
  {
    $search = explode("+", '"+,+-');
    $replace = explode("+", ' + + ');
    return str_replace($search, $replace, $string);
  }

  public static function createSlug(string $slug): string
  {
    $dashSeparator = '-';

    $result = self::filter($slug);
    $result = self::specialCharsNormalizer($result);
    $result = trim(preg_replace('/\s+/', $dashSeparator, $result), $dashSeparator);
    return strtolower($result);
  }
  
}