<?php

namespace emerus\handlers\query\additionals;

class Table
{
    private $table_name = null;
    private $db_name = null;

    /**
     * Designated constructor of table-object.
     *
     * @param string $table_name 
     * @param string $db_name 
     */
    public function __construct($table_name, $db_name = null)
    {
        $this->table_name = $table_name;
        $this->db_name = $db_name;
    }

    /**
     * accessor, which returns sql-friendly (escaped) string-representation of table
     *
     * @return string
     */
    public function __toString()
    {
        $res = '';

        if (null !== $this->db_name) {
            $res .= '`'.$this->db_name.'`.';
        }

        $res .= '`'.$this->table_name.'`';

        return $res;
    }

    /**
     * accessor, which returns raw table-name (without database-name)
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table_name;
    }
}

?>