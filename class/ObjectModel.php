<?php

/**
 * Description of ObjectModel
 *
 * @author Deroy
 */
abstract class ObjectModel
{

	public $id;
	public $name;
	protected $__isLoadedObject = FALSE;
	protected $__dbTable;
	protected $__dbFields = array(
		'id' => 'isValidObjectId',
		'name' => 'isValidObjectName'
	);

	public function __construct($id = NULL)
	{
		if (Validate::isValidObjectId($id)) {
			$statement = Db::getInstance()->select(array_keys($this->__dbFields))->from($this->__dbTable)->where("id = ?")->limit(1)->_exec(TRUE);
			/*$statement->setFetchMode(PDO::FETCH_INTO, $this);
			$statement->execute(array($id));
			$statement->fetch();
			 */
			Db::getInstance()->fetchIntoObject($statement, $this,array($id));
			$this->__isLoadedObject = TRUE;
		}
	}

	public function add()
	{
		$fields = $this->validateFields($error_fields, TRUE);
		if ($fields == FALSE) {
			throw new Exception(__METHOD__ . " fields: [" . implode(',', $error_fields) . "] do not pass validation");
		}
		$result = Db::getInstance()->insert($fields)->into($this->__dbTable)->limit(1)->_exec();
		if ($result instanceof PDOStatement && $result->rowCount() > 0) {
			$this->id = Db::lastInsertId();
			return TRUE;
		}
		return FALSE;
	}

	public function update()
	{
		if (empty($this->id)) {
			return FALSE;
		}
		$fields = $this->validateFields($error_fields);
		if ($fields == FALSE) {
			throw new Exception(__METHOD__ . " fields: [" . implode(',', $error_fields) . "] do not pass validation");
		}
		$result = Db::getInstance()->update($this->__dbTable)->set($fields)->where("id = {$this->id}")->limit(1)->_exec();
		if ($result instanceof PDOStatement && $result->rowCount() > 0) {
			return TRUE;
		}
		return FALSE;
	}

	public function save()
	{
		if (empty($this->__dbTable) || empty($this->__dbFields)) {
			return FALSE;
		}
		if (empty($this->id)) {
			return $this->add();
		} elseif ($this->__isLoadedObject) {
			return $this->update();
		}
		return FALSE;
	}

	public function delete()
	{
		$result = Db::getInstance()->delete($this->__dbTable)->where("id = {$this->id}")->limit(1)->_exec();
		if ($result instanceof PDOStatement && $result->rowCount() > 0) {
			return TRUE;
		}
		return FALSE;
	}

	public function validateFields(&$error = NULL, $no_id = FALSE)
	{
		$error = array();
		$success = array();
		foreach ($this->__dbFields as $field => $validator) {
			if ($field == 'id' && $no_id) {
				continue;
			}

			if (!empty($validator)) {
				if (!call_user_func("Validate::$validator", $this->{$field})) {
					array_push($error, $field);
				} else {
					$success[$field] = $this->{$field};
				}
			} else {
				$success[$field] = $this->{$field};
			}
		}

		if (empty($error)) {
			return $success;
		}

		return FALSE;
	}

	public function __toArray()
	{
		$array = array();

		foreach ($this->__dbFields as $field => $value) {
			$array[$field] = $this->{$field};
		}

		return $array;
	}

}