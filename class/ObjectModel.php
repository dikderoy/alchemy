<?php

/**
 * Description of ObjectModel
 *
 * @author Deroy
 */
abstract class ObjectModel
{

	/**
	 * defines key of identificator field related to db e.g. name of PRIMARY_ID db field
	 * @var string
	 */
	protected $identificator = 'id';

	/**
	 * defines whatever object was loaded from DB or just created in PHP
	 * e.g. does object have its copy in DB or not
	 * @var bool
	 */
	protected $__isLoadedObject = FALSE;

	/**
	 * defines name of table in DB to wich object is(or must be) releated(saved to)
	 * @var string
	 */
	protected $__dbTable;

	/**
	 * defines fields of object wich are releated to DB entry
	 * only fields in this list can be saved to DB as entry
	 * @var array
	 */
	protected $__dbFields = array(
		'id',
	);

	/**
	 * defines validators for DB-releated fiedls from $__dbFields
	 * keys must match field names and values must match static method names of Validate class
	 * @uses Validate collection of validators
	 * @var array
	 */
	protected $__dbFieldsValidators = array(
		'id' => 'isValidObjectId',
	);

	public function __construct($id = NULL)
	{
		if (isset($this->__dbFieldsValidators[$this->identificator])) {
			if (call_user_func('Validate::' . $this->__dbFieldsValidators[$this->identificator], $id)) {
				$error = FALSE;
			} else {
				$error = TRUE;
			}
		}
		if (!$error) {
			$statement = Db::getInstance()->select($this->__dbFields)->from($this->__dbTable)->where_complex("id = ?")->limit(1)->_exec(TRUE);
			if (Db::getInstance()->fetchIntoObject($statement, $this, array($id))) {
				$this->__isLoadedObject = TRUE;
			} else {
				$this->{$this->identificator} = $id;
			}
		}
	}

	/**
	 * add new object entry to DB
	 * @uses Db DB control class
	 * @param array $fields - array containing field names - set this if you wish to save only specified fields
	 * @return boolean
	 * @throws Exception
	 */
	public function add($fields = NULL)
	{
		$error_fields = array();
		$fields = $this->validateFields($fields, FALSE, $error_fields);
		if ($fields == FALSE) {
			throw new Exception(__METHOD__ . " fields: [" . implode(',', $error_fields) . "] do not pass validation");
		}
		$result = Db::getInstance()->insert($fields)->into($this->__dbTable)->limit(1)->_exec();
		if ($result instanceof PDOStatement && $result->rowCount() > 0) {
			$this->id = Db::getLastInsertId();
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * updates existing db entry
	 * @uses Db DB control class
	 * @param array $fields - array containing field names - set this if you wish to update only specified fields
	 * @return boolean
	 * @throws Exception
	 */
	public function update($fields = NULL)
	{
		if (empty($this->{$this->identificator})) {
			return FALSE;
		}
		$error_fields = array();
		$fields = $this->validateFields($fields, FALSE, $error_fields);
		if ($fields == FALSE) {
			throw new Exception(__METHOD__ . " fields: [" . implode(',', $error_fields) . "] do not pass validation");
		}
		$result = Db::getInstance()->update($this->__dbTable)->set($fields)->where_complex("id = {$this->id}")->limit(1)->_exec();
		if ($result instanceof PDOStatement && $result->rowCount() > 0) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * add or update entry in DB - intellyguess
	 * @param array $fields - array containing field names - set this if you wish to save only specified fields
	 * @return boolean
	 */
	public function save($fields = NULL)
	{
		if (empty($this->__dbTable) || empty($this->__dbFields)) {
			return FALSE;
		}
		if (empty($this->{$this->identificator})) {
			return $this->add($fields);
		} elseif ($this->__isLoadedObject && !empty($this->{$this->identificator})) {
			return $this->update($fields);
		} elseif (!$this->__isLoadedObject && !empty($this->{$this->identificator})) {
			return $this->add($this->__dbFields);
		}
		return FALSE;
	}

	/**
	 * delete object entry from DB
	 * @uses Db DB control class
	 * @return boolean
	 */
	public function delete()
	{
		$result = Db::getInstance()->delete($this->__dbTable)->where_complex("id = {$this->{$this->identificator}}")->limit(1)->_exec();
		if ($result instanceof PDOStatement && $result->rowCount() > 0) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * validates fields before add() and update() operations
	 * @param array $fields - fields, values of wich should be validated
	 * @param boolean $with_id (defaults to TRUE) - defines whatever to validate and include identificator field or not
	 * @param array $error - contain field names wich does not passed validation
	 * @return boolean|array - array of fields to operate over if they passed validation, FALSE overwise
	 */
	public function validateFields($fields = NULL, $with_id = TRUE, &$error = NULL)
	{
		//set actual fields array
		$fields = (empty($fields)) ? $this->__dbFields : $fields;

		$error = array();
		$success = array();
		foreach ($fields as $field) {
			if (!empty($this->__dbFieldsValidators[$field])) {
				if (!call_user_func("Validate::" . $this->__dbFieldsValidators[$field], $this->{$field})) {
					array_push($error, $field);
				} else {
					$success[$field] = $this->{$field};
				}
			} else {
				$success[$field] = $this->{$field};
			}
		}
		if ($with_id && call_user_func("Validate::" . $this->__dbFieldsValidators[$this->identificator], $this->{$this->identificator})) {
			$success[$this->identificator] = $this->{$this->identificator};
		}

		if (empty($error)) {
			return $success;
		}

		return FALSE;
	}

	/**
	 * converts object to array using $__dbFields list of field names
	 * @return array
	 */
	public function __toArray()
	{
		$array = array();

		foreach ($this->__dbFields as $field => $value) {
			$array[$field] = $this->{$field};
		}

		return $array;
	}

}