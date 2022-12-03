<?php

namespace emerus\core;

use emerus\binds\annotation\EntityMapping;
use emerus\binds\Column;
use emerus\core\annotation\Collection;
use emerus\utils\StringUtils;
use ReflectionProperty;

class AnnotatedElementUtils
{

  const CLAZZ = 'class';

  const COLLECTION = 'collection';

  const T_STRING = 'string';

  const T_INT = 'int';

  const T_DOUBLE = 'double';

  const DATETIME = 'datetime';

  const EAGER = "EAGER";

  const LAZY = "LAZY";

  const ENTITY = 'entity';

  const TABLE = 'table';

  const ID = 'id';

  const UUID = 'uuid';

  const COLUMN = 'column';

  const FETCH = 'fetch';

  const ENUMERATED = 'enumerated';

  const JOIN = 'joincolumn';

  const JOIN_TABLE = 'jointable';

  const RELATION = 'relation';

  const NAME = 'name';

  const TYPE = 'type';

  const TARGET = 'target';

  const MAPPED_BY = 'mappedby';

  const INVERSE_JOIN = 'inversejoin';

  const JOIN_COLUMN = 'joincolumn';

  const NULLABLE = 'nullable';

  const UPDATABLE = 'updatable';

  const LENGTH = 'length';

  const ORPHAN_REMOVAL = 'orphanremoval';

  public static function getEntityAnnotations(string $entityname, Collection $collection): EntityMapping
  {
    if (!$collection->hasAnnotations() || !$collection->contains(self::ENTITY)) {
      throw GeneralExceptionFactory::genericException("No entity annotation is setted in {$entityname}");
    }

    if (!$collection->contains(self::TABLE)) {
      throw GeneralExceptionFactory::genericException("No table name annotation is setted in {$entityname}");
    }

    return (new EntityMapping())
      ->setValid(true)
      ->setTablename($collection->getSingleAnnotation(self::TABLE)->getOptionValue(self::NAME));
  }

  public static function getPropertiesAnnotations(string $entityname, ReflectionProperty $property, Collection $collection): ?Column
  {
    if (!$collection->hasAnnotations()) {
      return null;
    }

    $propertyName = $property->getName();

    $column = new Column($entityname);
    $column->setName($propertyName);

    $column->setPrimaryKey($collection->contains(self::ID));

    if ($collection->contains(self::UUID)) {
      $column->setUUID();
    }

    $columnInfo = $collection->getSingleAnnotation(self::COLUMN);
    if ($columnInfo->hasOption(self::NAME)) {
      $column->setColumn($columnInfo->getOptionValue(self::NAME));
    }

    if ($columnInfo->hasOption(self::TYPE)) {
      $column->setType($columnInfo->getOptionValue(self::TYPE));
    }

    if ($columnInfo->hasOption(self::NULLABLE)) {
      $column->setNullable($columnInfo->getOptionValue(self::NULLABLE));
    }

    if ($columnInfo->hasOption(self::UPDATABLE)) {
      $column->setUpdatable($columnInfo->getOptionValue(self::UPDATABLE));
    }

    if ($columnInfo->hasOption(self::LENGTH)) {
      $column->setLength($columnInfo->getOptionValue(self::LENGTH));
    }

    if ($columnInfo->hasOption(self::TARGET)) {
      $column->setTarget($columnInfo->getOptionValue(self::TARGET));
      $column->setObject(true);
    }

    if ($collection->contains(self::JOIN)) {
      $joinInfo = $collection->getSingleAnnotation(self::JOIN);
      if (!$joinInfo->hasOption(self::NAME) || StringUtils::isNull($joinInfo->getOptionValue(self::NAME))) {
        throw GeneralExceptionFactory::genericException("No join field name is setted in {$entityname}");
      }
      $column->setJoinColumn($joinInfo->getOptionValue(self::NAME));
      $column->setOwner();
    }

    if ($collection->contains(self::FETCH)) {
      $fetchInfo = $collection->getSingleAnnotation(self::FETCH);
      if (!$fetchInfo->hasOption(self::TYPE) || StringUtils::isNull($fetchInfo->getOptionValue(self::TYPE))) {
        throw GeneralExceptionFactory::genericException("No fetch type encountered in {$entityname}");
      } else if ($fetchInfo->getOptionValue(self::TYPE) != self::LAZY && $fetchInfo->getOptionValue(self::TYPE) != self::EAGER) {
        throw GeneralExceptionFactory::genericException("Invalid fetch type annotation in {$entityname}");
      }

      $column->setFetch($fetchInfo->getOptionValue(self::TYPE));
    }

    if ($collection->contains(self::RELATION)) {
      $relationInfo = $collection->getSingleAnnotation(self::RELATION);
      if (!$relationInfo->hasOption(self::MAPPED_BY) || StringUtils::isNull($relationInfo->getOptionValue(self::MAPPED_BY))) {
        throw GeneralExceptionFactory::genericException("Invalid relation mapping annotation in {$entityname}");
      }

      $column->setMappedBy($relationInfo->getOptionValue(self::MAPPED_BY));
      if ($relationInfo->hasOption(self::ORPHAN_REMOVAL)) {
        $column->setOrphanRemoval();
      }
    }

    if ($collection->contains(self::JOIN_TABLE)) {
      $joinTableInfo = $collection->getSingleAnnotation(self::JOIN_TABLE);
      if (!$joinTableInfo->hasOption(self::NAME) || StringUtils::isNull($joinTableInfo->getOptionValue(self::NAME))) {
        throw GeneralExceptionFactory::genericException("No such argument name has been encountered in {$entityname}");
      } else if (!$joinTableInfo->hasOption(self::JOIN_COLUMN) || StringUtils::isNull($joinTableInfo->getOptionValue(self::JOIN_COLUMN))) {
        throw GeneralExceptionFactory::genericException("No such argument joinColumn has been encountered in {$entityname}");
      } else if (!$joinTableInfo->hasOption(self::INVERSE_JOIN) || StringUtils::isNull($joinTableInfo->getOptionValue(self::INVERSE_JOIN))) {
        throw GeneralExceptionFactory::genericException("No such argument inverseJoin has been encountered in {$entityname}");
      }

      $column->setJoinTable($joinTableInfo->getOptionValue(self::NAME));
      $column->setJoinColumn($joinTableInfo->getOptionValue(self::JOIN_COLUMN));
      $column->setInverseJoin($joinTableInfo->getOptionValue(self::INVERSE_JOIN));
    }

    return $column;
  }
}
