<?php

define('DS', DIRECTORY_SEPARATOR);
define('__ROOT__', __DIR__ . DS);
define('__PHP', '.php');
define('APPLICATION_PATH', __DIR__);

require('./modules/gleamlite/platform/Bootstrap.php');
\gleamlite\platform\Bootstrap::iniSetIncludePath();

if (!file_exists('./config.json')) {
  echo "No Configuration File Found.";
  die;
}

function loadConfiguration(string $filename): array
{
  $fileContents = '';
  if (!file_exists($filename)) {
    throw new Exception("No se encuentra el archivo {$filename}.");
  }

  if (!$fileContents = file_get_contents($filename)) {
    throw new Exception("El archivo {$filename} se encuentra vac�o.");
  }

  $response = json_decode($fileContents, true);

  $errorsCollection = [
    JSON_ERROR_DEPTH => "Máxima anidación excedida en el archivo: {$filename}",
    JSON_ERROR_STATE_MISMATCH => "Los modos no coinciden en el archivo: {$filename}",
    JSON_ERROR_CTRL_CHAR => "Carácter de control inesperado en el archivo: {$filename}",
    JSON_ERROR_SYNTAX => "La sintaxis del archivo {$filename} está malformada.",
    JSON_ERROR_UTF8 => "Error de codificación UTF-8 en el archivo: {$filename}"
  ];

  if (array_key_exists(json_last_error(), $errorsCollection)) {
    $message = $errorsCollection[json_last_error()] ?? 'Ha ocurrido un error inesperado en la inicialización';
    throw new Exception($message);
  }

  return $response;
}

$configurations = [];
try {
  $configurations = loadConfiguration('config.json');
}
catch (Exception $error) {
  echo $error->getMessage();
  die;
}

require('./modules/gleamlite/GleamLite.php');
\gleamlite\GleamLite::start($configurations);
