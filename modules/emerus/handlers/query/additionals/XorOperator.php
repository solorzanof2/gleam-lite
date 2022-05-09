<?php

namespace emerus\handlers\query\additionals;

class XorOperator extends Operator
{
    /**
     * Designated constructor.
     * Takes either single parameter — array of MQB_Conditions or several parameters-MQB_Conditions
     *
     * @param string|array $content,...
     */
    public function __construct($content)
    {
        if (func_num_args() > 1)
            parent::__construct(func_get_args());
        else
            parent::__construct($content);

        $this->startSql = "(";
        $this->implodeSql = " XOR ";
        $this->endSql = ")";
    }

    public function getSql(array &$parameters)
    {
        $content = $this->getContent();

        // shortcut
        if (count($content) == 1)
            return $content[0]->getSql($parameters);

        return parent::getSql($parameters);
    }
}

?>