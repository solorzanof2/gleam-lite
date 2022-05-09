<?php

namespace emerus\data;

use emerus\api\Page;
use emerus\api\Pageable;
use emerus\api\Sorting;
use emerus\core\EntityManager;
use emerus\core\GeneralExceptionFactory;
use emerus\core\InvalidArgumentExceptionFactory;
use emerus\core\RelationManager;
use emerus\utils\ObjectUtils;
use emerus\utils\StringUtils;
use Exception;

abstract class Repository
{

    /**
     * @var string
     */
    private $entity;

    /**
     * @var \emerus\core\RelationManager
     */
    private $relationManager;

    public function __construct(string $entityname = null)
    {
        if (StringUtils::isNull($entityname)) {
            throw InvalidArgumentExceptionFactory::getNameNotFoundException();
        }
        $this->entity = $entityname;

        $this->relationManager = new RelationManager($this->entity);
        $this->relationManager->map();
    }

    protected final function getRootManager(): EntityManager
    {
        return $this->relationManager->getRootEntityManager();
    }

    protected final function getManagersCollection(): array
    {
        return $this->relationManager->getManagersCollection();
    }

    protected final function prepareManager(): EntityManager
    {
        $manager = $this->getRootManager();
        $manager->setCollectionManager($this->getManagersCollection());
        return $manager;
    }

    public function findById(int $id): ?Entity
    {
        try {
            $entity = $this->prepareManager()->getById($id);
            if (ObjectUtils::isEmpty($entity)) {
                return null;
            }

            return $entity[0];
        } catch (Exception $e) {
            throw GeneralExceptionFactory::genericException($e->getMessage());
        }
    }

    public function findByUID(string $uuid): ?Entity
    {
        try {
            $manager = $this->prepareManager();
            $entity = $manager->getByUUID($uuid);
            if (empty($entity)) {
                return null;
            }

            return $entity[0];
        } catch (Exception $e) {
            throw GeneralExceptionFactory::genericException($e->getMessage());
        }
    }

    public function findAll(): ?array
    {
        try {
            $manager = $this->prepareManager();
            $collection = $manager->getAll();
            if (empty($collection)) {
                return null;
            }

            return $collection;
        } catch (Exception $e) {
            throw GeneralExceptionFactory::genericException($e->getMessage());
        }
    }

    public function findWhere(array $where, int $limit = 0, string $orderBy = null): ?array
    {
        try {
            if (empty($where)) {
                throw new Exception("Where clausule seems to be empty in {$this->entity} repository");
            }

            $manager = $this->prepareManager();
            $collection = $manager->getWhere($where, $limit, $orderBy);
            if (empty($collection)) {
                return null;
            }

            return $collection;
        } catch (Exception $e) {
            throw GeneralExceptionFactory::genericException($e->getMessage());
        }
    }

    public function findWhereIn(string $field, array $inValues, array $where = [], int $limit = 0, Sorting $sort = null): ?array
    {
        try {
            if (StringUtils::isNull($field)) {
                throw new Exception("Field value is null or empty in {$this->entity} repository");
            } elseif (empty($inValues)) {
                throw new Exception("In clausule seems to be empty in {$this->entity} repository");
            }

            $manager = $this->prepareManager();
            $collection = $manager->getWhereIn($field, $inValues, $where, $limit, $sort);
            if (empty($collection)) {
                return null;
            }

            return $collection;
        } catch (Exception $e) {
            throw GeneralExceptionFactory::genericException($e->getMessage());
        }
    }

    protected function findByQuery(string $query): ?array
    {
        try {
            if (StringUtils::isNull($query)) {
                throw new Exception("Query argument seems to be empty in {$this->entity} repository");
            }

            $manager = $this->prepareManager();
            $collection = $manager->getQuery($query);

            if (empty($collection)) {
                return null;
            }

            return $collection;
        } catch (Exception $e) {
            throw GeneralExceptionFactory::genericException($e->getMessage());
        }
    }

