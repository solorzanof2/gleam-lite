<?php

namespace gleamlite\http;

class ResponseEntity
{

  static function ok($body): ApiProxyResult
  {
    return ApiProxyResult::getInstance($body, 200);
  }

  static function noContent(): ApiProxyResult
  {
    return ApiProxyResult::getInstance('', 204);
  }
  
  static function serverError(string $message): ApiProxyResult
  {
    return ApiProxyResult::getInstance(["message" => $message], 500);
  }
  
}