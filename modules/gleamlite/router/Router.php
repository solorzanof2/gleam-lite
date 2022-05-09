<?php

namespace gleamlite\router;

/**
 * The main router class
 *
 * @package Router
 */
class Router
{

  const GET = 'GET';

  const POST = 'POST';

  const HEAD = 'HEAD';

  const PUT = 'PUT';

  const DELETE = 'DELETE';

  /** @var string Base url */
  private $basePath;

  /** @var string Current relative url */
  private $path;

  /** @var Route[] Currently registered routes */
  private $routes = [];

  /**
   * Constructor
   *
   * @param string $basePath the index url
   */
  public function __construct(string $basePath = '')
  {
    $this->basePath = $basePath;
    $path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $path = substr($path, strlen($basePath));
    $this->path = $path;
  }

  /**
   * Add a route
   *
   * @param string $expression
   * @param string $callback
   * @param array|string $methods
   * @return void
   */
  public function all(string $expression, string $callback, string $methods = null): void
  {
    $this->routes[] = new Route($expression, $callback, $methods);
  }

  /**
   * Alias for all
   *
   * @param string $expression
   * @param string $callback
   * @param null|array $methods
   */
  public function add(string $expression, string $callback, string $methods = null): void
  {
    $this->all($expression, $callback, $methods);
  }

  /**
   * Add a route for GET requests
   *
   * @param string $expression
   * @param string $callback
   */
  public function get(string $expression, string $callback, bool $isPrivate = false): void
  {
    $this->routes[] = new Route($expression, $callback, self::GET, $isPrivate);
  }

  /**
   * Add a route for POST requests
   *
   * @param string $expression
   * @param string $callback
   */
  public function post(string $expression, string $callback, bool $isPrivate = false): void
  {
    $this->routes[] = new Route($expression, $callback, self::POST, $isPrivate);
  }

  /**
   * Add a route for HEAD requests
   *
   * @param string $expression
   * @param string $callback
   */
  public function head(string $expression, string $callback, bool $isPrivate = false): void
  {
    $this->routes[] = new Route($expression, $callback, self::HEAD, $isPrivate);
  }

  /**
   * Add a route for PUT requests
   *
   * @param string $expression
   * @param string $callback
   */
  public function put(string $expression, string $callback, bool $isPrivate = false): void
  {
    $this->routes[] = new Route($expression, $callback, self::PUT, $isPrivate);
  }

  /**
   * Add a route for DELETE requests
   *
   * @param string $expression
   * @param string $callback
   */
  public function delete(string $expression, string $callback, bool $isPrivate = false): void
  {
    $this->routes[] = new Route($expression, $callback, self::DELETE, $isPrivate);
  }

  /**
   * Test all routes until any of them matches
   *
   * @throws RouteNotFoundException if the route doesn't match with any of the registered routes
   */
  public function getByMethod(string $method): RouteComponent
  {
    foreach ($this->routes as $route) {
      if ($route->matches($this->path, $method)) {
        return $route->execute();
      }
    }

    throw new RouteNotFoundException("404 Not Found.");
  }

  /**
   * Get the current url or the url to a path
   *
   * @param string $path
   * @return string
   */
  public function getUrl($path = null): string
  {
    if ($path === null) {
      $path = $this->path;
    }

    return "{$this->basePath}{$path}";
  }

  /**
   * Redirect from one url to another
   *
   * @param string $fromPath
   * @param string $to_path
   * @param int $code
   */
  // public function redirect(string $fromPath, string $toPath, $code = 302)
  // {
  //   $this->all($fromPath, function () use ($toPath, $code) {
  //     http_response_code($code);
  //     header("Location: {$toPath}");
  //   });
  // }

  public function getRoutes(): array
  {
    return $this->routes;
  }
}
