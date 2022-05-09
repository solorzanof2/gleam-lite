<?php

namespace gleamlite\http;

class ApiProxyResult
{
  const Ok = 0;
  const Error = -1;

  public $statusCode = 0;
  public $body;
  public $headers = [
    'Content-Type' => 'application/json; charset=utf8'
  ];

  public function __construct($body = null, int $statusCode = self::Ok, array $headers = [])
  {
    $this->statusCode = $statusCode;
    $this->body = $body;
    $this->headers += $headers;
  }
  
  static function getInstance($body, int $statusCode = self::Ok, array $headers = []): ApiProxyResult
  {
    return new self($body, $statusCode, $headers);
  }

  public function hasBody(): bool
  {
    return ($this->body !== null);
  }
  
}