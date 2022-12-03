<?php

namespace emerus\core;

use PDO;

class DataFactory
{

  /**
   * @var constant
   */
  const C_DEFAULT_DB = 'default';

  /**
   * @var DataFactory
   */
  private static $instance = null;

  /**
   * @var PDO
   */
  private $databaseConnections = null;

  /**
   * construction is not possible. This is prevented because this object is a singleton
   *
   * @return void
   */
  private function __construct()
  {
    // disable instantiation
  }

  /**
   * get the instance of the datafactory
   *
   * @return DataFactory
   */
  public static function getInstance()
  {
    if (self::$instance === null) {
      self::$instance = new DataFactory();
    }
    return self::$instance;
  }

  /**
   * Set the database connection for the Facade.
   *
   * @param PDO $connection
   */
  public function getConnection(string $databasename = null)
  {

    if ($this->databaseConnections == null) {
      throw new DataFactoryException('Database connection is not set');
    }

    if ($databasename === null) {
      $databasename = self::C_DEFAULT_DB;
    }

    if (!isset($this->databaseConnections[$databasename])) {
      throw new DataFactoryException('Database connection is not set for ' . $databasename);
    }

    return $this->databaseConnections[$databasename];
  }

  /**
   * Add a PDO connection to the factory.
   * It will always set the erromode of the PDO object to throw Exceptions
   *
   * @param $conn
   * @param $databaseName
   * @return unknown_type
   */
  public function addConnection(PDO $connection, $databaseName = null)
  {
    if ($databaseName === null) {
      $databaseName = self::C_DEFAULT_DB;
    }

    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $this->databaseConnections[$databaseName] = $connection;
  }

  public function beginTransaction($databasename = null)
  {
    $this->getConnection($databasename)->beginTransaction();
  }

  public function commit($databasename = null)
  {
    $this->getConnection($databasename)->commit();
  }

  public function rollBack($databasename = null)
  {
    $this->getConnection($databasename)->rollBack();
  }
}
