<?php

namespace emerus\handlers\query\additionals;

use emerus\handlers\query\SelectQuery;
use InvalidArgumentException;
use RangeException;

class ConditionImpl implements Condition
{
  private $content = array();
  private $validConditions = array("=", "<>", "<", ">", ">=", "<=", "like", "is null", "find_in_set", "and", "or", "xor", "in");
  private $validSingulars = array("is null");

  /**
   * Designated constructor.
   * First parameter should be one of the allowed comparator-strings
   * Second parameter should be some MQB_Field-compliant object
   * Third parameter is the value, which is compared against second-parameter. It should be either scalar-value, or another MQB_Field
   *
   * @param string $comparison 
   * @param MQB_Field $left 
   * @param mixed $right 
   * @throws RangeException, InvalidArgumentException
   */
  public function __construct($comparison, Field $left, $right = null)
  {
    $comparison = strtolower($comparison);

    if (!in_array($comparison, $this->validConditions))
      throw new RangeException('invalid comparator-function');

    if ($comparison == 'in') {
      if (is_array($right)) {
        // IN (1,2,3,4)
        foreach ($right as $value) {
          if (!is_numeric($value)) {
            throw new InvalidArgumentException('Right-op has to be array consisting of NUMERIC VALUES, if comparison is "in"');
          }
        }
      } else {
        // IN (SELECT …)
        if (!is_object($right) or !($right instanceof SelectQuery)) {
          throw new InvalidArgumentException('Right-op has to be object of class SelectQuery, if comparison is "in"');
        }

        if ($right->countSelects() != 1) {
          throw new InvalidArgumentException('Right-op has to be query with one field, if comparison is "in"');
        }
      }
    } elseif (!in_array($comparison, $this->validSingulars)) {
      if (is_scalar($right)) {
        $right = new Parameter($right);
      } elseif (null !== $right and !($right instanceof Parameter) and !($right instanceof Field)) {
        throw new InvalidArgumentException('Right-op has to be Parameter or MQB_Field. Got ' . get_class($right) . ' instead');
      }
    }

    $this->content = array($comparison, $left, $right);
  }

  public function getSql(array &$parameters)
  {
    $comparison = $this->content[0];
    $leftpart = $this->content[1]->getSql($parameters);

    if ($comparison == 'is null' or ($comparison == '=' and null === $this->content[2])) {
      return $leftpart . " IS NULL";
    } elseif ($comparison == '<>' and null === $this->content[2]) {
      return $leftpart . " IS NOT NULL";
    } elseif ($comparison == 'in') {
      $right = $this->content[2];

      if (is_array($right)) {
        return $leftpart . " IN (" . implode(', ', $right) . ")";
      } elseif ($right instanceof SelectQuery) {
        return $leftpart . ' IN (' . $right->sql($parameters) . ')';
      }
    } else {
      $rightpart = $this->content[2]->getSql($parameters);

      if ($comparison == "find_in_set")
        return $comparison . "(" . $rightpart . "," . $leftpart . ")";

      return $leftpart . " " . $comparison . " " . $rightpart;
    }
  }

  /**
   * accessor which returns comparator
   *
   * @return string
   */
  public function getComparison()
  {
    return $this->content[0];
  }

  /**
   * accessor which returns left-parameter of comparison
   *
   * @return MQB_Field
   */
  public function getLeft()
  {
    return $this->content[1];
  }

  /**
   * accessor which returns right-parameter of comparison
   *
   * @return mixed
   */
  public function getRight()
  {
    return $this->content[2];
  }
}
