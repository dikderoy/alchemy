<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Deroy
 * Date: 04.01.13
 * Time: 12:44
 */
class UniversalObjectModelEditor
{
	/**
	 * object from which object info is extracted
	 * @var ObjectModel
	 */
	public $infoObject;

	/**
	 * class to work with
	 * @var string
	 */
	public $className;

	/**
	 * @param $className - name of class to work with
	 */
	function __construct($className)
	{
		if (!Autoloader::autoloadModel($className)) {
			throw new ControllerActionError('ObjectModel you requested does not exists');
		}
		$this->infoObject = new $className();
		if ($this->infoObject instanceof ObjectModel) {
			$this->className = get_class($this->infoObject);
		} else {
			throw new ControllerActionError('Class name you provided does not extends ObjectModel class');
		}
	}

	/**
	 * get editable object
	 * @param string|integer $objectID
	 * @return ObjectModel
	 */
	public function getObject($objectID = NULL)
	{
		return new $this->className($objectID);
	}

	/**
	 * returns array of objects registered in database if any
	 * @param null|array $fields fields to show
	 * @return bool|array
	 */
	public function getObjectsList($fields = NULL)
	{
		$idFieldName = $this->infoObject->getIdFieldName();
		if (!in_array($idFieldName, $fields)) {
			array_push($fields, $idFieldName);
		}
		$list = Db::select($fields)->from($this->infoObject->getDbTable())->execute()->fetchArrayCollection();
		return $list;
	}

	/**
	 * returns object structure info
	 * @return array
	 */
	public function getObjectFields()
	{
		$fields = $this->infoObject->getFieldDefinitions();
		$data = array();
		foreach ($fields as $field => $properties) {
			$info = array(
				'required' => $properties[ObjectModel::FP_REQUIRED],
				'type'     => $this->typeToStr($properties[ObjectModel::FP_TYPE]),
				'size'     => $properties[ObjectModel::FP_SIZE]
			);
			$data[$field] = $info;
		}
		return $data;
	}

	/**
	 * returns string (textual) representation of type by code given
	 * @param $code - code value of type used inside ObjectModel class
	 * @return string
	 */
	protected function typeToStr($code)
	{
		switch ($code) {
			case ObjectModel::F_TYPE_INT:
				$str = 'integer';
				break;
			case ObjectModel::F_TYPE_STRING:
				$str = 'string';
				break;
			case ObjectModel::F_TYPE_BOOL:
				$str = 'boolean';
				break;
			case ObjectModel::F_TYPE_ARRAY:
				$str = 'array';
				break;
			case ObjectModel::F_TYPE_OBJECT:
				$str = 'object';
				break;
			case ObjectModel::F_TYPE_ANY:
				$str = 'any';
				break;
			default:
				$str = 'not defined';
		}
		return $str;
	}

	/**
	 * assign fields values from $data to ObjectModel instance
	 * saves them to Db (using save() method)
	 * returns object after all
	 * @param array $data
	 * @return ObjectModel
	 */
	public function saveChanges($data)
	{
		//check for validity of field names in $data
		$fields = array_intersect_key($data, $this->infoObject->getFieldDefinitions());

		$idFieldName = $this->infoObject->getIdFieldName();
		if (empty($fields[$idFieldName])) {
			$o = $this->getObject();
		} else {
			$o = $this->getObject($fields[$idFieldName]);
		}

		foreach ($fields as $field => $value) {
			$o->{$field} = $value;
		}
		$o->save();

		return $o;
	}
}