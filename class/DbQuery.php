<?php

/**
 * Description of DbQuery
 *
 * @author Deroy
 */
class DbQuery
{

	/**
	 * statement object (on wich operations will be performed)
	 * @var PDOStatement
	 */
	protected $statement;

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
	public function insert($all)
	{
		$all = func_get_args();
		foreach ($all as $args) {
			if (is_array($args)) {
				array_push($this->values, $args);
				$this->queryType = Db::Q_TYPE_INSERT;
			} else {
				throw new DbException('SQL error - insert() - args is not an array', E_RECOVERABLE_ERROR);
			}
		}

		$this->what = array_keys($args);

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
	 * lexical alias to KWDDB::from()
	 * @uses KWDDB::from()
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
	 * @return DbQuery
	 */
	public function where_complex($cond)
	{
		$this->where = $cond;
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

		if ($error) {
			return FALSE;
		}
		return $this->query = "select " . $mode . " " . $cols . " from " . $tables . $cond . $order . $limit;
	}

	protected function __insert()
	{
		$cols = (empty($this->what)) ? "*" : implode(",", $this->what);
		$tables = $this->__tables();

		if (is_array($this->values[0]) && !$error) {
			foreach ($this->values as $row) {
				//sort values array alphabetically by keys
				ksort($row);
				//make keys like ":key"
				if (!$error) {
					$vals = array();
					array_walk($row, array('Db', 'attachKeyColon'), array(&$vals));
					array_push($this->readyValueSet, $vals);
				} else {
					break;
				}
			}
			$values = implode(',', array_keys($vals));
		} else {
			return FALSE;
		}
		return $this->query = "insert into $tables($cols) values ($values)";
	}

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

	protected function __delete()
	{
		$tables = $this->__tables();
		$cond = $this->__where();
		$limit = $this->__limit();

		return $this->query = "delete from " . $tables . $cond . $limit;
	}

	/**
	 * fetches TABLE section string for all kinds of queries
	 * @return boolean|string
	 */
	protected function __tables()
	{
		if (is_array($this->tables) && !empty($this->tables)) {
			$this->tables = array_map(array('Db', 'backquoEnclose'), $this->tables);
			return implode(",", $this->tables);
		}
		throw new DbException('unexpected value of FROM/INTO clause - may cause undefined behavior', E_PARSE);
	}

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
			//cleaning
			$this->modificant = "";
			$this->what = array();
			$this->tables = array();
			$this->where = "";
			$this->orderBy = array();
			$this->orderDirection = 'ASC';
			$this->limit = NULL;
			$this->values = array();

			//prepare query
			$statement = Db::getPDO()->prepare($this->query);
			return $statement;
		} catch (PDOException $exc) {
			throw new DbException("DB :: " . __METHOD__ . " - Failed to prepare query :: {$this->query}\r" . $exc->getMessage(), $exc->getCode(), $exc);
		} catch (DbException $exc) {
			throw $exc;
		}

		return FALSE;
	}

	/**
	 * prepares statement
	 * use this if you wish to use prepared PDOStatement object properties
	 * @return PDOStatement
	 */
	public function prepare()
	{
		return $this->statement = $this->__make();
	}

	/**
	 * executes statement previosly prepared with proteceted __make() or public prepare()
	 *
	 * @param array $values [optional] if given used as valueSet
	 * @return PDOStatement
	 * @throws DbException
	 */
	public function execute_prepared($valueSet = NULL)
	{
		if (!($this->statement instanceof PDOStatement)) {
			return FALSE;
		}

		if (!$valueSet) {
			$valueSet = array_merge($this->readyValueSet, $this->whereValueSet);
		}
		try {
			if ($this->queryType === Db::Q_TYPE_INSERT && is_array($valueSet[0])) {
				foreach ($valueSet as $params) {
					$this->statement->execute($params);
				}
			} else {
				$this->statement->execute($valueSet);
			}
		} catch (PDOException $exc) {
			throw new DbException("DB :: " . __METHOD__ . " - Failed to execute query :: {$this->query}\r" . $exc->getMessage(), $exc->getCode(), $exc);
		}

		return $this->statement;
	}

	/**
	 * prepares and executes a statement
	 */
	public function execute()
	{
		$this->prepare();
		return $this->execute_prepared();
	}

}