<?php

namespace gleamlite\io;

use gleamlite\utils\SecurityUtils;

class Logger
{
  const LogPath = __ROOT__.'storage/logs';
  const StartLabel = 'START';
  const EndLabel = 'END';

  const Info = 'INF0';
  const Warning = 'WARN';
  const Error = 'ERR0';

  private static $instance;

  private $uuid = '';

  private $filename = '';

  private function __construct()
  {
    $now = $this->getDateTime();
    
    $this->createDirectory($now);

    $logPath = self::LogPath;
    $this->filename = "{$logPath}/log-{$now->toDateString()}.txt";

    $this->uuid = SecurityUtils::createUUID();
  }

  public static function write(string $message, string $severity = self::Info): void
  {
    if (!self::$instance) {
      self::$instance = new self();
    }

    $datetime = self::$instance->getDateTime(true);
    $uuid = self::$instance->uuid;
    self::$instance->print("{$datetime->toDateTimeString()} RequestId: {$uuid} {$severity} {$message}");
  }

  private function getDateTime(bool $full = false): LoggerTime
  {
    list($year, $month, $day) = explode('-', date('Y-m-d'));

    $result = new LoggerTime();
    $result->year = $year;
    $result->month = $month;
    $result->day = $day;

    if (!$full) {
      return $result;
    }

    list($hour, $minutes, $seconds) = explode(':', date('h:i:s'));

    $result->hours = $hour;
    $result->minutes = $minutes;
    $result->seconds = $seconds;

    return $result;
  }

  private function createDirectory(LoggerTime $now): void
  {
    if (is_dir(self::LogPath)) {
      return;
    }

    if (!mkdir(self::LogPath)) {
      trigger_error('Logger - Could not create the log directory.', E_USER_ERROR);
      return;
    }

    @chmod(self::LogPath, (int) "0777");
  }

  private function print(string $message): void
  {
    if (!$file = @fopen($this->filename, "a+b")) {
      trigger_error("Logger - Could not open file {$this->filename}", E_USER_ERROR);
      return;
    }

    flock($file, LOCK_EX);
    fwrite($file, $message . PHP_EOL);
    flock($file, LOCK_UN);
    fclose($file);

    @chmod($this->filename, (int) "0666");
  }
}

class LoggerTime
{
  public $year;
  public $month;
  public $day;
  public $hours;
  public $minutes;
  public $seconds;
  
  public function toDateString(): string
  {
    return "{$this->year}-{$this->month}-{$this->day}";
  }

  public function toDateTimeString(): string
  {
    return "{$this->toDateString()} {$this->hours}:{$this->minutes}:{$this->seconds}";
  }
}