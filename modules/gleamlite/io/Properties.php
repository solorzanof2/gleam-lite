<?php

namespace gleamlite\io;

class Properties
{

  private $dateFormat = 'd-M-y H:i:s';

  private $enable = true;

  private $errorsLogger = true;

  private $errorsVerbose = true;

  private $traceEnable = true;

  private $filename = '';

  private $showSql = true;

  public function __construct(array $properties = [])
  {
    if (empty($properties)) {
      return;
    }

    foreach ($properties as $property => $value) {
      $this->$property = $value;
    }
  }

  public function getDateFormat(): string
  {
    return $this->dateFormat;
  }

  public function getDate(): string
  {
    return date($this->dateFormat);
  }

  public function isEnable(): bool
  {
    return $this->enable;
  }

  public function errorsLogger(): bool
  {
    return $this->errorsLogger;
  }

  public function errorsVerbose(): bool
  {
    return $this->errorsVerbose;
  }

  public function isTraceEnable(): bool
  {
    return $this->traceEnable;
  }

  public function showSql(): bool
  {
    return $this->showSql;
  }

  public function getFilename(): string
  {
    return $this->filename;
  }
}
