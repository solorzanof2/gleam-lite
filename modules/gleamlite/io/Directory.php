<?php

namespace gleamlite\io;

class Directory
{

  const LOG_FOLDER = __ROOT__ . "storage/logs/";

  private $directoryProperties = [
    "directoryReadMode" => "0755",
    "directoryWriteMode" => "0777"
  ];

  private $filesProperties = [
    "fileReadMode" => "0644",
    "fileWriteMode" => "0666",
    "fopenRead" => "rb",
    "fopenWrite" => "r+b",
    "fopenWriteCreateDesctrutive" => "wb",
    "fopenReadWriteCreateDestructive" => "w+b",
    "fopenWriteCreate" => "ab",
    "fopenReadWriteCreate" => "a+b",
    "fopenWriteCreateStrict" => "xb",
    "fopenReadWriteCreateStrict" => "x+b",
    "imageExtensions" => ["jpeg", "jpg", "png", "svg"],
    "resourceExtensions" => ["css", "js", "txt", "csv", "xml", "html", "htm", "rss", "vcard", "appcache", "ttf", "woff", "woff2", "eot", "map"],
    "forbidden" => ["tmp", "log", "ht", "htaccess", "pem", "crt", "db", "sql", "version", "conf", "ini", "empty", "txt", "json"]
  ];

  private static $instance;

  private function __construct()
  {
  }

  public static function getInstance(): Directory
  {
    if (is_null(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function fileExists(string $filename): bool
  {
    return file_exists($filename);
  }

  public function directoryExists(string $directory): bool
  {
    return is_dir($directory);
  }

  public function getCreatedTime(string $filename): int
  {
    return filemtime($filename);
  }

  public function getContents(string $filename)
  {
    return file_get_contents($filename);
  }

  public function write(string $filename, string $fileContent, string $chmodKey = 'fopenReadWriteCreate'): void
  {
    if (!$filePrint = @fopen($filename, $this->filesProperties[$chmodKey])) {
      throw new DirectoryException("(write) Could not open file {$filename};");
    }

    flock($filePrint, LOCK_EX);
    fwrite($filePrint, $fileContent);
    flock($filePrint, LOCK_UN);
    fclose($filePrint);

    $this->chmodHandler($filename, 'fileWriteMode');
  }

  public function writeLog(string $filename, string $fileContent, string $chmodKey = 'fopenReadWriteCreate'): void
  {
    $logfile = self::LOG_FOLDER . $filename;
    $this->createDirectory(self::LOG_FOLDER);
    $this->write($logfile, $fileContent, $chmodKey);
  }

  public function createDirectory(string $directory): bool
  {
    if (!$this->directoryExists($directory)) {
      if (mkdir($directory, 0777, true)) {
        $this->chmodHandler($directory, 'directoryWriteMode', true);
        return true;
      }
    }
    return false;
  }

  private function chmodHandler(string $filename, string $chmodKey, bool $isDirectory = false): void
  {
    $octalValue = ($isDirectory) ? $this->directoryProperties[$chmodKey] : $this->filesProperties[$chmodKey];
    @chmod($filename, (int) $octalValue);
  }
}
