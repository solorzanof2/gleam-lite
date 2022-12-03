<?php

namespace emerus\data;

class Migration
{

  public $author = '';

  public $title = '';

  public $changeset = '';

  public $queriesCollection = [];

  public $signature = '';

  public $rawContent = '';

  public $route = '';

  public $isProcedure = false;

  public function queriesCollectionLength(): int
  {
    return count($this->queriesCollection);
  }

}