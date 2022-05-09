<?php

namespace gleamlite\platform;

class Timer
{

  private static $startTime;

  private function __construct() { }

  public static function start(): void
  {
    self::$startTime = microtime(true);
  }

  public static function stop(): string
  {
    $endTime = microtime(true);

    $difference = round($endTime - self::$startTime);
    $minutes = floor($difference / 60);
    $seconds = $difference % 60;

    return "{$minutes} minutes {$seconds} seconds";
  }
  
}