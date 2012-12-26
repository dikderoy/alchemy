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
	 * statement object (on wich operations will be performed)
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
	 * query modificant (e.g. DISTINCT)
	 * @var string
	 */
	protected $modificant;

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
	 * orderby clause
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
	 * array of values for insert or update queryes
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
	 * or FALSE if current instance of DbQuery doesnt have prepared object
	 * @return PDOStatement
	 */
	public function getSTO()
	{
		if($this->isPrepared()) {
			return $this->statement;
		}
		return FALSE;
	}

	/**
	 * returns count of queryes executed by this instance of DbQuery
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
		if($this->isPrepared()) {
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
	 * indicates whatever statement was executed or not
	 * @var boolean
	 */
	public function isExecuted()
	{
		if($this->isPrepared() && $this->executeCount > 0) {
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
		if($this->isExecuted()) {
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
	 * or number of arguments each of wich
	 * is a string defining column name
	 * @param array $args
	 * @param $_..
	 * @return DbQuery
	 */
	public function select($args = NULL)
	{
		if (!is_array($args))
			$args = func_get_args();
		$this->what = $args;
		$this->queryType = Db::Q_TYPE_SELECT;
		return $this;
	}

	/**
	 * sets insert what and values clauses of query
	 * accepts array of paired values where
	 * key = field name
	 * value = field value
	 * OR
	 * any number of arrays (structurally identical to each other)
	 * each of wich is a row in a multirow insert query
	 * @param array $args
	 * @return DbQuery
	 * @throws DbException
	 */
	public function insert($args)
	{
		$args = func_get_args();
		foreach ($args as $arg) {
			if (is_array($arg)) {
				array_push($this->values, $arg);
				$this->queryType = Db::Q_TYPE_INSERT;
			} else {
				throw new DbException('SQL error - insert() - args is not an array', E_RECOVERABLE_ERROR);
			}
		}

		$this->what = array_keys($arg);

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
	 * set table from wich to delete
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
	 * used usualy with select but can be called elsewere
	 *
	 * accepts string - table name
	 * OR
	 * any number of arguments each of wich
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
	 * @param string $cond
	 * @param array $args data to pass as where section parameters
	 * @return DbQuery
	 */
	public function whereComplex($cond, $args = array())
	{
		$this->where = $cond;
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
	 * sets fields by wich ORDER CLAUSE will work
	 * accepts array of strings each of wich is field name
	 * OR
	 * any number of arguments each of wich is a string contains field name
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
	 * accepts int value of limit
	 * OR
	 * int value of limit , int value of offset
	 * @param int $args
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
		$mode = (empty($this->modificant)) ? "" : implode(",", $this->modificant);
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
				//sort values array alphabetically by keys
				ksort($row);
				//make keys like ":key"
				$vals = array();
				array_walk($row, array('Db', 'attachKeyColon'), array(&$vals));
				array_push($this->readyValueSet, $vals);
			}
			$values = implode(',', array_keys($vals));
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
					throw new DbException('unexpected value of WHERE clause - may cause undefined behavior', E_PARSE);
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

	public function fetchQueryString()
	{
		return $this->__make();
	}

	/**
	 * forges query depending on data collected by construct methods
	 * select, update, insert, delete, freeform and helpers
	 * @return boolean|string
	 * @throws DbException
	 */
	protected function __make()
	{

		//set default values to registers
		$this->readyValueSet = array();
		$this->whereValueSet = array();
		//sorting cols alphabetically
		sort($this->what);
		//enclose cols and table names in backquots
		$this->what = array_map(array('Db', 'backquoEnclose'), $this->what);
		try {
			switch ($this->queryType) {
				case Db::Q_TYPE_FREEFORM:
					if(empty($this->query)) {
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
				default:
					break;
			}

			return $this->query;
		} catch (DbException $exc) {
			throw $exc;
		}

		return FALSE;
	}

	/**
	 * clears data stored in registers for query forge operation
	 */
	public function clearQueryData()
	{
		if($this->isExecuted()) {
			$this->statement->closeCursor();
		}

		$this->modificant = "";
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
	 * @return DbQuery
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
	 * executes statement previosly prepared with prepare()
	 *
	 * @param array $values [optional] if given used as valueSet
	 * @return DbQuery
	 * @throws DbException
	 */
	public function executePrepared($valueSet = NULL)
	{
		if (!$this->isPrepared()) {
			return FALSE;
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
	 * @param array $valueSet array containing all values what must be placed into prepared query as userdata
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
	 * @param string $class name of class wich instance should be returned
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
	 * @param string $class name of class wich instance should be returned
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
	 * @param boolean $wnum fetch with numerical keys or not
	 * @return boolean
	 */
	public function fetchArray($wnum = FALSE)
	{
		if ($this->isExecuted()) {
			$wnum = ($wnum) ? PDO::FETCH_BOTH : PDO::FETCH_ASSOC;
			return $this->statement->fetch($wnum);
		}
		return FALSE;
	}

	/**
	 * fetch next row of query result as array of key-paired values
	 * @param boolean $wnum fetch with numerical keys or not
	 * @return boolean
	 */
	public function fetchArrayCollection($wnum = FALSE)
	{
		if ($this->isExecuted()) {
			$wnum = ($wnum) ? PDO::FETCH_BOTH : PDO::FETCH_ASSOC;
			return $this->statement->fetchAll($wnum);
		}
		return FALSE;
	}

	public function rowsAffected()
	{
		if($this->isExecuted()) {
			return $this->statement->rowCount();
		}
		return 0;
	}

}