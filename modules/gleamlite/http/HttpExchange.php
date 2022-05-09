<?php

namespace gleamlite\http;

use Exception;
use gleamlite\utils\StringUtils;
use stdClass;

class HttpExchange
{

  const AJAX_BODY_METHODS = ['POST', 'PUT', 'PATCH'];

  const SUCCESS_STATUS = 0;

  const SUCCESS_MESSAGE = 'OK';

  const ERROR_STATUS = -1;

  private $statusCode = 200;

  private $headers = [];

  private $body;

  public static $statusCodesCollection = [
    100 => 'Continue',
    101 => 'Switching Protocols',
    102 => 'Processing',

    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    203 => 'Non-Authoritative Information',
    204 => 'No Content',
    205 => 'Reset Content',
    206 => 'Partial Content',
    207 => 'Multi-Status',
    208 => 'Already Reported',

    226 => 'IM Used',

    300 => 'Multiple Choices',
    301 => 'Moved Permanently',
    302 => 'Found',
    303 => 'See Other',
    304 => 'Not Modified',
    305 => 'Use Proxy',
    306 => '(Unused)',
    307 => 'Temporary Redirect',
    308 => 'Permanent Redirect',

    400 => 'Bad Request',
    401 => 'Unauthorized',
    402 => 'Payment Required',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Timeout',
    409 => 'Conflict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Payload Too Large',
    414 => 'URI Too Long',
    415 => 'Unsupported Media Type',
    416 => 'Range Not Satisfiable',
    417 => 'Expectation Failed',

    422 => 'Unprocessable Entity',
    423 => 'Locked',
    424 => 'Failed Dependency',

    426 => 'Upgrade Required',

    428 => 'Precondition Required',
    429 => 'Too Many Requests',

    431 => 'Request Header Fields Too Large',

    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout',
    505 => 'HTTP Version Not Supported',
    506 => 'Variant Also Negotiates',
    507 => 'Insufficient Storage',
    508 => 'Loop Detected',

    510 => 'Not Extended',
    511 => 'Network Authentication Required'
  ];

  protected $origin;

  protected $isSameOrigin = false;

  protected $allowOrigin;

  protected $method;

  protected $contentType;

  protected $url;

  protected $requestHeaders = [];

  protected $requestApiKey;

  protected $queryString = [];

  ///////////
  // @Data //
  ///////////

  /**
   * @var RequestBody
   */
  protected $requestBody;

  public function __construct(string $apiKey = null)
  {
    $this->requestHeaders = $this->getRequestHeaders();

    $this->url = $this->getVar('REQUEST_URI');
    $this->origin = $this->getVar('HTTP_ORIGIN');
    // $this->isSameOrigin = (in_array($this->requestHeaders['sec-fetch-site'], ['same-origin', 'same-site', 'none']));
    $this->isSameOrigin = true;

    if (StringUtils::isNull($this->origin) && !$this->isSameOrigin) {
      $this->sendForbidden(['response' => 'Missing Origin Header']);
    }
    
    $this->method = $this->getVar('REQUEST_METHOD');
    $this->contentType = $this->getVar('CONTENT_TYPE');

    $this->requestBody = $this->mapData();
    $this->requestApiKey = $apiKey ?? 'request-failure';
  }

  private function getRequestHeaders(): Headers {
    $collection = apache_request_headers();
    $headers = new Headers();
    foreach ($collection as $header => $value) {
      $lowerCase = strtolower($header);
      $headers->$lowerCase = $value;
    }
    return $headers;
  }

  private function mapData(): RequestBody
  {
    $request = new RequestBody();
    $this->queryString = new stdClass();

    if (!empty($_GET)) {
      foreach ($_GET as $property => $value) {
        $this->queryString->$property = $value;
      }
    }

    if (StringUtils::startsWith($this->contentType, 'application/json')) {
      $body = $this->getAjaxData();
      if (!is_null($body)) {
        $collection = json_decode($body, true);
        if (!is_null($collection)) {
          foreach ($collection as $property => $value) {
            $request->$property = $value;
          }
        }
      }
    }

    if (!empty($_POST)) {
      foreach ($_POST as $property => $value) {
        $request->$property = $value;
      }
    }

    if (!empty($_FILES)) {
      foreach ($_FILES as $property => $value) {
        $request->$property = $value;
      }
    }
    return $request;
  }

  private function getVar(string $key, $default = ''): string
  {
    return $_SERVER[$key] ?? $default;
  }

  private function getAjaxData(): ?string
  {
    static $body;

    if (!is_null($body)) {
      return $body;
    }

    if (in_array($this->method, self::AJAX_BODY_METHODS)) {
      $body = file_get_contents('php://input');
    }

    return $body;
  }

  public function checkApiKey(): void {
    if (StringUtils::notEquals($this->requestApiKey, $this->requestHeaders['x-api-key'] ?? 'header-failure')) {
      $this->sendForbidden(['response' => 'Missing Authentication Token']);
    }
  }

