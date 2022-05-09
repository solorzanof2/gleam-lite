<?php

use gleamlite\http\ApiProxyEvent;
use gleamlite\http\ApiProxyResult;
use models\AppResponse;

return function(ApiProxyEvent $event): ApiProxyResult {
  try {
    functionFailure();
    return AppResponse::ok($event->body);
  }
  catch (Exception $error) {
    return AppResponse::error($error);
  }
};