<?php

namespace emerus\core\annotation;

class AnnotationException extends \Exception
{

	public function __toString(): string
	{
		$class = __CLASS__;
		return "{$class}: [{$this->getCode()}]: {$this->getMessage()};";
	}

}

?>