    public function findRawDataByQuery(string $query): ?array
    {
        try {
            if (StringUtils::isNull($query)) {
                throw new Exception("Query argument seems to be empty in {$this->entity} repository");
            }

            $manager = $this->prepareManager();
            $collection = $manager->getRawDataByOpenQuery($query);

            if (empty($collection)) {
                return null;
            }

            return $collection;
        } catch (Exception $e) {
            throw GeneralExceptionFactory::genericException($e->getMessage());
        }
    }

    public function findWherePaginated(array $where, Pageable $pageRequest): Page
    {
        try {
            $this->validateWhereAndPagination($where, $pageRequest);

            $totalRows = $this->count($where);
            $totalPages = ceil($totalRows / $pageRequest->getSize());
            $pageRequest->setTotalPages($totalPages);

            $manager = $this->prepareManager();
            $dataCollection = $manager->getWherePaginated($where, $pageRequest);

            return $this->preparePagination($dataCollection, $pageRequest);
        } catch (Exception $e) {
            throw GeneralExceptionFactory::genericException($e->getMessage());
        }
    }

    public function findWhereInPaginated(array $where, Pageable $pageRequest): Page
    {
        try {
            $this->validateWhereAndPagination($where, $pageRequest);

            $totalRows = $this->count($where);
        } catch (Exception $e) {
            throw GeneralExceptionFactory::genericException($e->getMessage());
        }
    }

    public function count(array $where): int
    {
        try {
            $manager = $this->getRootManager();
            return $manager->getTotalRows($where);
        } catch (Exception $e) {
            throw GeneralExceptionFactory::genericException($e->getMessage());
        }
    }

    public function save(Entity $entity): Entity
    {
        try {
            $manager = $this->getRootManager();
            $entityName = $manager->getEntityName();
            $this->validateEntity($entity, $entityName);

            $manager->setCollectionManager($this->getManagersCollection());
            $entityId = $manager->save($entity);

            return $this->findById($entityId);
        } catch (Exception $e) {
            throw GeneralExceptionFactory::genericException($e->getMessage());
        }
    }

    public function remove(Entity $entity): void
    {
        try {
            $manager = $this->getRootManager();
            $entityName = $manager->getEntityName();
            $this->validateEntity($entity, $entityName);

            $manager->setCollectionManager($this->getManagersCollection());
            $manager->delete($entity);
        } catch (Exception $e) {
            throw GeneralExceptionFactory::genericException($e->getMessage());
        }
    }

    private function validateEntity(Entity $entity, string $entityName): void
    {
        if (!$entity instanceof $entityName) {
            $class = get_class($entity);
            throw new Exception("{$class} doesn't belong to repository " . __CLASS__);
        }
    }

    private function validateWhereAndPagination(array $where, Pageable $pageRequest): void
    {
        if (empty($where) || $pageRequest->getSize() == 0) {
            throw new Exception("More than one argument attributon is empty in {$this->entity} repository");
        }
    }

    protected function preparePagination(array $dataCollection, Pageable $pageRequest): Page
    {
        $page = new Page();
        # currentPage;
        $page->setCurrentPage($pageRequest->getRequestedPage());
        # previousPage;
        if ($pageRequest->getPage() == 0) {
            $page->setPreviousPage(0);
            $page->setHasPrevious(FALSE);
        } elseif ($pageRequest->getPage() >= 1) {
            $page->setPreviousPage($pageRequest->getRequestedPage());
            $page->setHasPrevious(TRUE);
        }

        # nextpage;
        if ($pageRequest->getRequestedPage() == $pageRequest->getTotalPages() || $pageRequest->getRequestedPage() > $pageRequest->getTotalPages()) {
            $page->setNextPage(0);
            $page->setHasNext(FALSE);
        } else {
            $page->setNextPage($pageRequest->getRequestedPage());
            $page->setHasNext(TRUE);
        }
        # totalPages;
        $page->setTotalPages($pageRequest->getTotalPages());
        # content;
        $page->setContent($dataCollection);

        return $page;
    }
}
