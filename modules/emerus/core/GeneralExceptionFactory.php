<?php

namespace emerus\core;

use Exception;

class GeneralExceptionFactory extends Exception
{

	const ORM_EXCEPTION_CODE = -2;

	public static function genericException( string $message ): Exception
	{
		return new self( $message, self::ORM_EXCEPTION_CODE );
	}

}

?>