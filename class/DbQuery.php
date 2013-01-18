<?php

/**
 * Class representing a single prepared or executed query
 * wrapper for PDOStatement object
 *
 * provides a set of methods to construct, prepare and execute SQL queries
 * and fetch results in different forms
 *
 * @category Database Control Set
 * @package Alchemy Framework
 * @version 1.0.0
 * @author Deroy aka Roman Bulgakov
 * @uses Db main db control class
 * @uses DbException exception class
 */
class DbQuery
{

	/**
	 * statement object (on which operations will be performed)
	 * @var PDOStatement
	 */
	protected $statement;

	/**
	 * holds count of executes of statement since it was prepared last time
	 * @var integer
	 */
	protected $executeCount = 0;

	/**
	 * current (or last) query type
	 * @var integer
	 */
	protected $queryType;

	/**
	 * query string
	 * @var string
	 */
	protected $query;

	/**
	 * query modifier (e.g. DISTINCT)
	 * @var string
	 */
	protected $modifier;

	/**
	 * array of cols used in query
	 * @var array
	 */
	protected $what = array();

	/**
	 * array of tables used in query
	 * @var array
	 */
	protected $tables = array();

	/**
	 * where conditions used in query
	 * @var string|array
	 */
	protected $where;

	/**
	 * where GLUE element (AND or OR)
	 * used than $where conditions is an array
	 * @var string
	 */
	protected $whereGlue = "and";

	/**
	 * holds prepared set of values to use
	 * in where clause of prepared query
	 * @var array
	 */
	protected $whereValueSet = array();

	/**
	 * order-by clause
	 * @var array
	 */
	protected $orderBy = array();

	/**
	 * sorting direction
	 * @var string
	 */
	protected $orderDirection = Db::SORT_ASC;

	/**
	 * limit of fetched lines
	 * @var string
	 */
	protected $limit;

	/**
	 * array of values for insert or update queries
	 * as key=>value
	 * @var array
	 */
	protected $values = array();

	/**
	 * set of values prepared for fetched query
	 * @var array
	 */
	protected $readyValueSet = array();

	/**
	 * returns PDOStatement object for direct use
	 * or FALSE if current instance of DbQuery does not have prepared object
	 * @return PDOStatement
	 */
	public function getSTO()
	{
		if ($this->isPrepared()) {
			return $this->statement;
		}
		return FALSE;
	}

	/**
	 * returns count of queries executed by this instance of DbQuery
	 * @return integer
	 */
	public function getExecuteCount()
	{
		return $this->executeCount;
	}

	/**
	 * returns formed or prepared (if statement exists) query string
	 * @return string
	 */
	public function getQueryString()
	{
		if ($this->isPrepared()) {
			return $this->statement->queryString;
		}
		return $this->query;
	}


