<pre>
<?php
	require_once 'config.php';

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
		$c = Registry::getInstance($system_config);

		var_dump($c);

		Db::getInstance($c);
		var_dump(Db::getInstance());


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