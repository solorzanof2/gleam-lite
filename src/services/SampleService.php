<?php

namespace services;

use gleamlite\core\Singleton;

class SampleService
{

  use Singleton;
  
  public function sample(): string
  {
    return "This message is coming from the SampleService dude!";
  }
  
}