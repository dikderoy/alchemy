<?php

/**
 * Collection of different Tools
 * as static methods
 *
 * @author Deroy
 */
class Tools
{

	/**
	 * includes file in runtime
	 * @param string $fname - file name
	 * @param string $path - defaults to APP_ROOT DIR
	 * @param string $extension - defaults to PHP
	 * @param bool $throw - defines whatever to throw exception or just return FALSE on failure
	 * @return bool
	 * @throws Exception - if file not found
	 */
	public static function includeFileIfExists($fname, $path = NULL, $extension = 'php', $throw = TRUE)
	{
		if (!$path) {
			$path = Registry::getInstance()->rootDirectory;
		}
		if (file_exists($path . $fname . '.' . $extension)) {
			include_once $path . $fname . '.' . $extension;
			return TRUE;
		} elseif ($throw) {
			throw new Exception("failed to include file `{$fname}`", 404);
		}
		return FALSE;
	}

}