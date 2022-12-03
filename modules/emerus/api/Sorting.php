<?php

namespace emerus\api;

use emerus\utils\StringUtils;

class Sorting
{
  private $column = '';
  private $sort = '';

  public function __construct(string $column = null, string $sort = null)
  {
    if (!StringUtils::isNull($column)) {
      $this->column = $column;
    }
    if (!StringUtils::isNull($sort)) {
      $this->sort = $sort;
    }
  }

  public function getColumn(): string
  {
    return $this->column;
  }

  public function setColumn(string $column): void
  {
    $this->column = $column;
  }

  public function getSort(): string
  {
    return $this->sort;
  }

  public function setSort(string $sort): void
  {
    $this->sort = $sort;
  }
}
