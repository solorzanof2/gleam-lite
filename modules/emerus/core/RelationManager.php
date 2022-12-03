<?php

namespace emerus\core;

class RelationManager
{
  private $rootEntityName = '';

  private $rootEntityManager = null;

  /** EntityManagersCollection */
  private $managersCollection = [];

  /** Relation Collection Array */
  private $collection = [];

  private $hasRelations = FALSE;

  public function __construct(string $entity)
  {
    $this->rootEntityName = $entity;
  }

  public function getRootEntityName(): string
  {
    return $this->rootEntityName;
  }

  public function getRootEntityManager(): EntityManager
  {
    return $this->rootEntityManager;
  }

  public function getTableName(): string
  {
    return $this->rootEntityManager->getTableName();
  }

  public function getCollection(): array
  {
    return $this->collection;
  }

  public function getManagersCollection(): array
  {
    return $this->managersCollection;
  }

  public function getManagerByName(string $entityName): EntityManager
  {
    return $this->managersCollection[$entityName];
  }

  public function getCollectionByKey(string $key): ?array
  {
    if (!isset($this->collection[$key])) {
      return null;
    }
    return $this->collection[$key];
  }

  public function getHasRelations(): bool
  {
    return $this->hasRelations;
  }

  public function map(): void
  {
    $this->rootEntityManager = new EntityManager();
    $this->rootEntityManager->mapEntity($this->rootEntityName);

    $this->managersCollection[$this->rootEntityManager->getEntityName()] = $this->rootEntityManager;
    $this->solveRelations($this->rootEntityManager);
  }

  private function solveRelations(EntityManager $manager): void
  {
    if (!$manager->relationsIsPresent()) {
      return;
    }

    foreach ($manager->getRelationsCollection() as $property => $relation) {
      $childManager = new EntityManager(TRUE);
      $childManager->mapEntity($relation->getTarget());
      $childManager->setRelationClass($relation);

      $this->managersCollection[$childManager->getEntityName()] = $childManager;
      $this->collection[$manager->getEntityName()][] = $relation->getTarget();

      $this->solveRelations($childManager);
    }

    $this->hasRelations = TRUE;
  }
}
