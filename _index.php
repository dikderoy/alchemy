<pre>
	<?php
	require_once 'config.php';

	class TestModel extends ObjectModel
	{

		protected $__dbTable = 'test';
		protected $__dbFields = array(
			'id',
			'name',
			'data'
		);
		protected $__dbFieldsValidators = array(
			'id' => 'isValidObjectId',
			'name' => 'isValidObjectName',
			'data' => ''
		);
		public $id;
		public $name;
		public $data;

	}

	try {
		$c = Registry::getInstance($system_config);

		//var_dump($c);

		Db::getInstance($c);
		//var_dump(Db::getInstance());


		$obj = new TestModel(10);
		echo "obj after create::\r\n";
		var_dump($obj);
		$obj->name = 'ObjectName' . uniqid();
		$obj->data = uniqid();
		echo "obj after change::\r\n";
		var_dump($obj);
		echo "save res::";
		var_dump($obj->save(array('data')));

		echo "obj reloaded from db:\r\n";
		$reloaded_obj = new TestModel(10);
		var_dump($reloaded_obj);
	} catch (Exception $exc) {
		echo $exc->getTraceAsString();
		echo $exc->getMessage();
	}
	?>
</pre>