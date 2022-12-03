<?php

namespace emerus\core;

use emerus\api\Pageable;
use emerus\api\Sorting;
use emerus\binds\Column;
use emerus\core\annotation\Parser;
use emerus\core\annotation\Reflection;
use emerus\data\Entity;
use emerus\handlers\CriteriaBuilder;
use emerus\utils\ObjectUtils;
use emerus\utils\StringUtils;
use RuntimeException;

class EntityManager
{

  const ID = 'id';

  const UUID = 'uid';

  const FETCH_EAGER = "EAGER";

  const FETCH_LAZY = "LAZY";

  const CLAZZ = 'class';

  const COLLECTION = 'collection';

  /**
   * ### Reflection Class Property
   * Contains the defined entity as
   * a Reflection Class
   * 
   * @var \ReflectionClass
   */
  private $class = null;

  /**
   * ### Table Name Property
   * Contains the current name
   * of the table for this
   * entity
   *
   * @var \emerus\binds\annotation\EntityMapping
   */
  // private $tableName = '';
  private $entityMapping;

  /**
   * ### Entity Name Property
   * Contains the name for the
   * current mapped entity
   *
   * @var string
   */
  private $entityName = '';

  /**
   * ### Entity Short Name Property
   * Contains the short name extracted
   * from Reflection
   *
   * @var string
   */
  private $entityShortName = '';

  /**
   * ### Columns Collection Property
   * Contains the collection of
   * columns for the current entity
   *
   * @var array
   */
  private $columnsCollection = [];

  /**
   * ### Is Child Property
   * Defines if the current entity
   * is invoking as child
   *
   * @var boolean
   */
  private $isChild = FALSE;

  /**
   * ### Relation Class Property
   * Defines the current join field
   * for the relation
   *
   * @var \emerus\binds\Column
   */
  private $relationClass;

  /**
   * ### Owner Relation Class
   * Contains the MappersColumn
   * That defines this entity as
   * owner of some relation
   *
   * @var array
   */
  private $ownerRelationProperties = [];

  /**
   * ### Instance Property
   * Contains the instance for
   * the current mapped entity
   *
   * @var \emerus\data\Entity
   */
  private $instance = null;

  /**
   * ### Raw Columns Collection
   * Defines the raw columns for the
   * entity; columns that not represent
   * some relationship
   *
   * @var array
   */
  private $rawColumnsCollection = [];

  /**
   * ### Raw Properties Collection Property
   * Contains the raw properties for the
   * entity; properties that not represent
   * some relationship
   *
   * @var array
   */
  private $rawPropertiesCollection = [];

  /**
   * ### Raw Property Relations Collection Property
   * Contains the properties that represent some
   * relation
   *
   * @var array
   */
  private $rawPropertyRelationsCollection = [];

  /**
   * ### Relations Collection Property
   * Defines the current relations for
   * this entity
   *
   * @var array
   */
  private $relationsCollection = [];

  /**
   * ### Data Collection Property
   * Contains the data when this
   * entity is gonna be stored
   *
   * @var array
   */
  private $dataCollection = [];

  /**
   * ### Record Property
   * Contains de Data Access Object
   * to persis data two way binding
   *
   * @var [type]
   */
  private $record = null;

  /**
   * ### Collection Manager Property
   * Contains all the defined Managers
   * related to this entity
   *
   * @var array
   */
  private $collectionManager = [];

  /**
   * ### Parent Id Property
   * Contains the parent id for
   * search relation that belongs
   * to the current entity
   *
   * @var integer
   */
  private $parentId = 0;

  /**
   * ### Default Database Connection
   *
   * @var string
   */
  private $defaultDbConnection = DataFactory::C_DEFAULT_DB;


  public function __construct(bool $isChild = false)
  {
    $this->isChild = $isChild;
  }


  public function getTableName(): string
  {
    // return $this->tableName;
    return $this->entityMapping->getTablename();
  }

  public function getEntityName(): string
  {
    return $this->entityName;
  }

  public function getColumnsCollection(): array
  {
    return $this->columnsCollection;
  }

  public function getColumnByName(string $key): ?Column
  {
    return $this->columnsCollection[$key] ?? null;
  }

  public function setRelationClass(Column $class): void
  {
    $this->relationClass = $class;
  }

