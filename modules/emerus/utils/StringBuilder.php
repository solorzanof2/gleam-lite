<?php

namespace emerus\utils;

class StringBuilder
{

  private $data = [];

  public function __construct()
  {
    $this->data = [];
  }

  public function getLength(): int
  {
    return count($this->data ?? []);
  }

  public function append(string $value): StringBuilder
  {
    $this->data[] = $value;
    return $this;
  }

  public function clear(): StringBuilder
  {
    $this->data = [];
    return $this;
  }

  public function remove(int $startIndex, int $length): void
  {
    array_splice($this->data, $startIndex, $length);
  }

  public function toString(string $separator = ''): string
  {
    return implode($separator, $this->data);
  }
}
