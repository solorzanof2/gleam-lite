<?php

namespace gleamlite\http;

use Exception;
use gleamlite\router\Router;

class Dispatcher extends HttpExchange {

  function __construct(string $basePath, array $functions, $apiKey)
  {
    parent::__construct($apiKey);

    $this->router = new Router($basePath);
    foreach ($functions as $function => $properties) {
      foreach ($properties['methods'] as $method) {
        $this->router->$method($properties['path'], $properties['handler'], $properties['private'] ?? false);
      }
    }
  }

  public static function getInstance(string $basePath, array $functions, string $apiKey = null): Dispatcher
  {
    return new self($basePath, $functions, $apiKey);
  }

  public function doDispatch(): void
  {
    try {
      $component = $this->router->getByMethod($this->method);
      if ($component->isPrivate) {
        $this->checkApiKey();
      }

      $event = new ApiProxyEvent();
      $event->url = $this->url;
      $event->method = $this->method;
      $event->headers = $this->requestHeaders;
      $event->pathParameters = $component->pathParameters;
      $event->queryStringParameters = $this->queryString;
      $event->body = $this->getRequestBody();

      $handler = $component->instance;
      $this->sendResponse($handler($event));
    }
    catch (Exception $error) {
      $this->sendErrorResponse($error->getMessage());
    }
  }

}