<?php

namespace emerus\handlers\query\additionals;

use InvalidArgumentException;
use RangeException;

class FieldImpl implements Field
{
    private $name;
    private $table;
    private $alias;

    /**
     * Designated constructor. Used to create representation of 'tN.field as alias' construction, which can be referred from various parts of query
     *
     * @param string $name 
     * @param integer $table 
     * @param string $alias 
     * @throws RangeException
     */
    public function __construct($name, $table = 0, $alias = null)
    {
        if (!$name)
            throw new RangeException('Name of the field is not specified');

        if (!is_string($name) or !is_numeric($table))
            throw new InvalidArgumentException();

        $this->table = $table;
        $this->name = $name;
        $this->alias = $alias;
    }

    public function getSql(array &$parameters, $full = false)
    {
        if (true === $full or null === $this->alias) {
            $res = '`t'.$this->table."`.`".$this->name.'`';

            if (null !== $this->alias) {
                $res .= ' AS `'.$this->alias.'`';
            }
        } else {
            $res = '`'.$this->alias.'`';
        }

        return $res;
    }

    /**
     * accessor for internal "number of table in query" property
     *
     * @return integer
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * accessor for internal "name of the field" property
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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

        return '`'.$this->alias.'`';
    }
}

?>