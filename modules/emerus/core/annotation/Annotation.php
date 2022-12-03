<?php

namespace emerus\core\annotation;

class Annotation
{

  private $name;

  private $options;

  public function __construct(string $name)
  {
    $this->name = strtolower($name);
    $this->options = [];
  }

  public function __sleep()
  {
    return ['name', 'options'];
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function getOptionValue(string $name): string
  {
    if (!$this->hasOption($name)) {
      throw new AnnotationException("Invalid property name {$name};");
    }
    return $this->options[$name];
  }

  public function getOptions(): array
  {
    return $this->options;
  }

  public function addOption(string $name, string $value): void
  {
    $name = strtolower($name);

    if (!$this->hasOption($name)) {
      $this->options[$name] = [];
    }
    $this->options[$name] = $value;
  }

  public function hasOption(string $name): bool
  {
    return isset($this->options[strtolower($name)]);
  }

  // public function getOptionSingleValue(string $name): string
  // {
  //   return array_shift($this->getOptionValue($name));
  // }
}
