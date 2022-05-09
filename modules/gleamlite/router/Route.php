<?php

namespace gleamlite\router;

use Exception;
use gleamlite\utils\StringUtils;
use stdClass;

/**
 * A class representing a registered route. Each route is composed of a regular expression,
 * an array of allowed methods, and a callback function to execute if it matches.
 *
 * @package Router
 */
class Route
{

  /** @var string The callback function */
  private $callback;

  /** @var Array contains allowed methods for this route */
  private $methods = ['GET', 'POST', 'HEAD', 'PUT', 'DELETE'];

  private $route = '';

  private $pathParameters;

  private $isPrivate = false;

  /**
   * Constructor
   *
   * @param string $expression regular expresion to test against
   * @param function $callback function executed if route matches
   * @param string|array $methods methods allowed
   */
  public function __construct(string $expression, $callback, $methods = null, bool $isPrivate = false)
  {
    // Allow an optional trailing backslash
    $this->route = $expression;
    $this->callback = $callback;

    if ($methods !== null) {
      $this->methods = is_array($methods) ? $methods : [$methods];
    }

    $this->pathParameters = new stdClass();
    $this->isPrivate = $isPrivate;
  }

  /**
   * See if route matches with path
   *
   * @param string $path
   * @return boolean
   */
  public function matches(string $path, string $method): bool
  {
    $pathSections = array_values(array_filter(explode('/', $path)));
    $routeSections = array_values(array_filter(explode('/', $this->route)));

    if (count($pathSections) != count($routeSections)) {
      return false;
    }

    $length = count($pathSections);

    $routeValue = '';
    $pathValue = '';
    for ($index = 0; $index < $length; $index++) {
      $routeValue = $routeSections[$index];
      $pathValue = $pathSections[$index];

      if ($routeValue === $pathValue) {
        continue;
      }

      if (!StringUtils::startsWith($routeValue, ':')) {
        return false;
      }

      $paramName = str_replace(':', '', $routeValue);
      $this->pathParameters->$paramName = $pathValue;
    }

    if (!in_array($method, $this->methods)) {
      throw new MethodNotAllowedException();
    }

    return true;
  }

  /**
   * Execute the callback.
   * The matches function needs to be called before this and return true.
   * We don't take the first match since it's the whole path
   */
  public function execute(): RouteComponent
  {
    $component = new RouteComponent();
    $component->pathParameters = $this->pathParameters;

    $filename = __ROOT__.$this->callback.__PHP;
    if (!file_exists($filename)) {
      throw new Exception("File not found for {$this->callback}");
    }

    $component->instance = require_once($filename);
    $component->isPrivate = $this->isPrivate;

    return $component;
  }
}
