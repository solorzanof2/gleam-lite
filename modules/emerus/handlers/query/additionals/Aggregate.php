<?php

namespace emerus\handlers\query\additionals;

use InvalidArgumentException;
use RangeException;

class Aggregate implements Field
{
  private $aggregate;
  private $distinct;
  private $name;
  private $table;
  private $alias;
  private $validAggregates = array("sum", "count", "min", "max", "avg");
  private $field = null;

  /**
   * Creates representation of SQLs aggregate-function.
   * $field can be null, if $aggregate is 'count' — this would result in COUNT(*) query. 
   * If $distinct is set to true, then something like the following will appear: COUNT(DISTINCT `foo`) AS `alias`
   *
   * @param string $aggregate 
   * @param string $field 
   * @param bool $distinct 
   * @param string $alias 
   * @throws RangeException, InvalidArgumentException
   */
  public function __construct($aggregate, $field = null, $distinct = false, $alias = null)
  {
    $aggregate = strtolower($aggregate);

    if (!in_array($aggregate, $this->validAggregates))
      throw new RangeException('Invalid aggregate function: ' . $aggregate);

    if (($field instanceof Field) or (null === $field and $aggregate == 'count')) {
      $this->aggregate = $aggregate;
      $this->distinct = ($distinct === true);
      $this->alias = $alias;
      $this->field = $field;
    } else {
      throw new InvalidArgumentException('field should be MQB_Field');
    }
  }

  public function getSql(array &$parameters, $full = false)
  {
    if (true === $full or null === $this->alias) {
      if (null === $this->field) {
        $field_sql = '*';
      } else {
        $field_sql = $this->field->getSql($parameters);
      }

      if ($this->distinct) {
        $field_sql = 'DISTINCT ' . $field_sql;
      }

      $field_sql = strtoupper($this->aggregate) . '(' . $field_sql . ')';

      if (null !== $this->alias) {
        $field_sql .= ' AS `' . $this->alias . '`';
      }
    } else {
      $field_sql = '`' . $this->alias . '`';
    }

    return $field_sql;
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
}
