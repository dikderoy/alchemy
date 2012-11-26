<?php

function autoloadClass($class)
{
	$path = dirname(__FILE__) . "/class/";
	$file = $path . $class . ".php";
	if (file_exists($file)) {
		include_once $file;
	}
}

function autoloadInterface($interface)
{
	Tools::includeFileIfExists($interface, dirname(__FILE__) . "/interface/", 'php', FALSE);
}

function autoloadController($controller)
{
	Tools::includeFileIfExists($controller, dirname(__FILE__) . "/controller/", 'php', FALSE);
}

spl_autoload_register('autoloadClass', TRUE);
spl_autoload_register('autoloadInterface', TRUE);
spl_autoload_register('autoloadController', TRUE);

$system_config = array(
	'rootDirectory' => dirname(__FILE__),
	// string - defines server address
	'dbServer' => 'mmvc.ops',
	// string - defines used db driver (PDO)
	'dbDriver' => 'mysql',
	// string - defines DB name
	'dbName' => 'test',
	// string - defines DB login
	'dbLogin' => 'mysql',
	// string - defines DB password
	'dbPassword' => 'mysql',
	// string - defines used charset for DBConnection
	'dbCharset' => 'utf8',
	// string - defines used charset for HTML
	'siteEncoding' => 'utf-8',
	// string - defines used DOCTYPE
	'htmlDoctype' => 'html5',
	// bool - defines access_control enabled on main page or not
	'main_access_restricted' => FALSE,
	// bool - defines whatever debug info (post, get, session, cookie arrays print_r()) must be shown or not
	'showEnveronmentDebug' => FALSE,
	// bool - defines whatever debug var_dump() function executed on response data
	'showResponseVardump' => TRUE,
	// int - define a lifetime of cookies in seconds
	'cookiesLifetime' => 40000,
);