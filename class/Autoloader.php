<?php

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
			return TRUE;
		}
		return FALSE;
	}

	public static function autoloadInterface($interface)
	{
		if (!Tools::includeFileIfExists($interface, SYSTEM_ROOT . "/interface_project/", 'php', FALSE)) {
			return Tools::includeFileIfExists($interface, SYSTEM_ROOT . "/interface/", 'php', FALSE);
		}
		return TRUE;
	}

	public static function autoloadController($controller)
	{
		if (!Tools::includeFileIfExists($controller, SYSTEM_ROOT . "/controller_project/", 'php', FALSE)) {
			return Tools::includeFileIfExists($controller, SYSTEM_ROOT . "/controller/", 'php', FALSE);
		}
		return TRUE;
	}

	public static function autoloadLibrary($lib)
	{
		if (!Tools::includeFileIfExists($lib, SYSTEM_ROOT . "/lib_project/", 'php', FALSE)) {
			return Tools::includeFileIfExists($lib, SYSTEM_ROOT . "/lib/", 'php', FALSE);
		}
		return TRUE;
	}

	public static function autoloadView($view)
	{
		if (!Tools::includeFileIfExists($view, SYSTEM_ROOT . "/view_project/", 'php', FALSE)) {
			return Tools::includeFileIfExists($view, SYSTEM_ROOT . "/view/", 'php', FALSE);
		}
		return TRUE;
	}

	public static function autoloadModel($model)
	{
		if (!Tools::includeFileIfExists($model, SYSTEM_ROOT . '/model_project/', 'php', FALSE)) {
			return Tools::includeFileIfExists($model, SYSTEM_ROOT . '/model/', 'php', FALSE);
		}
		return TRUE;
	}

}

spl_autoload_register('Autoloader::autoloadClass', TRUE);
spl_autoload_register('Autoloader::autoloadInterface', TRUE);
spl_autoload_register('Autoloader::autoloadLibrary', TRUE);
spl_autoload_register('Autoloader::autoloadView', TRUE);
spl_autoload_register('Autoloader::autoloadController', TRUE);
spl_autoload_register('Autoloader::autoloadModel', TRUE);