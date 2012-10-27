<pre>
<?php

	function __autoload($class)
	{
		$path = $_SERVER['DOCUMENT_ROOT'] . "/class/";
		$file = $path . $class . ".php";
		if (file_exists($file)) {
			include_once $file;
		} else {
			throw new Exception("can't autoload class - $class");
		}
	}

	class TestModel extends ObjectModel
	{

		protected $__dbTable = 'test';
		protected $__dbFields = array(
			'id' => 'isValidObjectId',
			'name' => 'isValidObjectName',
			'data' => ''
		);

		public $data;

	}

	try {
		$a = array(
			// string - defines server address
			'db_server' => 'mmvc.ops',
			// string - defines used db driver (PDO)
			'db_driver' => 'mysql',
			// string - defines DB name
			'db_name' => 'test',
			// string - defines DB login
			'db_login' => 'mysql',
			// string - defines DB password
			'db_password' => 'mysql',
			// string - defines used charset for DBConnection
			'db_charset' => 'utf8',
			// string - defines used charset for HTML
			'site_encoding' => 'utf-8',
			// string - defines used DOCTYPE
			'html_doctype' => 'html5',
			// bool - defines access_control enabled on main page or not
			'main_access_restricted' => FALSE,
			// bool - defines whatever debug info (post, get, session, cookie arrays print_r()) must be shown or not
			'show_enveronment_debug' => FALSE,
			// bool - defines whatever debug var_dump() function executed on response data
			'show_response_vardump' => TRUE,
			// int - define a lifetime of cookies in seconds
			'cookies_lifetime' => 40000,
		);

		$c = SystemConfig::getInstance($a);

		$cc = new Config($a);
		var_dump($c,$cc);

		$dbh = Db::getInstance();
		$dbh->setDbParameters($c->db_driver, $c->db_server, $c->db_name, $c->db_charset, $c->db_login, $c->db_password);
		var_dump($dbh);


		  $obj = new TestModel(1);
		  var_dump($obj);
		  $obj->data = uniqid();
		  $obj->save();


		$reloaded_obj = new TestModel(1);
		var_dump($reloaded_obj);
	} catch (Exception $exc) {
		echo $exc->getTraceAsString();
		echo $exc->getMessage();
	}

?>
</pre>