  public function getRelationClass(): ?Column
  {
    return $this->relationClass;
  }

  public function getOwnerRelationProperties(): array
  {
    return $this->ownerRelationProperties;
  }

  public function getOwnerRelationPropertyByName(string $property): ?string
  {
    if (isset($this->ownerRelationProperties[$property])) {
      return $this->ownerRelationProperties[$property];
    }
    return null;
  }

  public function getOwner(): bool
  {
    return $this->relationClass->getOwner();
  }

  public function getPropertyBind(): string
  {
    return $this->relationClass->getName();
  }

  public function getJoinColumn(): string
  {
    return $this->relationClass->getJoinColumn();
  }

  public function getFetch(): string
  {
    return $this->relationClass->getFetch();
  }

  public function getMappedBy(): string
  {
    return $this->relationClass->getMappedBy();
  }

  public function getType(): string
  {
    return $this->relationClass->getType();
  }

  public function getJoinTable(): string
  {
    return $this->relationClass->getJoinTable();
  }

  public function getInverseJoin(): string
  {
    return $this->relationClass->getInverseJoin();
  }

  public function getIsManyToMany(): bool
  {
    return $this->relationClass->getIsManyToMany();
  }

  public function getInstance(): Entity
  {
    $instance = $this->instance;
    $this->instance = null;
    return $instance;
  }

  public function getRawPropertiesCollection(): array
  {
    return $this->rawPropertiesCollection;
  }

  public function getRawColumnsCollection(): array
  {
    return $this->rawColumnsCollection;
  }

  public function getRawPropertyRelationsCollection(): array
  {
    return $this->rawPropertyRelationsCollection;
  }

  public function getRelationsCollection(): array
  {
    return $this->relationsCollection;
  }

  public function isChild(): bool
  {
    return $this->isChild;
  }

  public function ownerIsPresent(): bool
  {
    return (count($this->ownerRelationProperties) > 0);
  }

  public function relationsIsPresent(): bool
  {
    return (count($this->relationsCollection) > 0);
  }

  public function checkIsOwner(string $property): bool
  {
    $column = $this->columnsCollection[$property];
    return $column->getOwner();
  }

  public function isUnregistered(): bool
  {
    return ($this->dataCollection[self::ID] == 0);
  }

  public function getDataId(): int
  {
    if ($this->dataCollection[self::ID] != 0) {
      return (int) $this->dataCollection[self::ID];
    }
    return 0;
  }

  public function getDataCollection(): array
  {
    return $this->dataCollection;
  }

  public function updateDataCollectionByKey(string $key, $value): void
  {
    if (isset($this->dataCollection[$key]) || in_array($key, $this->rawColumnsCollection)) {
      $this->dataCollection[$key] = $value;
    }
  }

  public function setCollectionManager(array $collection): void
  {
    if (empty($this->collectionManager)) {
      $this->collectionManager = $collection;
    }
  }

  private function getManagerByName(string $manager): EntityManager
  {
    return $this->collectionManager[$manager];
  }

  public function setParentId(int $id): void
  {
    $this->parentId = $id;
  }

  private function getConnection(string $connectionName = null): \PDO
  {
    if (StringUtils::isNull($connectionName)) {
      $connectionName = $this->defaultDbConnection;
    }
    return DataFactory::getInstance()->getConnection($connectionName);
  }

  public function setValue(string $property, $value): void
  {

    if (is_null($this->instance)) {
      $entity = $this->entityName;
      $this->instance = new $entity();
    }

    if ($property == self::ID) {
      $parent = $this->class->getParentClass();
      $propertyId = $parent->getProperty($property);
      $propertyId->setAccessible(TRUE);
      $propertyId->setValue($this->instance, $value);

      return;
    }

    if (!property_exists($this->entityName, $property)) {
      throw GeneralExceptionFactory::genericException("Invalid property name in {$this->entityName}");
    }

    $reflectionProperty = $this->class->getProperty($property);
    $reflectionProperty->setAccessible(TRUE);
    $reflectionProperty->setValue($this->instance, $value);
  }

