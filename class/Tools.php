<?php

/**
 * Description of Tools
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
		} elseif($throw) {
			throw new Exception("failed to include file `{$fname}`", 404);
		} else {
			return FALSE;
		}
	}

}