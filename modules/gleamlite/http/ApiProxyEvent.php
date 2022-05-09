<?php

namespace gleamlite\http;

class ApiProxyEvent
{

  public $url = '';
  public $method = '';
  public $headers;
  public $pathParameters;
  public $queryStringParameters;
  public $body;
  
}