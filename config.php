<?php

require_once 'class/Autoloader.php';

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
	//debug_display
	'showDebug' => TRUE,
	// bool - defines whatever debug info (post, get, session, cookie arrays print_r()) must be shown or not
	'showEnveronmentDebug' => FALSE,
	// bool - defines whatever debug var_dump() function executed on response data
	'showResponseVardump' => FALSE,
	// int - define a lifetime of cookies in seconds
	'cookiesLifetime' => 40000,

);