<?php

/**
 * ActiveRecord presentation class
 *
 * @category Object Representation Set
 * @package Alchemy Framework
 * @version 2.0.0
 * @author Deroy aka Roman Bulgakov
 * @uses Db Database control set
 */
abstract class ObjectModel
{
	/* field's property names for definition collection */

	const FP_VALIDATOR = 1;
	const FP_PACKAGER = 2;
	const FP_UNPACKER = 3;
	const FP_TYPE = 4;
	const FP_SIZE = 5;
	const FP_REQUIRED = 6;

	/* type definitions */
	const F_TYPE_INT = 7;
	const F_TYPE_STRING = 8;
	const F_TYPE_BOOL = 9;
	const F_TYPE_ARRAY = 10;
	const F_TYPE_OBJECT = 11;
	const F_TYPE_ANY = 12;

	/**
	 * defines key of identifier field related to db e.g. name of PRIMARY_ID db field
	 * @var string
	 */
	protected $identifier = 'id';

	/**
	 * defines whatever object was loaded from DB or just created in PHP
	 * e.g. does object have its copy in DB or not
	 * @var bool
	 */
	protected $__isLoadedObject = FALSE;

	/**
	 * defines name of table in DB to which object is(or must be) related(saved to)
	 * @var string
	 */
	protected $__dbTable;

	/**
	 * defines fields of object which are related to DB entry
	 * only fields in this list can be saved to DB as entry
	 * @var array
	 */
	protected $__dbFields = array();

	/**
	 * holds definitions for each DB related field of object
	 * validation and preparation behavior depends on this property
	 * @var array collection
	 */
	protected $__fieldDefinitions = array(
		'id' => array(
			self::FP_TYPE => self::F_TYPE_INT,
			self::FP_SIZE => 64,
			self::FP_VALIDATOR => 'isValidObjectId',
			self::FP_REQUIRED => TRUE
		)
	);

	/**
	 * constructs new object,
	 * if $id given - tries to load related entry from DB
	 * @param mixed $id
	 */
	public function __construct($id = NULL)
	{
		//retrieve list of field names defined as db-related
		$this->__dbFields = array_keys($this->__fieldDefinitions);
		//load data if exists
		if ($this->onConstructCheck($id)) {
			$this->load($id);
		}
	}

	/**
	 * returns object identifier field value
	 * @return string|integer
	 */
	public function getId()
	{
		return $this->{$this->identifier};
	}

	/**
	 * returns object identifier field name
	 * @return string
	 */
	public function getIdFieldName()
	{
		return $this->identifier;
	}

	/**
	 * returns table name which object is related to
	 * @return string
	 */
	public function getDbTable()
	{
		return $this->__dbTable;
	}

	/**
	 * returns field definitions array of object
	 * @return array
	 */
	public function getFieldDefinitions()
	{
		return $this->__fieldDefinitions;
	}



	/**
	 * use this if you want to properly load object
	 * which has protected or private db-related properties
	 * @param string $id
	 * @return ObjectModel
	 */
	public static function protectedInstanceLoad($id = NULL)
	{
		//create a blank instance of class to get access to properties
		$propertyStack = new static();
		//perform ID param check
		if ($propertyStack->onConstructCheck($id)) {
			$res = Db::select()->from($propertyStack->__dbTable)->where(array($propertyStack->identifier => $id))->limit(1)->execute();
			$instance = $res->fetchObject(get_called_class());
			if ($instance instanceof static) {
				$instance->__isLoadedObject = TRUE;
				$instance->unpackFields();
				return $instance;
			}
		}
		return new static();
	}

	/**
	 * check performed on object construction to prevent search with wrong ID values
	 * @param mixed $id
	 * @return boolean
	 */
	protected function onConstructCheck($id = NULL)
	{
		if (empty($id)) {
			return FALSE;
		} elseif (isset($this->__fieldDefinitions[$this->identifier][self::FP_VALIDATOR])) {
			return call_user_func('Validate::' . $this->__fieldDefinitions[$this->identifier][self::FP_VALIDATOR], $id);
		}
		return TRUE;
	}

