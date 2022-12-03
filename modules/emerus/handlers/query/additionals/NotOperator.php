<?php

namespace emerus\handlers\query\additionals;

use InvalidArgumentException;

class NotOperator extends Operator
{
  private $my_content = null;

  /**
   * designated constructor. takes either MQB_Condition or array consisting of the single MQB_Condition
   *
   * @param mixed $content
   * @throws InvalidArgumentException
   */
  public function __construct($content)
  {
    if (is_array($content)) {
      // compatibility with "legacy" API
      if (count($content) != 1)
        throw new InvalidArgumentException("NotOp takes an array of exactly one Condition or Operator");

      $content = $content[0];
    }

    parent::__construct(array($content));
  }

  public function getSql(array &$parameters)
  {
    $content = $this->getContent();
    return 'NOT (' . $content[0]->getSql($parameters) . ')';
  }
}
