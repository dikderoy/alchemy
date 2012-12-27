<?php

define('SYSTEM_ROOT', dirname(dirname(__FILE__)));

/**
 * Autoloader is a collection of functions used ONLY as class loaders by spl_autoload()
 *
 * @author Deroy
 */
class Autoloader
{

	public static function autoloadClass($class)
	{
		$path = SYSTEM_ROOT . "/class/";
		$file = $path . $class . ".php";
		if (file_exists($file)) {
			include_once $file;
		}
	}

	public static function autoloadInterface($interface)
	{
		Tools::includeFileIfExists($interface, SYSTEM_ROOT . "/interface/", 'php', FALSE);
	}

	public static function autoloadController($controller)
	{
		Tools::includeFileIfExists($controller, SYSTEM_ROOT . "/controller/", 'php', FALSE);
	}

	public static function autoloadLibrary($lib)
	{
		Tools::includeFileIfExists($lib, SYSTEM_ROOT . "/lib/", 'php', FALSE);
	}

	public static function autoloadView($view)
	{
		Tools::includeFileIfExists($view, SYSTEM_ROOT . "/view/", 'php', FALSE);
	}

	public static function autoloadModel($model)
	{
		Tools::includeFileIfExists($model, SYSTEM_ROOT . '/model/', 'php', FALSE);
	}

}

spl_autoload_register('Autoloader::autoloadClass', TRUE);
spl_autoload_register('Autoloader::autoloadInterface', TRUE);
spl_autoload_register('Autoloader::autoloadLibrary', TRUE);
spl_autoload_register('Autoloader::autoloadView', TRUE);
spl_autoload_register('Autoloader::autoloadController', TRUE);
spl_autoload_register('Autoloader::autoloadModel', TRUE);