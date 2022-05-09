<?php

namespace gleamlite\platform;

class Bootstrap
{

	public static function iniSetIncludePath(): void
	{
		ini_set('include_path', implode(PATH_SEPARATOR, [
			__ROOT__ . 'modules',
			__ROOT__ . 'src',
			ini_get('include_path')
		]));

		require('Autoloader.php');

		Autoloader::register();
	}
}
