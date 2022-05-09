<?php

namespace gleamlite\platform;

class Autoloader
{

	public static function load(string $class)
	{
		$classFile = str_replace('\\', DS, $class) . __PHP;
		$file = stream_resolve_include_path($classFile);
		if ($file && file_exists($file)) {
			require_once($file);
			return true;
		}
		return false;
	}

	public static function register()
	{
		return spl_autoload_register('\gleamlite\platform\Autoloader::load');
	}
}
