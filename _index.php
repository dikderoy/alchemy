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

		$storage = new TestModel(1);
		echo "previous stored id:";
		echo $prev_sid = $storage->data;
		$obj = new TestModel();
		echo "\r\nobj after create::\r";
		var_dump($obj);
		$obj->name = 'ObjectName' . uniqid();
		$obj->data = uniqid();
		echo "obj after change::\r";
		var_dump($obj);
		echo "save res::";
		var_dump($obj->save());
		$storage->data = $obj->id;
		$storage->save();

		echo "obj reloaded from db:\r\n";
		$reloaded_obj = new TestModel($obj->id);
		var_dump($reloaded_obj);

		echo "del res::";
		$reloaded_obj = new TestModel($prev_sid);
		var_dump($reloaded_obj->delete());


	} catch (Exception $exc) {
		echo $exc->getTraceAsString();
		echo $exc->getMessage();
	}
?>