<?php

namespace emerus\data;

class MigrationRepository extends Repository
{

  public function __construct()
  {
    parent::__construct(Changelog::class);
  }

}