<?php

namespace emerus\handlers\query;

use emerus\handlers\query\additionals\Condition;
use emerus\handlers\query\additionals\Field;
use emerus\handlers\query\additionals\Table;
use InvalidArgumentException;
use LogicException;

/**
 * This class contains all the common logic shared by other query-classes
 *
 * @package mysql-query-builder
 * @author Alexey Zakhlestin
 */
abstract class BasicQuery
{
  private $conditions = null;
  private $parameters;
  private $sql = null;
  private $orderby;
  private $orderdirection;

  /**
   * contains QBTable objects related to current query
   *
   * @var array
   */
  protected $from = array();

  /**
   * Constructor provides common logic (all queries are done on tables), but does not direct instantiation of BasicQuery
   *
   * @param mixed $tables 
   */
  protected function __construct($tables)
  {
    $this->setTables($tables);
  }

  /**
   * Sets which table(s) the query will be applied to
   *
   * @param mixed $tables Can be either string, QBTable instance or array of strings/QBTables
   * @return void
   * @throws InvalidArgumentException, LogicException
   */
  public function setTables($tables)
  {
    if (is_string($tables) or $tables instanceof Table)
      $tables = array($tables);

    if (!is_array($tables))
      throw new InvalidArgumentException('table(s) should be specified as a string, or array of strings');

    if (count($tables) == 0)
      throw new InvalidArgumentException('there were no tables, specified');

    $this->from = array();
    foreach ($tables as $table) {
      if (is_string($table)) {
        $this->from[] = new Table($table);
      } elseif ($table instanceof Table) {
        $this->from[] = $table;
      } else {
        throw new LogicException("Invalid object is provided as a table");
      }
    }

    $this->reset();
  }

  /**
   * Sets where-condition, which will be applied to query
   * The most typical objects to use as parameters are Condition and AndOp
   *
   * @param Condition $conditions 
   * @return void
   * @author Jimi Dini
   */
  public function setWhere(Condition $conditions = null)
  {
    if (null === $conditions) {
      $this->conditions = null;
    } elseif ($conditions instanceof Condition) {
      $this->conditions = clone $conditions;
    }

    $this->reset();
  }

  /**
   * setup "ORDER BY" clause of Query.
   * $orderlist is supposed to be array of objects implementing MQB_Field (most-probably, Field objects).
   * $orderdirectionlist is supposed to be array of booleans, where TRUE means DESC and FALSE means ASC.
   * if number of elements of $orderdirectionlist is smaller that number of elements of $orderlist array, then ASC is applied to the tail-objects
   *
   * @param array $orderlist 
   * @param array $orderdirectionlist 
   * @return void
   * @throws InvalidArgumentException
   */
  public function setOrderby(array $orderlist, array $orderdirectionlist = array())
  {
    foreach ($orderlist as $field)
      if (!($field instanceof Field))
        throw new InvalidArgumentException('Only object implementing MQB_Field can be used in setOrderBy');

    $this->orderby = $orderlist;
    $this->orderdirection = $orderdirectionlist;

    $this->reset();
  }

  /**
   * accessor, which returns array of table-names used in Query.
   *
   * @return array
   */
  public function showTables()
  {
    $res = array();
    foreach ($this->from as $table) {
      $res[] = $table->getTable();
    }

    return $res;
  }

  /**
   * accessor, which returns current-querys condition
   *
   * @return MQB_Condition
   */
  public function showConditions()
  {
    return $this->conditions;
  }

  // internal stuff

  /**
   * This method should be overridden by descendents
   *
   * @param array $parameters 
   * @return void
   * @throws LogicException
   */
  protected function getSql(array &$parameters)
  {
    throw new LogicException();
  }

  /**
   * Returns "FROM" clause which can be used in various queries
   *
   * @param array $parameters 
   * @return void
   */
  protected function getFrom(array &$parameters)
  {
    $froms = array();
    for ($i = 0; $i < count($this->from); $i++) {
      $froms[] = $this->from[$i]->__toString() . ' AS `t' . $i . '`';
    }

    $sql = ' FROM ' . implode(", ", $froms);

    return $sql;
  }

  /**
   * Returns "WHERE" clause which can be used in various queries
   *
   * @param array $parameters 
   * @return void
   */
  protected function getWhere(array &$parameters)
  {
    if (null === $this->conditions)
      return "";

    $sql = $this->conditions->getSql($parameters);

    if (empty($sql))
      return "";

    return " WHERE " . $sql;
  }

  /**
   * Returns "ORDER BY" clause which can be used in various queries
   *
   * @param array $parameters 
   * @return void
   */
  protected function getOrderby(array &$parameters)
  {
    if (!$this->orderby || !is_array($this->orderby))
      return "";

    foreach ($this->orderby as $i => $field) {
      if (array_key_exists($i, $this->orderdirection) && $this->orderdirection[$i])
        $direction = ' DESC';
      else
        $direction = ' ASC';

      if (null !== $alias = $field->getAlias())
        $sqls[] = $alias . $direction;
      else
        $sqls[] = $field->getSql($parameters) . $direction;
    }

    return " ORDER BY " . implode(", ", $sqls);
  }

  /**
   * resets internal cache-structures, which are used for generation of sql-string and parameters-array
   *
   * @return void
   */
  protected function reset()
  {
    $this->parameters = array();
    $this->sql = null;
  }

  /**
   * rebuilds (if needed) and returns SQL-string, which can be used for "prepared" query
   *
   * @return string
   */
  public function sql(array &$parameters = null)
  {
    if (null === $this->sql) {
      if (is_array($parameters)) {
        $this->parameters = &$parameters;
      } else {
        $this->parameters = array();
      }
      $this->sql = $this->getSql($this->parameters);
    }

    return $this->sql;
  }

  /**
   * returns array of parameters, which can be used with SQL-string from ->sql() method.
   * WARNING: this method does not rebuild SQL-Query. Be sure to call ->sql() before using it.
   *
   * @return array
   * @author Jimi Dini
   */
  public function parameters()
  {
    if (null === $this->sql) {
      throw new LogicException('->sql() method should be called, before calling ->parameters() method');
    }

    return $this->parameters;
  }
}
