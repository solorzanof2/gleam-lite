<?php

namespace emerus\api;

class Page
{
  private $hasNext = FALSE;

  private $hasPrevious = FALSE;

  private $previousPage = 0;

  private $rawCurrentPage = 0;

  private $currentPage = 0;

  private $nextPage = 0;

  private $totalPages = 0;

  private $content = [];

  public function __construct()
  {
  }

  public function getHasNext(): bool
  {
    return $this->hasNext;
  }

  public function setHasNext(bool $hasNext): void
  {
    $this->hasNext = $hasNext;
  }

  public function getHasPrevious(): bool
  {
    return $this->hasPrevious;
  }

  public function setHasPrevious(bool $hasPrevious): void
  {
    $this->hasPrevious = $hasPrevious;
  }

  public function getPreviousPage(): int
  {
    return $this->previousPage;
  }

  public function setPreviousPage(int $previousPage): void
  {
    $this->previousPage = $previousPage - 1;
  }

  public function getCurrentPage(): int
  {
    return $this->currentPage;
  }

  public function getRawCurrentPage(): int
  {
    return $this->rawCurrentPage;
  }

  public function setCurrentPage(int $currentPage): void
  {
    $this->currentPage = $currentPage;
    if ($currentPage == 0) {
      $this->rawCurrentPage = 1;
    } else {
      $this->rawCurrentPage = $currentPage;
    }
  }

  public function getNextPage(): int
  {
    return $this->nextPage;
  }

  public function setNextPage(int $nextPage): void
  {
    $this->nextPage = $nextPage + 1;
  }

  public function getTotalPages(): int
  {
    return $this->totalPages;
  }

  public function setTotalPages(int $totalPages): void
  {
    $this->totalPages = $totalPages;
  }

  public function getContent(): array
  {
    return $this->content;
  }

  public function setContent(array $content): void
  {
    $this->content = $content;
  }
}
