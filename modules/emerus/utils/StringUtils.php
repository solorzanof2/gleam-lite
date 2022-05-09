<?php

namespace emerus\utils;

class StringUtils
{

    const EMPTY = '';

    const COMMA_DELIMITER = ', ';

	public static function isNull( string $text = null ): bool
	{
		$text = trim( $text );
		return ( is_null( $text ) || empty( $text ) );
    }
    
    public static function isNotNull( string $text = null ): bool
    {
        return ( ! self::isNull( $text ) );
    }

    public static function hasLength( string $text = null ): bool
	{
		return ( ! is_null( $text ) && strlen( $text ) > 0 );
	}

	public static function isEmpty( string $text = null ): bool
	{
		return ( is_null( $text ) || empty( $text ) );
    }
    
    public static function isNotEmpty( string $text = null ): bool
    {
        return ( ! self::isEmpty( $text ) );
    }

	public static function contains( string $haystack, string $needle ): bool
	{
		return ( strpos( $haystack, $needle ) > 0 );
    }
    
    public static function startsWith( string $source, string $needle ): bool
    {
        return ( substr( $source, 0, strlen( $needle ) ) === $needle );
    }

    public static function endsWith( string $source, string $needle ): bool
    {
        $len = strlen( $needle ); 
        if ( $len == 0 ) {
            return true;
        }
        return ( substr( $source, -$len ) === $needle );
    }

    public static function format( string $format, $values ): string
    {
        if ( ObjectUtils::isArray( $values ) ) {
            return vsprintf( $format, $values );
        }
        return sprintf( $format, $values );
    }

	public static function equals( string $firstValue, string $secondValue ): bool
	{
		return ( strcasecmp( $firstValue, $secondValue) == 0 );
    }
    
    public static function notEquals( string $firstValue, string $secondValue ): bool
    {
        return ( ! self::equals( $firstValue, $secondValue ) );
    }
    
    public static function collectionToCommaDelimitedString( array $collection = [] ): string
    {
        if ( ObjectUtils::isArray( $collection ) && ObjectUtils::isNotEmpty( $collection ) ) {
            return implode( self::COMMA_DELIMITER, $collection );
        }
        return self::EMPTY;
    }
    
}

?>