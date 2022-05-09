<?php

namespace emerus\core\annotation;

class Collection
{
	
	private $annotations = [];

	public function __sleep()
	{
		return [ 'annotations' ];
	}

	public function contains( string $name ): bool
	{
		$name = strtolower( $name );
		return isset( $this->annotations[ $name ] );
	}

	public function getAll(): array
	{
		return $this->annotations;
	}

	public function getSingleAnnotation( string $name ): Annotation
	{
		$annotations = $this->getAnnotations( $name );
		return array_shift( $annotations );
	}

	public function getAnnotations( string $name ): array
	{
		$name = strtolower( $name );

		if ( $this->contains( $name ) ) {
			return $this->annotations[ $name ];
		}
		throw new AnnotationException( "Invalid annotation name {$name};" );
	}

	public function add( Annotation $annotation ): void
	{
		$name = strtolower( $annotation->getName() );

		if ( ! $this->contains( $name ) ) {
			$this->annotations[ $name ] = [];
		}
		$this->annotations[ $name ][] = $annotation;
	}

	public function count(): int
	{
		return count( $this->annotations );
	}

	public function hasAnnotations(): bool
	{
		return ( $this->count() > 0 );
	}
}

?>