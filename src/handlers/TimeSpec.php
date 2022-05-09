<?php

namespace handlers;

use Exception;
use gleamlite\http\ApiProxyEvent;
use gleamlite\http\ApiProxyResult;
use gleamlite\utils\DateUtils;
use models\AppResponse;

return function(ApiProxyEvent $event): ApiProxyResult {
  try {
    $shortDate = date('Y-m-d');
    $milliseconds = strtotime($shortDate) * 1000;
    $response = [
      'manual' => [
        'millisecondsNow' => (time() * 1000),
        'millisecondsFromDate' => [
          'result' => $milliseconds,
          'date' => $shortDate,
        ],
        'dateFromMilliseconds' => date('Y-m-d', ($milliseconds / 1000)),
      ],
      'spec' => [
        'millisecondsNow' => DateUtils::nowMilliseconds(),
        'millisecondsFromDate' => [
          'result' => DateUtils::todayMilliseconds(),
          'date' => DateUtils::nowDatabaseShortDate()
        ],
        'dateFromMilliseconds' => DateUtils::millisecondsToShortDate($milliseconds),
      ],
    ];

    return AppResponse::ok($response);
  }
  catch (Exception $error) {
    return AppResponse::error($error);
  }
};