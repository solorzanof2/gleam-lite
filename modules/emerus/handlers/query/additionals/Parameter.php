<?php

namespace emerus\handlers\query\additionals;

class Parameter
{
    private $content;

    /**
     * Creates representation of literal-value, passed as the single parameter
     *
     * @param string|integer|bool|null $content 
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    public function getSql(array &$parameters)
    {
        $number = count($parameters) + 1;

        $parameters[":p".$number] = $this->content;

        return ":p".$number;
    }

    /**
     * accessor, which returns value of parameter
     *
     * @return mixed
     */
    public function getParameters()
    {
        return $this->content;
    }
}

?>