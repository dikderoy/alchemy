<?php

require_once 'config.php';

try {
	Registry::getInstance($system_config);

	Core::getInstance()->init();
	Core::getInstance()->run();
	Core::getInstance()->finish();
} catch (Exception $exc) {
	echo "<pre>".$exc->getTraceAsString()."\r\n".$exc->getMessage()."\r\n";
	var_dump(Core::getInstance(),Registry::getInstance());
}