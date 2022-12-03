<?php

namespace emerus\handlers\query\additionals;

interface Condition
{
  /**
   * used for generation of "prepared" SQL-queries. Supposed to be used recursively, and add parameters to the end of $parameters stack
   *
   * @param array $parameters 
   * @return string
   */
  public function getSql(array &$parameters);
}
