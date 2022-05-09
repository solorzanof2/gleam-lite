<?php

namespace emerus;

use emerus\core\DataFactory;
use PDO;
use PDOException;

try {
	$dsn = sprintf('%s:host=%s;dbname=%s', $db['dbdriver'], $db['hostname'], $db['database']);

	$instance = new PDO($dsn, $db['username'], $db['password'], [PDO::ATTR_PERSISTENT => TRUE, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
	$instance->exec('SET NAMES UTF8');
	
	DataFactory::getInstance()->addConnection($instance, DataFactory::C_DEFAULT_DB);
}
catch (PDOException $error) {
	echo $error->getMessage() . "<br>";
	echo "Something went wrong with database connection <br /> \n";
	die;
}