  public function mapEntity(string $entity): void
  {
    $this->entityName = $entity;

    $reflectionClass = new Reflection(true);
    $reflectionClass->setAnnotationParser(new Parser());
    try {
      $this->class = $reflectionClass->getClass($entity);
    } catch (\ReflectionException $e) {
      throw GeneralExceptionFactory::genericException($e->getMessage());
    }

    $this->entityShortName = $this->class->getShortName();

    $this->entityMapping = AnnotatedElementUtils::getEntityAnnotations($this->entityName, $reflectionClass->getClassAnnotations($this->entityName));

    $propertiesCollection = $reflectionClass->getProperties($this->entityName);

    if (count($propertiesCollection) == 1 && isset($propertiesCollection[self::ID])) {
      throw GeneralExceptionFactory::genericException("Properties not found or has no public modifiers");
    }

    foreach ($propertiesCollection as $property) {
      $column = AnnotatedElementUtils::getPropertiesAnnotations($this->entityName, $property, $reflectionClass->getPropertyAnnotations($this->entityName, $property->getName()));
      if (ObjectUtils::isNotNull($column)) {
        if ($column->getObject()) {
          if (($column->getFetch() == self::FETCH_LAZY && !$this->isChild) || $column->getFetch() == self::FETCH_EAGER && $this->isChild || $column->getFetch() == self::FETCH_EAGER && !$this->isChild) {
            $this->rawPropertyRelationsCollection[] = $property->getName();
            $this->relationsCollection[$property->getName()] = $column;
          }

          if ($column->getOwner()) {
            $this->ownerRelationProperties[] = $property->getName();
            $this->rawColumnsCollection[] = $column->getJoinColumn();
          }
        }

        $keyName = (StringUtils::isNull($column->getColumn())) ? $column->getName() : $column->getColumn();
        $this->columnsCollection[$keyName] = $column;
        if (!StringUtils::isNull($column->getColumn())) {
          $this->rawColumnsCollection[] = $column->getColumn();
        }
        $this->rawPropertiesCollection[] = $property->getName();
      }
    }
  }

  public function getCriteriaBuilder(): CriteriaBuilder
  {
    return new CriteriaBuilder($this->entityMapping->getTablename());
  }

  private function getProperty(string $propertyName): \ReflectionProperty
  {
    $property = $this->class->getProperty($propertyName);
    $property->setAccessible(TRUE);
    return $property;
  }

  public function getAll(): array
  {
    return $this->solveRelations($this->getBySql($this->getCriteriaBuilder()->getSql()));
  }

  public function getById(int $id): array
  {
    $builder = $this->getCriteriaBuilder()->where(self::ID, CriteriaBuilder::EQUALS, $id);
    $dataCollection = $this->getBySql($builder->getSql(), $builder->getParameters());

    return $this->solveRelations($dataCollection);
  }

  public function getByUUID(string $uuid): array
  {
    $builder = $this->getCriteriaBuilder()->where(self::UUID, CriteriaBuilder::EQUALS, $uuid);
    $dataCollection = $this->getBySql($builder->getSql(), $builder->getParameters());

    return $this->solveRelations($dataCollection);
  }

  public function getByForeingKey(string $owner, int $parentId): array
  {
    $column = $this->columnsCollection[$owner];
    $builder = $this->getCriteriaBuilder()->where($column->getJoinColumn(), CriteriaBuilder::EQUALS, $parentId);
    $dataCollection = $this->getBySql($builder->getSql(), $builder->getParameters());

    return $this->solveRelations($dataCollection);
  }

  public function getWhere(array $where, int $limit = 0, string $orderBy = null): ?array
  {
    $builder = $this->getCriteriaBuilder()
      ->addWhereAndEqualsCondition($where);

    if ($limit != 0) {
      $builder->setLimit($limit);
    }

    if (StringUtils::isNotNull($orderBy)) {
      $builder->orderBy(self::ID, $orderBy);
    }

    $dataCollection = $this->getBySql($builder->getSql(), $builder->getParameters());

    return $this->solveRelations($dataCollection);
  }

  public function getWherePaginated(array $where, Pageable $pageRequest)
  {
    $builder = $this->getCriteriaBuilder()
      ->addWhereAndEqualsCondition($where)
      ->setLimit($pageRequest->getSize(), $pageRequest->getOffset())
      ->orderBy($pageRequest->getColumn(), $pageRequest->getSort());

    $dataCollection = $this->getBySql($builder->getSql(), $builder->getParameters());

    return $this->solveRelations($dataCollection);
  }

