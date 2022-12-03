<?php

namespace emerus\core\annotation;

class Reflection
{

  private $reflectionClasses = [];

  private $annotatedClasses = [];

  private $annotatedMethods = [];

  private $annotatedProperties = [];

  private $classesAnnotated = [];

  private $reflectionMethods = [];

  private $reflectionProperties = [];

  private $withAnnotations = true;

  private $annotationParser;

  public function __construct(bool $withAnnotations = true)
  {
    $this->withAnnotations = $withAnnotations;
  }

  public function setAnnotationParser(Parser $parser): void
  {
    $this->annotationParser = $parser;
  }

  public function getClassesByAnnotation(string $annotation): array
  {
    if (!$this->withAnnotations) {
      return [];
    }

    if (isset($this->classesAnnotated[$annotation])) {
      return $this->classesAnnotated[$annotation];
    }

    return [];
  }

  public function getMethodAnnotations(string $class, string $method): array
  {
    if (!$this->withAnnotations) {
      return [];
    }

    $key = $class . $method;
    if (isset($this->annotatedMethods[$key])) {
      return $this->annotatedMethods[$key];
    }

    $reflectionMethod = $this->getMethod($class, $method);
    $annotations = $this->annotationParser->parse($reflectionMethod->getDocComment());

    return $annotations;
  }

  public function getPropertyAnnotations(string $class, string $property): Collection
  {
    if (!$this->withAnnotations) {
      return [];
    }

    $key = $class . $property;
    if (isset($this->annotatedProperties[$key])) {
      return $this->annotatedProperties[$key];
    }

    $reflectionProperty = $this->getProperty($class, $property);
    $annotations = $this->annotationParser->parse($reflectionProperty->getDocComment());

    return $annotations;
  }

  private function populateClassesPerAnnotations($class, Collection $annotations): void
  {
    foreach ($annotations->getAll() as $name => $annotation) {

      if (!isset($this->classesAnnotated[$name])) {
        $this->classesAnnotated[$name] = [];
      }
      $this->classesAnnotated[$name][$class] = $class;
    }
  }

  public function getClassAnnotations(string $class): Collection
  {
    if (!$this->withAnnotations) {
      return [];
    }
    if (isset($this->annotatedClasses[$class])) {
      return $this->annotatedClasses[$class];
    }

    $this->annotatedClasses[$class] = [];
    $reflectionClass = $this->getClass($class);
    $annotations = $this->annotationParser->parse($reflectionClass->getDocComment());

    $this->populateClassesPerAnnotations($class, $annotations);

    $this->annotatedClasses[$class] = $annotations;

    return $annotations;
  }

  public function getClass(string $class): \ReflectionClass
  {
    if (isset($this->reflectionClasses[$class])) {
      return $this->reflectionClasses[$class];
    }

    $this->reflectionClasses[$class] = new \ReflectionClass($class);
    return $this->reflectionClasses[$class];
  }

  public function getMethod(string $class, string $method): \ReflectionMethod
  {
    if (isset($this->reflectionMethods[$class][$method])) {
      return $this->reflectionMethods[$class][$method];
    }

    if (!isset($this->reflectionMethods[$class])) {
      $this->reflectionMethods[$class] = [];
    }

    $this->reflectionMethods[$class][$method] = new \ReflectionMethod($class, $method);
    return $this->reflectionMethods[$class][$method];
  }

  public function getProperty(string $class, string $property): \ReflectionProperty
  {
    if (isset($this->reflectionProperties[$class][$property])) {
      return $this->reflectionProperties[$class][$property];
    }

    if (!isset($this->reflectionProperties[$class])) {
      $this->reflectionProperties[$class] = [];
    }

    $this->reflectionProperties[$class][$property] = new \ReflectionProperty($class, $property);
    return $this->reflectionProperties[$class][$property];
  }

  public function getClassAncestors(string $class): array
  {
    $response = [];

    $reflectionClass = $this->getClass($class);
    while ($reflectionClass = $reflectionClass->getParentClass()) {

      $response[] = $reflectionClass->getName();
    }

    return $response;
  }

  public function getClassAncestorsAndInterfaces(string $class): array
  {
    $response = [];
    $response = $this->getClassAncestors($class);
    return array_merge($response, $this->getClass($class)->getInterfaceNames());
  }

  public function getProperties(string $class): array
  {
    if (!empty($this->reflectionProperties[$class])) {
      return $this->reflectionProperties[$class];
    }

    if (!isset($this->reflectionProperties[$class])) {
      $this->reflectionProperties[$class] = [];
    }

    $ancestorsCollection = $this->getClassAncestors($class);
    if (!empty($ancestorsCollection)) {
      foreach ($ancestorsCollection as $ancestors) {
        $ancestorsReflection = $this->getClass($ancestors);
        foreach ($ancestorsReflection->getProperties() as $property) {
          $this->reflectionProperties[$class][$property->getName()] = $property;
        }
      }
    }

    foreach ($this->getClass($class)->getProperties() as $property) {
      $this->reflectionProperties[$class][$property->getName()] = $property;
    }

    return $this->reflectionProperties[$class];
  }
}
