<?php

use gleamlite\http\ApiProxyEvent;
use gleamlite\http\ApiProxyResult;
use gleamlite\http\ResponseEntity;
use models\AppResponse;
use services\SampleService;

return function(ApiProxyEvent $event): ApiProxyResult {
  try {
    $service = SampleService::getInstance();
    $message = new stdClass();
    $message->handler = 'Its Ok Dude... we can continue later...';
    $message->service = $service->sample();
    $message->url = $event->url;

    return AppResponse::ok($message);
  }
  catch (Exception $error) {
    return AppResponse::error($error);
  }
};