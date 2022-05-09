<?php

namespace emerus\api;

use emerus\utils\SortEnum;

class Pageable
{
    private $page = 0;

    private $offset = 0;

    private $size = 0;

    private $sort = null;

    private $column = null;

    private $rawPage = 0;

    private $totalPages = 0;

    public function __construct(int $page, int $size, string $sort = null, string $column = null)
    {
        if ($page > 1) {
            $this->offset = ($page - 1) * $size;
            $this->page = $page - 1;
            $this->rawPage = $page;
        } elseif ($page == 0 || $page == 1) {
            $this->rawPage = 1;
        }
        $this->size = $size;

        if (!is_null($sort) && !is_null($column)) {
            $this->sort = $sort;
            $this->column = $column;
        }
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getSort(): string
    {
        return $this->sort;
    }

    public function getSortIsDesc(): bool
    {
        return ($this->sort == SortEnum::DESC);
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getRequestedPage(): int
    {
        return $this->rawPage;
    }

    public function setTotalPages(int $totalPages): void
    {
        $this->totalPages = $totalPages;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }
}

?>