	/**
	 * indicates whatever query is forged with __make()
	 * and data is accumulated in $readyValueSet and $whereValueSet
	 * @return boolean
	 */
	public function isForged()
	{
		if (!empty($this->query)) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * indicates whatever statement is prepared or not
	 * @return boolean
	 */
	public function isPrepared()
	{
		if ($this->statement instanceof PDOStatement) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * find out whatever statement was executed or not
	 * @return boolean
	 */
	public function isExecuted()
	{
		if ($this->isPrepared() && $this->executeCount > 0) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * destructor of class object
	 * reports number of executed queries to Db class when called
	 * @uses Db main db control class
	 */
	public function __destruct()
	{
		if ($this->isExecuted()) {
			$this->statement->closeCursor();
		}
		Db::getInstance()->reportQueryes($this);
	}

	/**
	 * sets $queryString as query to be prepared
	 * correct behavior depends on provided $queryString
	 * @param string $queryString
	 * @return DbQuery
	 */
	public function freeFormQuery($queryString)
	{
		$this->queryType = Db::Q_TYPE_FREEFORM;
		$this->query = $queryString;

		return $this;
	}

	/**
	 * sets select clause of query
	 * accepts array () where:
	 * each element is column name
	 *
	 * or number of arguments each of which
	 * is a string defining column name
	 * @param array $args
	 * @param string $_ [optional]
	 * @return DbQuery
	 */
	public function select($args = NULL, $_ = NULL)
	{
		$this->queryType = Db::Q_TYPE_SELECT;
		if (empty($args) && empty($_)) {
			return $this;
		}
		if (!is_array($args)) {
			$args = func_get_args();
		}
		$this->what = $args;

		return $this;
	}

	/**
	 * sets insert what and values clauses of query
	 * accepts array of paired values where
	 * key = field name
	 * value = field value
	 * OR
	 * any number of arrays (structurally identical to each other)
	 * each of which is a row in a multi-row insert query
	 * @param array $args
	 * @param array $_ [optional]
	 * @return DbQuery
	 * @throws DbException
	 */
	public function insert($args, $_ = NULL)
	{
		$this->queryType = Db::Q_TYPE_INSERT;
		if ($_ === NULL && is_array($args)) {
			$this->values[0] = $args;
			$this->what = array_keys($args);
		} else {
			$args = func_get_args();
			$this->what = array_keys($args[0]);
			foreach ($args as $arg) {
				if (is_array($arg)) {
					array_push($this->values, $arg);
				} else {
					throw new DbException('SQL error - insert() - args is not an array', E_RECOVERABLE_ERROR);
				}
			}
		}
		return $this;
	}

	/**
	 * sets table to update
	 * accepts string table name parameter
	 * @param string $args
	 * @return DbQuery
	 */
	public function update($args)
	{
		$this->queryType = Db::Q_TYPE_UPDATE;
		return $this->from($args);
	}

	/**
	 * set values for update
	 * accepts array of paired values where
	 * key = field name
	 * value = field value
	 * @param array $args (key=>pair)
	 * @return DbQuery
	 * @throws DbException
	 */
	public function set($args)
	{
		if (is_array($args)) {
			$this->values = $args;
		} else {
			throw new DbException('SQL error - update - set() - args is not an array', E_RECOVERABLE_ERROR);
		}
		return $this;
	}

	/**
	 * set table from which to delete
	 * accepts string table name parameter
	 * @param string $args
	 * @return DbQuery
	 */
	public function delete($args)
	{
		$this->queryType = Db::Q_TYPE_DELETE;
		return $this->from($args);
	}

	/**
	 * defines tables with witch query will work
	 * used usually with select but can be called elsewhere
	 *
	 * accepts string - table name
	 * OR
	 * any number of arguments each of which
	 * is a string containing table name
	 * @param array $args
	 * @return DbQuery
	 */
	public function from($args)
	{
		if (!is_array($args))
			$args = func_get_args();
		$this->tables = $args;
		return $this;
	}

	/**
	 * defines tables with witch query will work
	 * lexical alias to from()
	 * @uses DbQuery::from()
	 * @param array $args
	 * @return DbQuery
	 */
	public function into($args)
	{
		return $this->from($args);
	}

	/**
	 * write argument contents to where clause
	 * for complex WHERE conditions
	 * @param string $condition
	 * @param array $args data to pass as where section parameters
	 * @return DbQuery
	 */
	public function whereComplex($condition, $args = array())
	{
		$this->where = $condition;
		$this->whereValueSet = $args;
		return $this;
	}

	/**
	 * set where clause components
	 * e.g "where id = 1"
	 * presented as array('id' => 1);
	 * @param array $array
	 * @param string $glue
	 * @return DbQuery
	 */
	public function where(array $array, $glue = "and")
	{
		$this->where = $array;
		$this->whereGlue = $glue;
		return $this;
	}

	/**
	 * sets fields by which ORDER CLAUSE will work
	 * accepts array of strings each of which is field name
	 * OR
	 * any number of arguments each of which is a string contains field name
	 * @param array $args
	 * @return DbQuery
	 */
	public function orderBy($args)
	{
		if (!is_array($args)) {
			$args = func_get_args();
		}
		$this->orderBy = $args;
		return $this;
	}

	/**
	 * sets the direction of sorting
	 * default is ASC
	 *
	 * must be one of DB_SORT_* constants
	 * @param string $dir
	 * @return DbQuery
	 */
	public function orderDirection($dir)
	{
		switch ($dir) {
			case Db::SORT_ASC:
				$this->orderDirection = 'ASC';
				break;
			case Db::SORT_DESC:
				$this->orderDirection = 'DESC';
				break;
			default:
				$this->orderDirection = 'ASC';
				break;
		}
		return $this;
	}

	/**
	 * sets limit parameter of query
	 * @param integer $num value of limit
	 * @param null|integer $from value of offset
	 * @return DbQuery
	 */
	public function limit($num, $from = NULL)
	{
		$this->limit = (empty($from)) ? $num : array(0 => $num, 1 => $from);
		return $this;
	}

	/**
	 * constructs query of type SELECT
	 * INTERNAL USE ONLY
	 * @return string forged query
	 */
	protected function __select()
	{
		//SELECT HOW?
		$mode = (empty($this->modifier)) ? "" : $this->modifier;
		//WHAT?
		$cols = (empty($this->what)) ? "*" : implode(",", $this->what);
		//WHERE TO FIND?
		$tables = $this->__tables();
		//ONLY WHEN
		$cond = $this->__where();
		//SORTING BY
		$order = (empty($this->orderBy)) ? "" : " order by " . implode(",", $this->orderBy) . " " . $this->orderDirection;
		//HOW MUCH?
		$limit = $this->__limit();
		return $this->query = "select " . $mode . " " . $cols . " from " . $tables . $cond . $order . $limit;
	}

	/**
	 * constructs query of type INSERT
	 * INTERNAL USE ONLY
	 * @return string forged query
	 */
	protected function __insert()
	{
		$cols = (empty($this->what)) ? "*" : implode(",", $this->what);
		$tables = $this->__tables();

		if (is_array($this->values[0])) {
			foreach ($this->values as $row) {
				//make keys like ":key"
				$vals = array();
				array_walk($row, array('Db', 'attachKeyColon'), array(&$vals));
				array_push($this->readyValueSet, $vals);
			}
			$values = implode(',', array_keys($this->readyValueSet[0]));
		} else {
			return FALSE;
		}
		return $this->query = "insert into $tables($cols) values ($values)";
	}

	/**
	 * constructs query of type UPDATE
	 * INTERNAL USE ONLY
	 * @return string forged query
	 */
	protected function __update()
	{
		//tables
		$tables = $this->__tables();
		//set clause
		$this->what = array_keys($this->values);
		$set = array();
		foreach ($this->what as $key) {
			array_push($set, "`$key` = :$key");
		}
		//values array (make indexes like ":key")
		array_walk($this->values, array('Db', 'attachKeyColon'), array(&$this->readyValueSet));
		//where clause
		$cond = $this->__where();
		$limit = $this->__limit();
		return $this->query = "update " . $tables . " set " . implode(',', $set) . $cond . $limit;
	}

	/**
	 * constructs query of type DELETE
	 * INTERNAL USE ONLY
	 * @return string forged query
	 */
	protected function __delete()
	{
		$tables = $this->__tables();
		$cond = $this->__where();
		$limit = $this->__limit();
		return $this->query = "delete from " . $tables . $cond . $limit;
	}

	/**
	 * fetches TABLE section string for all kinds of queries
	 * INTERNAL USE ONLY
	 * @return string
	 * @throws DbException
	 */
	protected function __tables()
	{
		if (is_array($this->tables) && !empty($this->tables)) {
			$this->tables = array_map(array('Db', 'backquoEnclose'), $this->tables);
			return implode(",", $this->tables);
		}
		throw new DbException('unexpected value of FROM/INTO clause - may cause undefined behavior', E_PARSE);
	}

	/**
	 * fetches WHERE clause for SELECT, UPDATE and DELETE queries
	 * INTERNAL USE ONLY
	 * @return string
	 * @throws DbException
	 */
	protected function __where()
	{
		if (empty($this->where)) {
			return '';
		} elseif (is_array($this->where)) {
			$set = array();
			foreach ($this->where as $key => $value) {
				if (is_numeric($value)) {
					array_push($set, "`$key` = :w_$key");
				} elseif (is_string($value)) {
					array_push($set, "`$key` like :w_{$key}");
				} elseif (is_null($value)) {
					throw new DbException("unexpected value `{$value}` = NULL of WHERE clause - may cause undefined behavior", E_PARSE);
				}
				//make indexes like ":w_key"
				$this->whereValueSet[":w_$key"] = $value;
			}

			return ' where ' . implode(" {$this->whereGlue} ", $set);
		} elseif (is_string($this->where)) {
			return ' where ' . $this->where;
		}
		throw new DbException('unexpected value of WHERE clause - may cause undefined behavior', E_PARSE);
	}

	/**
	 * fetches LIMIT clause for SELECT, UPDATE and DELETE queries
	 * INTERNAL USE ONLY
	 * @return string
	 * @throws DbException
	 */
	protected function __limit()
	{
		if (empty($this->limit)) {
			return '';
		} elseif (is_numeric($this->limit)) {
			return ' limit ' . $this->limit;
		} elseif (is_array($this->limit)) {
			return ' limit ' . implode(',', $this->limit);
		}
		throw new DbException('unexpected value of LIMIT clause - may cause undefined behavior', E_PARSE);
	}

	/**
	 * forges query depending on data collected by construct methods
	 * select, update, insert, delete, free-form and helpers
	 * @return string
	 * @throws DbException
	 */
	protected function __make()
	{
		//set default values to registers
		$this->readyValueSet = array();
		$this->whereValueSet = array();
		//enclose cols and table names in back-quotes
		$this->what = array_map(array('Db', 'backquoEnclose'), $this->what);
		try {
			switch ($this->queryType) {
				case Db::Q_TYPE_FREEFORM:
					if (empty($this->query)) {
						throw new DbException("FREEFORM query error - query is empty!", E_PARSE);
					}
					break;
				case Db::Q_TYPE_SELECT:
					$this->__select();
					break;
				case Db::Q_TYPE_INSERT:
					$this->__insert();
					break;
				case Db::Q_TYPE_UPDATE:
					$this->__update();
					break;
				case Db::Q_TYPE_DELETE:
					$this->__delete();
					break;
				default:
					break;
			}
			return $this->query;
		} catch (DbException $exc) {
			throw $exc;
		}
	}

	/**
	 * clears data stored in registers for query forge operation
	 */
	public function clearQueryData()
	{
		if ($this->isExecuted()) {
			$this->statement->closeCursor();
		}

		$this->modifier = "";
		$this->what = array();
		$this->tables = array();
		$this->where = "";
		$this->orderBy = array();
		$this->orderDirection = 'ASC';
		$this->limit = NULL;
		$this->values = array();
		$this->readyValueSet = array();
		$this->whereValueSet = array();

		$this->executeCount = 0;
	}

	/**
	 * prepares statement
	 * @param bool $forcePrepare prepare statement anyway
	 * @return DbQuery
	 * @throws DbException
	 */
	public function prepare($forcePrepare = FALSE)
	{
		try {
			if ($forcePrepare || !$this->isPrepared()) {
				$this->__make();
				$this->statement = Db::getPDO()->prepare($this->query);
			}
			return $this;
		} catch (PDOException $exc) {
			throw new DbException("DB :: " . __METHOD__ . " - Failed to prepare query :: {$this->query}\r" . $exc->getMessage(), $exc->getCode(), $exc);
		}
	}

	/**
	 * executes statement previously prepared with prepare()
	 *
	 * @param array $valueSet [optional] if given used as valueSet
	 * @return DbQuery
	 * @throws DbException
	 */
	public function executePrepared($valueSet = NULL)
	{
		if (!$this->isPrepared()) {
			return $this;
		}
		if (!$valueSet) {
			$valueSet = array_merge($this->readyValueSet, $this->whereValueSet);
		}
		try {
			if ($this->queryType === Db::Q_TYPE_INSERT && is_array($valueSet[0])) {
				foreach ($valueSet as $params) {
					$this->statement->execute($params);
					$this->executeCount++;
				}
			} else {
				$this->statement->execute($valueSet);
				$this->executeCount++;
			}
		} catch (PDOException $exc) {
			throw new DbException("DB :: " . __METHOD__ . " - Failed to execute query :: {$this->query}\r" . $exc->getMessage(), $exc->getCode(), $exc);
		}
		return $this;
	}

	/**
	 * prepares and executes a statement
	 * @param array $valueSet array containing all values what must be placed into prepared query as user-data
	 * @return DbQuery
	 */
	public function execute($valueSet = NULL)
	{
		$this->prepare();
		return $this->executePrepared($valueSet);
	}

	/**
	 * instantly executes passed query
	 * correct behavior depends on query string passed
	 * @param string $query
	 * @return DbQuery
	 * @throws DbException
	 */
	public function instantExecute($query)
	{
		try {
			$this->statement = Db::getPDO()->query($query);
			$this->executeCount++;
			return $this;
		} catch (PDOException $exc) {
			throw new DbException("Db::failed to prepare and execute instant query: `$query`", E_RECOVERABLE_ERROR, $exc);
		}
	}

	/**
	 * return a string
	 * fetched using __make()
	 * from data given to construct methods chains:
	 * select(),insert(),update(),delete()
	 * @return string
	 */
	public function fetchQueryString()
	{
		return $this->__make();
	}

	/**
	 * pass next row of query result into existing object instance
	 * @param ObjectModel $obj - object to fetch into
	 * @return boolean
	 */
	public function fetchIntoObject($obj)
	{
		if ($this->isExecuted()) {
			$this->statement->setFetchMode(PDO::FETCH_INTO, $obj);
			return $this->statement->fetch();
		}
		return FALSE;
	}

	/**
	 * fetch next row of query result as an object of given class
	 * @param string $class name of class which instance should be returned
	 * @param array $params array of parameters passed to class constructor
	 * @return \boolean|object
	 */
	public function fetchObject($class, $params = NULL)
	{
		if ($this->isExecuted()) {
			return $this->statement->fetchObject($class, $params);
		}
		return FALSE;
	}

	/**
	 * fetch results of query as array of objects of given class
	 * @param string $class name of class which instance should be returned
	 * @param array $params array of parameters passed to class constructor
	 * @return \boolean|array
	 */
	public function fetchObjectCollection($class, $params = NULL)
	{
		if ($this->isExecuted()) {
			return $this->statement->fetchAll(PDO::FETCH_CLASS, $class, $params);
		}
		return FALSE;
	}

	/**
	 * fetch next row of query result as array of key-paired values
	 * @param boolean $num fetch with numerical keys or not
	 * @return boolean
	 */
	public function fetchArray($num = FALSE)
	{
		if ($this->isExecuted()) {
			$num = ($num) ? PDO::FETCH_BOTH : PDO::FETCH_ASSOC;
			return $this->statement->fetch($num);
		}
		return FALSE;
	}

	/**
	 * fetch next row of query result as array of key-paired values
	 * @param boolean $num fetch with numerical keys or not
	 * @return boolean
	 */
	public function fetchArrayCollection($num = FALSE)
	{
		if ($this->isExecuted()) {
			$num = ($num) ? PDO::FETCH_BOTH : PDO::FETCH_ASSOC;
			return $this->statement->fetchAll($num);
		}
		return FALSE;
	}

	/**
	 * returns number of rows affected by last query executed
	 * @return int
	 */
	public function rowsAffected()
	{
		if ($this->isExecuted()) {
			return $this->statement->rowCount();
		}
		return 0;
	}

}