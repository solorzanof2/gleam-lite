<?php

namespace emerus\core;

use InvalidArgumentException;

class InvalidArgumentExceptionFactory extends InvalidArgumentException
{
	const INVALID_ARGUMENT_CODE = -1000;

	/**
	 * This exception is triggered when an entity has no annotations
	 * @param  string $entityName name of the evaluated entity object
	 * @return InvalidArgumentException
	 */
	public static function invalidEntityAnnotation( string $entityName ): InvalidArgumentException
	{
		return new self( "The entity {$entityName} has no valid annotation types", self::INVALID_ARGUMENT_CODE );
	}

	public static function annotationNotFound( string $annotation ): InvalidArgumentException
	{
		return new self( "The entity has no mapped {$annotation} annotation", self::INVALID_ARGUMENT_CODE );
	}

	public static function invalidArgument( string $argument ): InvalidArgumentException
	{
		return new self( "The Argument {$argument} is not valid", self::INVALID_ARGUMENT_CODE );
	}

	public static function getNameNotFoundException(): InvalidArgumentException
	{
		return new self( "No entity name was provided", self::INVALID_ARGUMENT_CODE );
	}
}

?>