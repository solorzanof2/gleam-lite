<?php

namespace emerus\handlers\query\additionals;

class AllFields
{
    private $table;

    /**
     * Designated constructor. Takes "number of table in query" as the parameter
     *
     * @param integer $table 
     */
    public function __construct($table = 0)
    {
        $this->table = $table;
    }

    public function getSql(array &$parameters)
    {
        return '`t'.$this->table."`.*";
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
}

?>