<?php

namespace emerus\binds;

use emerus\utils\StringUtils;

class Column
{
    
    const CLAZZ = 'class';

    const COLLECTION = 'collection';
  
    const T_STRING = 'string';
  
    const T_INT = 'int';
  
    const T_DOUBLE = 'double';
  
    const DATETIME = 'datetime';

    const EAGER = "EAGER";

	const LAZY = "LAZY";

	/**
	 * ### Property Name
	 * 
	 * @var string
	 */
	private $name = "";

	/**
	 * ### Column Name
	 * 
	 * @var string
	 */
	private $column = "";

	/**
	 * ### Value Property
	 * Determine de value for the column
	 * defined by property {$type}
	 * 
	 * @var mixed
	 */
	private $value = null;

	/**
	 * ### Is Primary Key Property
	 * Determine if the current column
	 * is the primary key for the
	 * table
	 *
	 * @var boolean
	 */
	private $primaryKey = false;

	/**
	 * ### Object Property
	 * Determine if the current
	 * column is for store a
	 * relation object or 
	 * collection
	 *
	 * @var boolean
	 */
	private $object = false;
	
	/**
	 * ### Entity Property
	 * Determine the entity
	 * name for the current
	 * column type
	 *
	 * @var string
	 */
	private $target = '';

	/**
	 * ### Type Property
	 * Determine the type
	 * of the current column
	 *
	 * @var string
	 */
	private $type = 'string';

	/**
	 * ### Default Values Property
	 * Determine the default values
	 * for this column. This will be
	 * usefull with @Enumerated fields
	 * 
	 * @var array
	 */
	private $defaultValues = [];

	/**
	 * ### Join Column Property
	 * Defines the table field
	 * as relation target
	 *
	 * @var string
	 */
	private $joinColumn = '';

	/**
	 * ### Fetch Property
	 * Defines the fetch type
	 * for the current column
	 * EAGER is defatul.
	 *
	 * @var string
	 */
	private $fetch = self::EAGER;

	/**
	 * ### Mapped By Property
	 * Defines if the current
	 * relation is mapped by
	 * foreing entity property
	 *
	 * @var string
	 */
	private $mappedBy = '';

	/**
	 * ### Owner Property
	 * Determine if the current
	 * column is owner of the
	 * mapped relation
	 *
	 * @var boolean
	 */
	private $owner = false;

	/**
	 * ### Inverse Join Property
	 * Contains the name of the field
	 * inverseJoin for foreing table
	 * 
	 * @var string
	 */
	private $inverseJoin = '';

	/**
	 * ### Join Table Property
	 * Determine the name of the table
	 * that join between many to many
	 * relationship
	 *
	 * @var string
	 */
	private $joinTable = '';

	/**
	 * ### Is Many To Many Property
	 * Determine if the current column is a
	 * many to many relationship
	 *
	 * @var boolean
	 */
	private $isManyToMany = false;

	/**
	 * ### Entity Property
	 * Defines the name of the
	 * entity for wich this
	 * column belong
	 *
	 * @var string
	 */
	private $entity = '';

	/**
	 * ### Updatable Property
	 * Defines if this column/property
	 * can be updated
	 * 
	 * @var bool
	 */
	private $updatable = true;

	/**
	 * ### Nullable Property
	 * Defines if this column/property
	 * can be null
	 *
	 * @var boolean
	 */
	private $nullable = true;

	/**
	 * ### Length Property
	 * Defines the longitude varchar
	 * base on
	 *
	 * @var integer
	 */
	private $length = 0;

	/**
	 * ### Orphan Removal Property
	 * Defines that the orphan childs
	 * must to be removed wen his parent
	 * has been deleted
	 *
	 * @var boolean
	 */
	private $orphanRemoval = true;

	/**
	 * ### Is UUID Property
	 * Defines if the current property
	 * is container of auto-generated
	 * uuid
	 *
	 * @var boolean
	 */
	private $isUuid = false;

	public function __construct( string $entity = null )
	{
		if ( ! StringUtils::isNull( $entity ) )
		{
			$this->entity = $entity;
		}
    }
    
	public static function toArray(): array
	{
		return [
			self::EAGER,
			self::LAZY
		];
	}

	public function setName( string $name ): void
	{
		$this->name = $name;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setColumn( string $column ): void
	{
		$this->column = $column;
	}

	public function getColumn(): string
	{
		return $this->column;
	}

	public function setValue( $value ): void
	{
		$this->value = $value;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function setPrimaryKey( bool $primaryKey ): void
	{
		$this->primaryKey = $primaryKey;
	}

	public function getPrimaryKey(): bool
	{
		return $this->primaryKey;
	}

	public function setObject( bool $object ): void
	{
		$this->object = $object;
	}

	public function getObject(): bool
	{
		return $this->object;
	}

	public function setType( string $type ): void
	{
		$this->type = $type;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function setDefaultValues( array $values ): void
	{
		$this->defaultValues = $values;
	}

	public function getDefaultValues(): array
	{
		return $this->defaultValues;
	}

	public function setTarget( string $target ): void
	{
		$this->target = $target;
	}

	public function getTarget(): string
	{
		return $this->target;
	}

	public function setJoinColumn( string $joinColumn ): void
	{
		$this->joinColumn = $joinColumn;
	}

	public function getJoinColumn(): string
	{
		return $this->joinColumn;
	}

	public function setMappedBy( string $mappedBy ): void
	{
		$this->mappedBy = $mappedBy;
	}

	public function getMappedBy(): string
	{
		return $this->mappedBy;
	}

	public function setFetch( string $fetch ): void
	{
		$this->fetch = $fetch;
	}

	public function getFetch(): string
	{
		return $this->fetch;
	}

	public function setOwner(): void
	{
		$this->owner = TRUE;
	}

	public function getOwner(): bool
	{
		return $this->owner;
	}

	public function getInverseJoin(): string
	{
		return $this->inverseJoin;
	}

	public function setInverseJoin( string $inverseJoin ): void
	{
		$this->inverseJoin = $inverseJoin;
	}

	public function setJoinTable( string $joinTable ): void
	{
		$this->joinTable = $joinTable;
		$this->isManyToMany = TRUE;
	}

	public function getJoinTable(): string
	{
		return $this->joinTable;
	}

	public function getIsManyToMany(): bool
	{
		return $this->isManyToMany;
	}

	public function getEntity(): string
	{
		return $this->entity;
	}

	public function setEntity( string $entity ): void
	{
		$this->entity = $entity;
	}

	public function updatable(): bool
	{
		return $this->updatable;
	}

	public function setUpdatable( bool $updatable ): void
	{
		$this->updatable = $updatable;
	}

	public function nullable(): bool
	{
		return $this->nullable;
	}

	public function setNullable( bool $nullable ): void
	{
		$this->nullable = $nullable;
	}

	public function length(): int
	{
		return $this->length;
	}

	public function setLength( int $length ): void
	{
		$this->length = $length;
	}

	public function getOrphanRemoval(): bool
	{
		return $this->orphanRemoval;
	}

	public function setOrphanRemoval(): void
	{
		$this->orphanRemoval = TRUE;
	}

	public function getIsUUID(): bool
	{
		return $this->isUuid;
	}

	public function setUUID(): void
	{
		$this->isUuid = TRUE;
	}

}

?>
