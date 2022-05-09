<?php

namespace gleamlite\utils;

use Exception;

class SecurityUtils
{
  const DEFAULT_SALT = '3b16253cc2eef0463d5a0f459caf0645ca23286373bea60d60612f99f386c1b3';

  public static function createGuid(): string
  {
    $data = openssl_random_pseudo_bytes(32);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0010
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));
  }

  public static function createUUID(): string
  {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0x0fff) | 0x4000,
      mt_rand(0, 0x3fff) | 0x8000,
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
  }

  public static function cleanHTML(string $html): string
  {
    return strip_tags($html);
  }

  public static function cleanEmail(string $email): string
  {
    return filter_var($email, FILTER_SANITIZE_EMAIL);
  }

  public static function isValidEmail(string $email): bool
  {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
  }

  public static function isInvalidEmail($email): bool
  {
    return (!self::isValidEmail($email));
  }

  public static function passwordSaltWrapper(string $password): string
  {
    $salt = self::DEFAULT_SALT;
    return "{$salt}.{$password}.{$salt}";
  }

  public static function passwordBuilder(string $password, string $salt = null): string
	{
		$firstLayer = md5($password);
		if (StringUtils::isNull($salt)) {
			return $firstLayer;
		}

		return md5(sprintf('%s.%s.%s', $salt, $firstLayer, $salt));
	}

  public static function stringNullThrowing(string $errorMessage, string $needle = null): void
	{
		if (StringUtils::isNull($needle)) {
      throw new Exception($errorMessage);
		}
	}

  public static function emailInvalidOrNullThrowing(array $options, string $needle = null): void
	{
		self::stringNullThrowing($options['null'], $needle);

		if (SecurityUtils::isInvalidEmail(SecurityUtils::cleanEmail($needle))) {
      throw new Exception($options['invalid']);
		}
	}

  public static function validateIdentifierThrowing(string $message, string $id): void
	{
    if ($id != '0' && (strlen($id) < 32)) {
      throw new Exception($message);
    }
	}
  
}