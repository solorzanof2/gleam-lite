<?php

namespace gleamlite\io;

class DirectoryException extends \Exception
{

	public function __toString(): string
	{
		$class = __CLASS__;
		return "{$class}: [$this->getCode()]: {$this->getMessage()};";
	}
}
