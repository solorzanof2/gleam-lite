<?php

namespace emerus\core;

use emerus\data\Migration;
use emerus\utils\StringBuilder;
use emerus\utils\StringUtils;
use Exception;
use Generator;

class FileManager
{

  const Changeset = '--changeset';
  const Procedure = '--pl';

  private $basePath = '';

  private $filesCollection = [];

  private $migrationsCollection = [];

  public function __construct(string $basePath)
  {
    $this->basePath = $basePath;
  }

  public function scanMigrationPath(): void
  {
    try {
      $collection = [];

      $filesCollection = array_diff(scandir($this->basePath), ['.', '..']);
      foreach ($filesCollection as $file) {
        $route = $this->basePath.DS.$file;
        if (!StringUtils::contains($route, '.sql')) {
          continue;
        }

        $collection[] = $route;
      }

      $this->filesCollection = $collection;
    }
    catch (Exception $error) {
      throw new Exception('Something went wrong when try to scan the migration directory.', 30455, $error);
    }
  }

  public function parseMigrations(): void
  {
    try {
      foreach ($this->filesCollection as $route) {
        $fileStream = fopen($route, 'r');

        $fileContent = fread($fileStream, filesize($route));
        
        $migration = new Migration();
        $queryBuilder = new StringBuilder();

        $linesCollection = explode(PHP_EOL, $fileContent);
        
        foreach ($linesCollection as $line) {
          $text = preg_replace('/\r/', '', $line);

          if (!$text) {
            continue;
          }

          if (!StringUtils::contains($text, self::Changeset)) {
            $queryBuilder->append(trim($text));
            if ($migration->isProcedure) {
              continue;
            }

            if (StringUtils::endsWith($text, ';')) {
              $migration->queriesCollection[] = $queryBuilder->toString(' ');
              $queryBuilder = new StringBuilder();
            }

            continue;
          }

          $isProcedure = StringUtils::contains($text, self::Procedure);
          if ($isProcedure && $queryBuilder->getLength()) {
            $migration->queriesCollection[] = $queryBuilder->toString(' ');
          }

          if ($migration->queriesCollectionLength()) {
            $migration->signature = $this->generateSignature($migration);
            $migration->route = $route;
            $migration->rawContent = $fileContent;

            $this->migrationsCollection[] = $migration;
            $migration = new Migration();
          }

          if ($isProcedure) {
            $text = str_replace(self::Procedure, '', $text);
            $migration->isProcedure = $isProcedure;
          }

          $queryBuilder = new StringBuilder();

          $info = trim(str_replace(self::Changeset, '', $text));
          list($author, $title) = explode(':', $info);
          $migration->author = $author;
          $migration->title = $title;
          $migration->changeset = $text;
        }

        if ($migration->isProcedure) {
          $migration->queriesCollection[] = $queryBuilder->toString(' ');
        }

        $migration->signature = $this->generateSignature($migration);
        $migration->route = $route;
        $migration->rawContent = $fileContent;

        $this->migrationsCollection[] = $migration;

        fclose($fileStream);
      }

      return;
    }
    catch (Exception $error) {
      throw new Exception('Something went wrong when try to parse the migration files', 30456, $error);
    }
  }

  public function getMigration(): Generator
  {
    foreach ($this->migrationsCollection as $migration) {
      yield $migration;
    }
  }

  private function generateSignature(Migration $migration): string
  {
    $data = [];
    $data[] = $migration->changeset;
    foreach ($migration->queriesCollection as $query) {
      $data[] = $query;
    }

    return md5(base64_encode(implode(' ', $data)));
  }
}