  public function getWhereIn(string $field, array $inValues, array $where = [], int $limit = 0, Sorting $sort = null)
  {
    $builder = $this->getCriteriaBuilder();
    $conditionsCollection = [];

    if (!empty($where)) {
      foreach ($where as $whereField => $value) {
        $conditionsCollection[] = CriteriaBuilder::equals($whereField, $value);
      }
      $conditionsCollection[] = CriteriaBuilder::in($field, $inValues);
      $builder->addWhereAndCondition($conditionsCollection);
    } else {
      $builder->where($field, CriteriaBuilder::IN, $inValues);
    }

    if ($limit != 0) {
      $builder->setLimit($limit);
    }

    if ($sort != null) {
      $builder->orderBy($sort->getColumn(), $sort->getSort());
    }

    $dataCollection = $this->getBySql($builder->getSql(), $builder->getParameters());

    return $this->solveRelations($dataCollection);
  }

  public function getQuery(string $query): ?array
  {
    return $this->solveRelations($this->getBySql($query));
  }

  public function getTotalRows(array $where): int
  {
    $builder = $this->getCriteriaBuilder()->count();
    $conditionsCollection = [];
    foreach ($where as $field => $value) {
      $conditionsCollection[] = CriteriaBuilder::equals($field, $value);
    }

    if (count($conditionsCollection) > 1) {
      $builder->addWhereAndCondition($conditionsCollection);
    } else {
      foreach ($where as $field => $value) {
        $builder->where($field, CriteriaBuilder::EQUALS, $value);
        break;
      }
    }

    list($data) = $this->getBysql($builder->getSql(), $builder->getParameters());
    return $data['COUNT(*)'];
  }

  private function solveRelations(array $dataCollection): array
  {
    $resultCollection = [];
    foreach ($dataCollection as $row) {
      foreach ($row as $key => $value) {
        if (!preg_match('/^(.*)_id$/', $key)) {
          $this->setValue($key, $value);
        }
      }

      foreach ($this->relationsCollection as $property => $relation) {
        $childManager = $this->getManagerByName($relation->getTarget());
        $childManager->setCollectionManager($this->collectionManager);
        $childManager->setParentId($row[self::ID]);
        $where = null;
        $dataChild = [];

        if ($relation->getIsManyToMany()) {
          $builder = $this->getCriteriaBuilder()
            ->select($relation->getJoinTable())
            ->where($relation->getJoinColumn(), CriteriaBuilder::EQUALS, $row[self::ID]);
          $joinDataCollection = $this->getBySql($builder->getSql(), $builder->getParameters());

          foreach ($joinDataCollection as $joinRow) {
            list($result) = $childManager->getById($joinRow[$relation->getInverseJoin()]);
            $dataChild[] = $result;
          }
        } elseif ($relation->getOwner()) {
          $childId = $row[$relation->getJoinColumn()] ?? null;
          if (is_null($childId)) {
            continue;
          }
          $dataChild = $childManager->getById($childId);
        } else {
          $dataChild = $childManager->getByForeingKey($relation->getMappedBy(), (int) $row[self::ID]);
        }

        if ($relation->getType() == self::COLLECTION) {
          $this->setValue($relation->getName(), $dataChild);
        } elseif ($relation->getType() == self::CLAZZ && is_array($dataChild)) {
          $this->setValue($relation->getName(), $dataChild[0]);
        }
      }

      $resultCollection[] = $this->getInstance();
    }

    return $resultCollection;
  }

  public function getRawDataByOpenQuery(string $query, array $bindsCollection = []): ?array
  {
    $collection = $this->getBySql($query, $bindsCollection);
    if (empty($collection)) {
      return null;
    }

    return $collection;
  }

  public function executeQuery(string $query): void
  {
    $databaseHandler = $this->getConnection();

    $statement = $databaseHandler->prepare($query);
    $result = $statement->execute();

    if (!$result) {
      $errorInfo = $statement->errorInfo();
      throw new RuntimeException($errorInfo[2]);
    }
  }

  private function getBySql(string $query, array $bindsCollection = [], string $connectionName = null): array
  {
    $databaseHandler = $this->getConnection($connectionName);

    $statement = $databaseHandler->prepare($query);
    $statement->execute($bindsCollection);

    return $statement->fetchAll(\PDO::FETCH_ASSOC);
  }