  public function getRequestBody(): RequestBody
  {
    return $this->requestBody;
  }

  public function preflightHandler(string $allowOrigin = ''): void
  {
    $this->allowOrigin = $allowOrigin ?? '';
    if (StringUtils::notEquals($this->allowOrigin, $this->origin) && !$this->isSameOrigin) {
      // $this->logger->methodDebug( __FUNCTION__, "Publishing 403 - Missing origin in request header for OPTIONS." );
      $this->sendForbidden(['response' => '403 Forbidden']);
      die;
    }

    if ($this->method == RequestMethods::OPTIONS) {
      // $this->logger->methodDebug( __FUNCTION__, "Publishing OPTIONS headers." );
      header("Access-Control-Allow-Origin: {$this->allowOrigin}");
      header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
      header('Access-Control-Allow-Headers: Content-Type, Authorization, Cache-Control, X-Requested-With, x-api-key, Access-Control-Allow-Origin');
      header('Access-Control-Allow-Credentials: true');
      header('Access-Control-Max-Age: 1728000');
      header("Content-Length: 0");
      header("Content-Type: application/json");
      exit(0);
    }

    $this->headers['Access-Control-Allow-Origin'] = $this->allowOrigin;
  }

  public function setStatus(int $statusCode = null): HttpExchange
  {
    if ($statusCode === null) {
      return $this->statusCode;
    }

    if (array_key_exists($statusCode, self::$statusCodesCollection)) {
      $this->statusCode = $statusCode;
    }
    else {
      throw new Exception("Invalid http status code {$statusCode};");
    }

    return $this;
  }

  public function setHeader(string $name, string $value = null): HttpExchange
  {
    $this->headers[$name] = $value;
    return $this;
  }

  public function setHeaders(array $collection = []): HttpExchange
  {
    foreach ($collection as $key => $value) {
      $this->setHeader($key, $value);
    }
    
    return $this;
  }

  public function getHeaders(): array
  {
    return $this->headers;
  }

  public function getResponseBody(): string
  {
    return $this->body;
  }

  public function setResponseBody(string $body): HttpExchange
  {
    $this->body .= $body;

    return $this;
  }

  public function clear(): HttpExchange
  {
    $this->statusCode = 200;
    $this->headers = [];
    $this->body = '';

    return $this;
  }

  public function cache($expires = false): HttpExchange
  {
    if ($expires === false) {
      $this->headers['Expires'] = 'Mon, 26 Jul 1997 05:00:00 GMT';
      $this->headers['Cache-Control'] = [
        'no-store, no-cache, must-revalidate',
        'post-check=0, pre-check=0',
        'max-age=0'
      ];
      $this->headers['Pragma'] = 'no-cache';
    } else {
      $expires = is_int($expires) ? $expires : strtotime($expires);
      $this->headers['Expires'] = gmdate('D, d M Y H:i:s', $expires) . ' GMT';
      $this->headers['Cache-Control'] = 'max-age=' . ($expires - time());
      if (isset($this->headers['Pragma']) && $this->headers['Pragma'] == 'no-cache') {
        unset($this->headers['Pragma']);
      }
    }
    return $this;
  }

  public function sendHeaders(): HttpExchange
  {
    # Send status code header
    if (strpos(php_sapi_name(), 'cgi') !== false) {
      header(
        sprintf(
          'Status: %d %s',
          $this->statusCode,
          self::$statusCodesCollection[$this->statusCode]
        ),
        true
      );
    }
    else {
      header(
        sprintf(
          '%s %d %s',
          (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1'),
          $this->statusCode,
          self::$statusCodesCollection[$this->statusCode]
        ),
        true,
        $this->statusCode
      );
    }

    # Send other headers
    foreach ($this->headers as $field => $value) {
      if (is_array($value)) {
        foreach ($value as $v) {
          header("{$field}: {$v}", false);
        }
      } else {
        header("{$field}: {$value}");
      }
    }

    # Send content length
    if (($length = strlen($this->body)) > 0) {
      header("Content-Length: {$length}");
    }

    return $this;
  }

  public function send(): void
  {

    if (!headers_sent()) {
      $this->sendHeaders();
    }

    echo $this->body;
  }

  public function sendErrorResponse(string $message): void
  {
    $this->sendResponse(ResponseEntity::serverError($message));
  }

  public function sendResponse(ApiProxyResult $response): void
  {
    $this
      ->setStatus($response->statusCode)
      ->setHeaders($response->headers);
  
    $payload = self::SUCCESS_MESSAGE;
    if ($response->hasBody()) {
      $payload = json_encode($response->body);
    }

    $this
      ->setResponseBody($payload)
      ->send();
  }

  private function sendForbidden(array $message): void {
    header('HTTP/1.1 403 Forbidden');
    header("Content-Type: application/json");
    // header("Access-Control-Allow-Origin: {$this->allowOrigin}");
    echo json_encode($message);
    die;
  }
}
