<?php

namespace emerus\utils;

class ObjectUtils
{

	public static function isArray($object): bool
	{
		return is_array($object);
	}

	public static function isEmpty(array $object = []): bool
	{
		return (empty($object));
	}

	public static function isNotEmpty(array $object = []): bool
	{
		return (!self::isEmpty($object));
	}

	public static function isNull($object = null): bool
	{
		return is_null($object);
	}

	public static function isNotNull($object = null): bool
	{
		return (!self::isNull($object));
	}

	public static function containsElement(array $array, string $element): bool
	{
		$response = false;
		if (empty($array)) {
			return $response;
		}

		foreach ($array as $arrayElement) {
			if ($arrayElement !== $element) {
				continue;
			}

			$response = true;
			break;
		}
		return $response;
	}

	public static function keyExists(array $array, string $key): bool
	{
		return array_key_exists($key, $array);
	}

	public static function generateUuid(): string
	{
		return sprintf(
			'%s-%s-%04x-%04x-%s',
			bin2hex(openssl_random_pseudo_bytes(4)),
			bin2hex(openssl_random_pseudo_bytes(2)),
			hexdec(bin2hex(openssl_random_pseudo_bytes(2))) & 0x0fff | 0x4000,
			hexdec(bin2hex(openssl_random_pseudo_bytes(2))) & 0x3fff | 0x8000,
			bin2hex(openssl_random_pseudo_bytes(6))
		);
	}

	public static function join(array $left, array $right): array
	{
		return array_merge($left, $right);
	}
}