  private function saveBySql(string $query, array $bindsCollection = [], string $connectionName = null): int
  {
    $databaseHandler = $this->getConnection($connectionName);

    $statement = $databaseHandler->prepare($query);
    $result = $statement->execute($bindsCollection);

    if (!$result) {
      $errorInfo = $statement->errorInfo();
      throw new RuntimeException($errorInfo[2]);
    }

    return $databaseHandler->lastInsertId();
  }

  private function deleteBySql(string $query, array $bindsCollection = [], string $connectionName = null): void
  {
    $databaseHandler = $this->getConnection($connectionName);

    $statement = $databaseHandler->prepare($query);
    $result = $statement->execute($bindsCollection);

    if (!$result) {
      $errorInfo = $statement->errorInfo();
      throw new RuntimeException($errorInfo[2]);
    }
  }

  public function save(Entity $entity): int
  {
    $this->class = new \ReflectionClass($entity);

    $entityArray = [];
    foreach ($this->columnsCollection as $column) {
      if ($column->getName() == self::ID) {
        $parent = $this->class->getParentClass();
        $propertyId = $parent->getProperty($column->getName());
        $propertyId->setAccessible(TRUE);
        $entityArray[self::ID] = $propertyId->getValue($entity);
      } elseif (!StringUtils::isNull($column->getColumn())) {
        if ($column->getColumn() == self::UUID && $entityArray[self::ID] == 0) {
          $entityArray[self::UUID] = ObjectUtils::generateUuid();
        } else {
          $childProperty = $this->class->getProperty($column->getName());
          $childProperty->setAccessible(TRUE);
          # here is were validations must be applied
          $entityArray[$column->getColumn()] = $childProperty->getValue($entity);
        }
      }
    }

    # solve owner at first;
    foreach ($this->ownerRelationProperties as $relation) {
      $column = $this->columnsCollection[$relation];
      $childProperty = $this->getProperty($relation);
      $dataChild = $childProperty->getValue($entity);

      $childManager = $this->getManagerByName($column->getTarget());
      $childManager->setCollectionManager($this->collectionManager);

      if ($childManager->isChild()) {
        if ($dataChild == null && $this->parentId == 0) {
          continue;
        }

        if ($dataChild == null && $this->parentId != 0) {
          $entityArray[$column->getJoinColumn()] = $this->parentId;
        } else {
          if ($dataChild->getId() != 0 && !$column->updatable()) {
            $entityArray[$column->getJoinColumn()] = $dataChild->getId();
          } else {
            $entityArray[$column->getJoinColumn()] = $childManager->save($dataChild);
          }
        }
      } else {
        $entityArray[$column->getJoinColumn()] = $this->parentId;
      }
    }

    # save entity before resolve foreing relations
    $parentId = 0;
    if ($entity->getId() == 0) {
      unset($entityArray[self::ID]);
      $builder = $this->getCriteriaBuilder()
        ->insert()
        ->values($entityArray);
      $parentId = $this->saveBySql($builder->getSql(), $builder->getParameters());
    } else {
      $parentId = $entityArray[self::ID];
      unset($entityArray[self::ID]);
      $builder = $this->getCriteriaBuilder()
        ->update()
        ->values($entityArray)
        ->where(self::ID, CriteriaBuilder::EQUALS, $parentId);
      $this->saveBySql($builder->getSql(), $builder->getParameters());
    }

    # solve foreing relations with entity id;
    foreach ($this->relationsCollection as $relation) {
      $childManager = null;
      if ($relation->getOwner()) {
        continue;
      }

      $childManager = $this->getManagerByName($relation->getTarget());
      $childManager->setCollectionManager($this->collectionManager);
      $childManager->setParentId($parentId);
      $childProperty = $this->getProperty($relation->getName());
      $dataChild = $childProperty->getValue($entity);

      if ($relation->getIsManyToMany()) {
        # read current data in database;
        $builder = $this->getCriteriaBuilder()
          ->select($relation->getJoinTable())
          ->where($relation->getJoinColumn(), CriteriaBuilder::EQUALS, $entity->getId());
        $joinDataCollection = $this->getBySql($builder->getSql(), $builder->getParameters());

        # if has no data at joinTable
        if (empty($joinDataCollection)) {
          # verify if dataChild has any values, 
          # in case of null just continue with main foreach
          if (is_null($dataChild)) {
            continue;
          }
          # all for insert
          # insert first all childsManager related objects
          foreach ($dataChild as $childRow) {
            $nextId = 0;
            if ($childRow->getId() != 0) {
              $nextId = $childRow->getId();
            } else {
              $nextId = $childManager->save($childRow);
            }

            # then insert all corresponding joinTable data
            $builder = $this->getCriteriaBuilder()
              ->insert($relation->getJoinTable())
              ->values([
                $relation->getJoinColumn() => $parentId,
                $relation->getInverseJoin() => $nextId
              ]);
            $this->saveBySql($builder->getSql(), $builder->getParameters());
          }
        } else {
          # add data to collector;
          $joinCollector = new Collector($relation->getJoinColumn(), $relation->getInverseJoin(), $joinDataCollection);

          foreach ($dataChild as $childRow) {
            $nextId = 0;
            if ($childRow->getId() != 0) {
              $nextId = $childRow->getId();
            } else {
              $nextId = $childManager->save($childRow);
            }
            $joinCollector->add($nextId);
          }

          # collector->getDroppeds
          $dropsCollection = $joinCollector->getDroppeds();

          # delete droppeds from @joinTable;
          if (!empty($dropsCollection)) {
            $builder = $this->getCriteriaBuilder()
              ->delete($relation->getJoinTable())
              ->addWhereAndCondition([
                CriteriaBuilder::in($relation->getInverseJoin(), $dropsCollection),
                CriteriaBuilder::equals($relation->getJoinColumn(), $parentId)
              ]);
            $this->deleteBySql($builder->getSql(), $builder->getParameters());
          }

          # add collector->getAddeds to @joinTable;
          foreach ($joinCollector->get() as $newRow) {
            if ($newRow[self::ID] != null) {
              # for update
              $updateId = $newRow[self::ID];

              # filtering id for prevent updating it
              unset($newRow[self::ID]);
              $builder = $this->getCriteriaBuilder()
                ->update($relation->getJoinTable())
                ->values($newRow)
                ->where(self::ID, CriteriaBuilder::EQUALS, $updateId);

              # maybe here seems to be a good site to make some validations, think about!
              $this->saveBySql($builder->getSql(), $builder->getParameters());
            } else {
              # to insert
              unset($newRow[self::ID]);
              $builder = $this->getCriteriaBuilder()
                ->insert($relation->getJoinTable())
                ->values($newRow);

              # maybe here seems to be a good site to make some validations, think about!
              $this->saveBySql($builder->getSql(), $builder->getParameters());
            }
          }
        }
      } else {
        if ($relation->getType() == self::COLLECTION) {
          if (!empty($dataChild)) {
            foreach ($dataChild as $childRow) {
              $childManager->save($childRow);
            }
          }
        } else {
          if (!empty($dataChild)) {
            $childManager->save($dataChild);
          }
        }
      }
    }

    return $parentId;
  }

  public function delete(Entity $entity): void
  {
    $this->class = new \ReflectionClass($entity);

    foreach ($this->relationsCollection as $relation) {
      if ($relation->getIsManyToMany()) {
        $builder = $this->getCriteriaBuilder()
          ->delete($relation->getJoinTable())
          ->where($relation->getJoinColumn(), CriteriaBuilder::EQUALS, $entity->getId());
        $this->deleteBySql($builder->getSql(), $builder->getParameters());
      }

      if ($relation->getOrphanRemoval()) {
        $childManager = $this->getManagerByName($relation->getTarget());
        $childManager->setCollectionManager($this->collectionManager);
        $childProperty = $this->getProperty($relation->getName());
        $dataChild = $childProperty->getValue($entity);

        if ($relation->getType() == self::COLLECTION && !empty($dataChild)) {
          foreach ($dataChild as $childRow) {
            $childManager->delete($childRow);
          }
        } elseif ($dataChild != null) {
          $childManager->delete($dataChild);
        }
      }
    }

    $builder = $this->getCriteriaBuilder()
      ->delete()
      ->where(self::ID, CriteriaBuilder::EQUALS, $entity->getId());
    $this->deleteBySql($builder->getSql(), $builder->getParameters());
  }
}
