<?php

/**
 * Description of ObjectModel
 *
 * @author Deroy
 */
abstract class ObjectModel
{

	protected $isLoadedObject = FALSE;
	protected $db_table;
	protected $db_fields = array(
		'id' => 'isValidObjectId',
		'name' => 'isValidObjectName'
	);
	public $id;
	public $name;

	public function __construct($id = NULL)
	{
		if (Validate::isValidObjectId($id)) {
			$statement = Db::getInstance()->select(array_keys($this->db_fields))->from($this->db_table)->where("id = ?")->limit(1)->_exec(TRUE);
			$statement->setFetchMode(PDO::FETCH_INTO, $this);
			$statement->execute(array($id));
			$statement->fetch();
			$this->isLoadedObject = TRUE;
		}
	}

	public function add()
	{
		$fields = $this->validateFields($error_fields, TRUE);
		if ($fields == FALSE) {
			throw new Exception(__METHOD__ . " fields: [" . implode(',', $error_fields) . "] do not pass validation");
		}
		$result = Db::getInstance()->insert($fields)->into($this->db_table)->limit(1)->_exec();
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
		$result = Db::getInstance()->update($this->db_table)->set($fields)->where("id = {$this->id}")->limit(1)->_exec();
		if ($result instanceof PDOStatement && $result->rowCount() > 0) {
			return TRUE;
		}
		return FALSE;
	}

	public function save()
	{
		if (empty($this->db_table) || empty($this->db_fields)) {
			return FALSE;
		}
		if (empty($this->id)) {
			return $this->add();
		} elseif ($this->isLoadedObject) {
			return $this->update();
		}
		return FALSE;
	}

	public function delete()
	{
		$result = Db::getInstance()->delete($this->db_table)->where("id = {$this->id}")->limit(1)->_exec();
		if ($result instanceof PDOStatement && $result->rowCount() > 0) {
			return TRUE;
		}
		return FALSE;
	}

	public function validateFields(&$error = NULL, $no_id = FALSE)
	{
		$error = array();
		$success = array();
		foreach ($this->db_fields as $field => $validator) {
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

		foreach ($this->db_fields as $field => $value) {
			$array[$field] = $this->{$field};
		}

		return $array;
	}

}

?>