	/**
	 * load procedure performed on construct
	 * @param mixed $id
	 */
	protected function load($id = NULL, DbQuery $query = NULL)
	{
		if(empty($query)) {
			$query = Db::select($this->__dbFields)->from($this->__dbTable)->where(array($this->identifier => $id))->limit(1);
		}
		$query->execute();
		$data = $query->fetchArray();
		if (is_array($data) && count($data)>=count($this->__dbFields)) {
			foreach($data as $field => $value){
				$this->{$field}=$value;
			}
			//if ok set object as loaded and restore fields structure
			$this->__isLoadedObject = TRUE;
			$this->unpackFields();
		} else {
			//just assign id to identifier field
			$this->{$this->identifier} = $id;
		}
	}

	/**
	 * add new object entry to DB
	 * @uses Db DB control class
	 * @param array $fields - array containing field names - set this if you wish to save only specified fields
	 * @param boolean $includeID - whatever to save custom ID to DB
	 * @return boolean
	 * @throws Exception
	 */
	public function add($fields = NULL, $includeID = FALSE)
	{
		$fields = $this->getPreparedFieldCollection($fields, $includeID);
		$result = Db::insert($fields)->into($this->__dbTable)->limit(1)->execute();
		if ($result->rowsAffected() > 0) {
			$this->{$this->identifier} = Db::getLastInsertId();
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
		if (empty($this->{$this->identifier})) {
			return FALSE;
		}
		$fields = $this->getPreparedFieldCollection($fields, FALSE);
		$result = Db::update($this->__dbTable)->set($fields)->where(array($this->identifier => $this->{$this->identifier}))->limit(1)->execute();
		if ($result->rowsAffected() > 0) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * add or update entry in DB - intellectual guess
	 * @param array $fields - array containing field names - set this if you wish to save only specified fields
	 * @return boolean
	 */
	public function save($fields = NULL)
	{
		if (empty($this->__dbTable) || empty($this->__dbFields)) {
			return FALSE;
		}
		if (empty($this->{$this->identifier})) {
			return $this->add($fields);
		} elseif ($this->__isLoadedObject && !empty($this->{$this->identifier})) {
			return $this->update($fields);
		} elseif (!$this->__isLoadedObject && !empty($this->{$this->identifier})) {
			return $this->add($fields, TRUE);
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
		if (!$this->__isLoadedObject || empty($this->{$this->identifier})) {
			return FALSE;
		}
		$result = Db::delete($this->__dbTable)->where(array($this->identifier => $this->{$this->identifier}))->limit(1)->execute();
		if ($result->rowsAffected() > 0) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * validates fields before add() and update() operations
	 * @param array $fields - fields, values of which should be validated
	 * @param boolean $includeID (defaults to TRUE) - defines whatever to validate and include identifier field or not
	 * @param array $error - reference to array contain field names which does not passed validation
	 * @return boolean|array - array of fields to operate over if they passed validation, FALSE otherwise
	 */
	protected function validateFields($fields = NULL, $includeID = TRUE, &$error = NULL)
	{
		//set actual fields array
		$fields = (empty($fields)) ? $this->__dbFields : $fields;
		//init containers
		$error = array();
		$success = array();
		foreach ($fields as $field) {
			if (!$includeID && $field === $this->identifier) {
				//if ID must be excluded from validation list we will skip step
				continue;
			}

			if ($this->__fieldDefinitions[$field][self::FP_REQUIRED] && empty($this->{$field})) {
				//if field is required and still appears to be empty - it is an error
				array_push($error, $field);
				continue;
			} elseif (!$this->__fieldDefinitions[$field][self::FP_REQUIRED] && empty($this->{$field})) {
				continue;
			}

			if (!empty($this->__fieldDefinitions[$field][self::FP_TYPE])) {
				//validate whatever contained value meets it type definition if type is defined
				switch ($this->__fieldDefinitions[$field][self::FP_TYPE]) {
					case self::F_TYPE_INT:
						$check = is_numeric($this->{$field});
						break;
					case self::F_TYPE_BOOL:
						$check = is_bool($this->{$field});
						break;
					case self::F_TYPE_STRING:
						$check = is_string($this->{$field});
						break;
					case self::F_TYPE_ARRAY:
						$check = is_array($this->{$field});
						break;
					case self::F_TYPE_OBJECT:
						$check = is_object($this->{$field});
						break;
					case self::F_TYPE_ANY:
						$check = TRUE;
						break;
					default :
						$check = TRUE;
						break;
				}

				if (!$check) {
					array_push($error, $field);
					continue;
				}
			}

			if (empty($this->__fieldDefinitions[$field][self::FP_VALIDATOR])) {
				//if validator does not present we will auto-validate field
				$success[$field] = $this->{$field};
			} elseif (call_user_func("Validate::" . $this->__fieldDefinitions[$field][self::FP_VALIDATOR], $this->{$field})) {
				//if specific validator present we will use it to validate the field
				$success[$field] = $this->{$field};
			} else {
				//if other conditions are failed - seems the field is not valid - then we put it in errors
				array_push($error, $field);
			}
		}

		if (empty($error)) {
			//if everything went ok we return container with valid fields and its values to caller
			return $success;
		}
		//otherwise return false
		return FALSE;
	}

	/**
	 * returns array of fields and they values,
	 * validated to meet requirements and properly prepared to place into DB
	 *
	 * @param array $fields - list of fields by name to validate and prepare
	 * @param boolean $includeID - whatever to include identifier field to process
	 * @return array fields with values validated and prepared (e.g. serialized)
	 * @throws Exception
	 */
	protected function getPreparedFieldCollection($fields = NULL, $includeID = TRUE)
	{
		//set actual fields array to work over
		$fields = (empty($fields)) ? $this->__dbFields : $fields;
		//validating fields using validators set in $this->__dbFieldsValidators
		$error_fields = array();
		$fields = $this->validateFields($fields, $includeID, $error_fields);
		if ($fields === FALSE) {
			throw new Exception(__METHOD__ . " fields: [" . implode(',', $error_fields) . "] does not pass validation");
		}
		//prepare fields to be stored in db
		$fields = $this->packFields($fields);

		return $fields;
	}

	/**
	 * prepares fields values to be saved into DB,
	 * e.g. serializes them
	 * @param array $fields list of fields to prepare
	 * @return array
	 */
	protected function packFields($fields)
	{
		$ready = array();
		foreach ($fields as $field => $value) {
			if (empty($this->__fieldDefinitions[$field][self::FP_PACKAGER])) {
				switch ($this->__fieldDefinitions[$field][self::FP_TYPE]) {
					case self::F_TYPE_OBJECT:
					case self::F_TYPE_ARRAY:
						$ready[$field] = serialize($value);
						break;
					case self::F_TYPE_BOOL:
						$ready[$field] = ($value) ? 1 : 0;
						break;
					default:
						$ready[$field] = $value;
						break;
				}
			} else {
				$ready[$field] = call_user_func($this->__fieldDefinitions[$field][self::FP_PACKAGER], $value);
			}
		}

		return $ready;
	}

	/**
	 * restores original field values after they was retrieved from DB
	 * depends on field TYPE defined in __fieldDefinitions[]
	 * or on field PACKAGER callback provided in __fieldDefinitions[]
	 */
	protected function unpackFields()
	{
		foreach ($this->__dbFields as $field) {
			if (empty($this->__fieldDefinitions[$field][self::FP_UNPACKER])) {
				switch ($this->__fieldDefinitions[$field][self::FP_TYPE]) {
					case self::F_TYPE_OBJECT:
					case self::F_TYPE_ARRAY:
						$this->{$field} = unserialize($this->{$field});
						break;
					case self::F_TYPE_BOOL:
						$this->{$field} = (bool) $this->{$field};
						break;
					default:
						break;
				}
			} else {
				$this->{$field} = call_user_func($this->__fieldDefinitions[$field][self::FP_UNPACKER], $this->{$field});
			}
		}
	}

	/**
	 * converts object to array using $__dbFields list of field names by default
	 * or given array as list of fields
	 * @param array $fields list of fields to convert (default is __dbFields)
	 * @return array
	 */
	public function __toArray($fields = NULL)
	{
		$array = array();
		$fields = (empty($fields)) ? $this->__dbFields : $fields;
		foreach ($fields as $field) {
			$array[$field] = $this->{$field};
		}
		return $array;
	}

}