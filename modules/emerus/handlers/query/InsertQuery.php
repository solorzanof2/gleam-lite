<?php

namespace emerus\handlers\query;

use emerus\handlers\query\additionals\Parameter;
use InvalidArgumentException;

/**
 * This class contains logic of "INSERT" queries
 *
 * @package mysql-query-builder
 * @author Alexey Zakhlestin
 */
class InsertQuery extends BasicQuery
{
    private $values;
    private $on_duplicate_update = false;

    /**
     * Constructor of INSERT query. 
     * WARNING: INSERT can be applied only to the single table. You can use array as the first parameter of constructor, but it should be array of 1 element
     *
     * @param mixed $tables 
     * @param bool $on_duplicate_update 
     * @throws InvalidArgumentException
     */
    public function __construct($tables, $on_duplicate_update = false)
    {
        parent::__construct($tables);

        if (count($this->from) != 1)
            throw new InvalidArgumentException('INSERT can be used only on the single table');

        $this->on_duplicate_update = $on_duplicate_update;
    }

    /**
     * magic accessor, which lets setting parts of "SET …" clause with simple "$obj->field = 'value';" statements
     *
     * @param string $key 
     * @param mixed $value 
     * @return void
     */
    public function __set($key, $value)
    {
        $this->values[$key] = new Parameter($value);
        $this->reset();
    }

    /**
     * sets "SET …" clause of query to the new value. Array is supposed to be in the following format: 
     * [field_name => value, field2 => value2, …] 
     *
     * @param array $values 
     * @return void
     */
    public function setValues(array $values)
    {
        $this->values = array();

        foreach ($values as $key => $value) {
            $this->values[$key] = new Parameter($value);
        }

        $this->reset();
    }

    protected function getSql(array &$parameters)
    {
        $sql = $this->getInsert($parameters);
        $sql .= $this->getValues($parameters);

        if (true === $this->on_duplicate_update) {
            $sql .= $this->getUpdate($parameters);
        }

        return $sql;
    }

    private function getInsert(&$parameters)
    {
        $inserts = array();
        foreach (array_keys($this->values) as $key) {
            $inserts[] = '`'.$key.'`';
        }

        $sql = "INSERT INTO ".$this->from[0]->__toString()." (".implode(", ", $inserts).")";

        return $sql;
    }

    private function getValues(&$parameters)
    {
        $values = array();
        foreach ($this->values as $k => $v) {
            $values[] = $v->getSql($parameters);
        }
        $sql = " VALUES (".implode(", ", $values).")";

        return $sql;
    }

    private function getUpdate(&$parameters)
    {
        // if (!isset($this->values['id']))
        //     throw new LogicException("id field is required for ON DUPLICATE KEY UPDATE functionality");

        $values = array();
        foreach ($this->values as $k => $v) {
            if ('id' == $k) { // FIXME: не всегда первичным ключом является id
                $values[] = '`id` = LAST_INSERT_ID(`id`)';
            } else {
                $values[] = '`'.$k.'` = VALUES(`'.$k.'`)';
            }
        }

        $sql = " ON DUPLICATE KEY UPDATE ".implode(", ", $values);

        return $sql;
    }
}
