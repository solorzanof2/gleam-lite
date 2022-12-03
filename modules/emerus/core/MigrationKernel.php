<?php

namespace emerus\core;

use emerus\data\Changelog;
use emerus\data\MigrationRepository;
use emerus\utils\StringUtils;
use Exception;

class MigrationKernel
{

  const ChangelogTable = '`databasechangelog`';

  public $repository;

  public $migrationRoute = '';

  public $fileManager;

  public $changelogCollection = [];
  
  public $isLock = false; // #TODO maybe this could be handled by a single file dblock.tmp;

  public function __construct(string $migrationRoute)
  {
    $this->repository = new MigrationRepository();
    $this->migrationRoute = $migrationRoute;
    $this->fileManager = new FileManager($migrationRoute);
  }

  public function hasChangelog(): bool
  {
    return (count($this->changelogCollection) > 0);
  }
  
  public function initialize(): void
  {
    try {
      // verify if migration changelog exists or create it if isn't;
      $this->checkChangelog();

      // scan migration folder searching files;
      $this->fileManager->scanMigrationPath();
      
      // parse all migrations;
      $this->fileManager->parseMigrations();

      $this->changelogCollection = $this->repository->findAll() ?? [];
    }
    catch (Exception $error) {
      $this->isLock = true;
      throw $error;
    }
  }

  public function start(): void
  {
    try {
      if ($this->isLock) {
        throw new Exception("Migration has been locked.");
      }

      foreach ($this->fileManager->getMigration() as $migration) {
        $changelog = $this->findChangelogByAuthorAndTitle($migration->author, $migration->title);
        if ($changelog) {
          if ($changelog->signature != $migration->signature) {
            throw new Exception("MD5 signature has changed before was '{$changelog->signature}', but now is: {$migration->signature}");
          }

          continue;
        }

        foreach ($migration->queriesCollection as $query) {
          $this->repository->execute($query);
        }

        $entity = new Changelog();
        $entity->author = $migration->author;
        $entity->title = $migration->title;
        $entity->route = $migration->route;
        $entity->dateexecuted = date('Y-m-d h:i:s');
        $entity->signature = $migration->signature;

        $this->repository->save($entity);
      }
    }
    catch (Exception $error) {
      throw $error;
    }
  }

  private function checkChangelog(): void
  {
    try {
      $this->repository->findRawDataByQuery("SELECT 1 FROM ".self::ChangelogTable.";");
      return;
    }
    catch (Exception $error) {
      if (!StringUtils::contains($error->getMessage(), 'Base table or view not found')) {
        throw $error;
      }
      
      $builder = [
        'CREATE TABLE '.self::ChangelogTable.' (',
        '`id` bigint(11) PRIMARY KEY NOT NULL AUTO_INCREMENT, ',
        '`author` varchar(255) NOT NULL, ',
        '`title` varchar(255) UNIQUE NOT NULL, ',
        '`route` varchar(255) NOT NULL, ',
        '`dateexecuted` DATETIME NOT NULL, ',
        '`signature` varchar(50) NOT NULL);',
      ];

      $query = implode('', $builder);
      $this->repository->execute($query);
    }
  }

  private function findChangelogByAuthorAndTitle(string $author, string $title): ?Changelog
  {
    if (!$this->hasChangelog()) {
      return null;
    }

    $changelog = null;
    foreach ($this->changelogCollection as $row) {
      if ($row->author == $author && $row->title == $title) {
        $changelog = $row;
        break;
      }
    }

    return $changelog;
  }
}