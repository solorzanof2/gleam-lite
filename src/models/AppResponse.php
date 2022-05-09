<?php

namespace models;

use Exception;
use gleamlite\http\ApiProxyResult;
use gleamlite\http\ResponseEntity;

class AppResponse
{
  const Success = 'API_SUCCESS';
  const Error = 'API_ERROR';

  public $status = self::Success;
  public $response;

  public function __construct($data = null, string $status = AppResponse::Success)
  {
    $this->status = $status;
    $this->response = $data;
  }

  static function getInstance($data = null, string $status = AppResponse::Success): AppResponse
  {
    return new self($data, $status);
  }

  static function getError($message = ''): AppResponse
  {
    return new self($message, AppResponse::Error);
  }

  static function ok($data = null): ApiProxyResult {
    return ResponseEntity::ok(self::getInstance($data));
  }

  static function error(Exception $error): ApiProxyResult {
    return ResponseEntity::ok(self::getError($error->getMessage()));
  }
  
}