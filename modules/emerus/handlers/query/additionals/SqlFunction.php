<?php

namespace emerus\handlers\query\additionals;

use InvalidArgumentException;

class SqlFunction implements Field
{
  private $name;
  private $values;
  private $alias;

  private $validNames = array('substring', 'year', 'month', 'day', 'date', 'length');

  /**
   * Designated constructor, which generates representation of '$name($value1, $value2, ... $valueN) as $alias' sql-construct
   * $values can either be literal, MQB_Field or array of literals and MQB_Fields
   *
   * @param string $name 
   * @param mixed $values 
   * @param string $alias 
   * @throws InvalidArgumentException
   */
  public function __construct($name, $values, $alias = null)
  {
    if (!is_string($name) or !in_array($name, $this->validNames))
      throw new InvalidArgumentException('Invalid sql-function: ' . $name);

    if (!is_array($values))
      $values = array($values);

    foreach ($values as $v) {
      if (is_object($v) and !($v instanceof Field))
        throw new InvalidArgumentException("Something wrong passed as a parameter");
    }

    $this->name = $name;
    $this->values = $values;
    $this->alias = $alias;
  }

  public function getSql(array &$parameters)
  {
    $result = strtoupper($this->name) . "(";

    $first = true;
    foreach ($this->values as $v) {
      if ($first) {
        $first = false;
      } else {
        $result .= ', ';
      }

      if (is_object($v)) {
        $result .= $v->getSql($parameters);
      } else {
        $result .= $v;
      }
    }

    $result .= ')';

    if (null !== $this->alias) {
      $result .= ' AS ' . $this->getAlias();
    }

    return $result;
  }

  /**
   * accessor for internal "number of alias" property. returns NULL, if alias is not set
   *
   * @return string|null
   */
  public function getAlias()
  {
    if (null === $this->alias)
      return null;

    return '`' . $this->alias . '`';
  }

  /**
   * accessor for internal "name of the function" property
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
}
