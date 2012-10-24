<pre>
<?php

	function __autoload($class)
	{
		$path = $_SERVER['DOCUMENT_ROOT']."/class/";
		$file = $path.$class.".php";
		if(file_exists($file)) {
			include_once $file;
		} else {
			throw new Exception("can't autoload class - $class");
		}
	}

	class TestModel extends ObjectModel
	{
		public $data;

		protected $db_table = 'test';
		protected $db_fields = array(
			'id' => 'isValidObjectId',
			'name' => 'isValidObjectName',
			'data' => ''
		);
	}

	try {
		$dbh = Db::getInstance();
		$dbh->setDbParameters('mysql', 'mmvc.ops', 'test', 'utf8', 'mysql', 'mysql');
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