<?php

namespace emerus\core\annotation;

class Parser
{
	protected static $ignoredAnnotations = [
		'access', 'author', 'copyright', 'deprecated',
		'example', 'ignore', 'internal', 'link', 'see',
		'since', 'tutorial', 'version', 'package',
		'subpackage', 'name', 'global', 'param',
		'return', 'staticvar', 'category', 'staticVar',
		'static', 'throws', 'inheritdoc',
		'inheritDoc', 'license', 'todo', 'deprecated',
		'deprec', 'author', 'property' , 'method' ,
		'abstract', 'exception', 'magic' , 'api' ,
		'final', 'filesource', 'throw' , 'uses' ,
		'usedby', 'private' , 'Annotation' , 'override' ,
		'codeCoverageIgnoreStart' , 'codeCoverageIgnoreEnd' ,
		'Attribute' , 'Attributes' , 'Index', 'InheritanceType', 'DiscriminatorMap',
		'DiscriminatorColumn', 'GeneratedValue', 'HasLifeCycleCallbacks',
		'Target' , 'SuppressWarnings'
	];

	private function parseOptions( $options )
	{
		$total = preg_match_all(
			'/([^=,]*)=[\s]*([\s]*"[^"]+"|\{[^\{\}]+\}|[^,"]*[\s]*)/', $options, $matches
		);
		$options = [];
		if ( $total > 0 ) {

			for ( $i = 0; $i < $total; $i++ ) {

				$key = trim( $matches[ 1 ][ $i ] );
				$value = str_replace( '"', '', trim( $matches[ 2 ][ $i ] ) );
				$options[ $key ] = [];

				if ( strpos( $value, '{' ) === 0) {
					$value = substr( $value, 1, -1 );
					$value = explode( ',', $value );

					foreach ( $value as $k => $v ) {

						$options[ $key ][] = trim( $v );
					}
				} else {
					$options[ $key ][] = $value;
				}
			}
		}
		return $options;
	}

	public function parse( string $text ): Collection
	{
		$response = new Collection();
		if ( preg_match_all( '/@([^@\n\r\t]*)/', $text, $globalMatches ) > 0 ) {

			foreach ( $globalMatches[ 1 ] as $annotationText ) {

				preg_match( '/([a-zA-Z0-9]+)/', $annotationText, $localMatches );

				if ( in_array( $localMatches[ 1 ], self::$ignoredAnnotations ) ) {
					continue;
				}

				$annotation = new Annotation( $localMatches[ 1 ] );
				$optionsStart = strpos( $annotationText, '(' );

				if ( $optionsStart !== false ) {

					$optionsEnd = strrpos( $annotationText, ')' );
					$optionsLength = $optionsEnd - $optionsStart - 1;
					$options = trim( substr( $annotationText, $optionsStart + 1, $optionsLength ) );

					foreach ( $this->parseOptions( $options ) as $key => $values) {

						foreach ( $values as $value ) {
							$annotation->addOption( $key, $value );
						}
					}
				}
				$response->add( $annotation );
			}
		}
		return $response;
	}
}

?>