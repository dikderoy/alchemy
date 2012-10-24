<?php

class Db
{

	/**
	 * instance of DB
	 * @var Db
	 */
	protected static $instance;

	/**
	 * PDO DB resource
	 * @var PDO
	 */
	protected $PDO;
	protected $dbDriver;
	protected $serverAddress;
	protected $dbName;
	protected $charSet;
	protected $login;
	protected $password;
	protected $options = array();

	/**
	 * last executed query result
	 * @var PDOStatement
	 */
	public $lastQuery;

	/**
	 * keeps track how much querys was executed by this instance
	 * @var int
	 */
	protected $queryesTotal = 0;

	/**
	 * holds current (or last) query type
	 * @var string
	 */
	protected $queryType;
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
	 * @var string
	 */
	protected $where;

	/**
	 * orderby clause
	 * @var array
	 */
	protected $orderBy = array();
	protected $orderDirection = 'ASC';

	/**
	 * limit section of query
	 * @var string
	 */
	protected $limit;

	/**
	 * array of values for insert or update queryes
	 * as key=>value
	 * @var type
	 */
	protected $values = array();
	protected $readyValueSet = array();

	private function __construct()
	{
		/* ... @return Singleton */
	}

	/**
	 * protect from creation  by cloning
	 */
	private function __clone()
	{
		/* ... @return Singleton */
	}

	/**
	 * protect from creation by unserialize
	 */
	private function __wakeup()
	{
		/* ... @return Singleton */
	}

	/**
	 * returns singleton instance of DB
	 * @return Db
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		if (self::$instance->PDO instanceof PDO) {
			return self::$instance;
		} elseif (self::$instance->hasDbParameters()) {
			self::$instance->connect();
			return self::$instance;
		} else {
			return self::$instance;
		}
	}

	/**
	 * sets parameters for connection to a server
	 * @param string $dbDriver
	 * @param string $serverAddress
	 * @param string $dbName
	 * @param string $charSet
	 * @param string $login
	 * @param string $password
	 * @param array $options - additional options for PDO object
	 */
	public function setDbParameters($dbDriver, $serverAddress, $dbName, $charSet, $login, $password, $options = array())
	{
		$this->dbDriver = $dbDriver;
		$this->serverAddress = $serverAddress;
		$this->dbName = $dbName;
		$this->charSet = $charSet;
		$this->login = $login;
		$this->password = $password;
	}

	public function hasDbParameters()
	{
		if (empty($this->dbDriver)
				|| empty($this->serverAddress)
				|| empty($this->dbName)
				|| empty($this->charSet)
				|| empty($this->login)
				|| empty($this->password)) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * initiate a connection to server
	 * @param string $dbDriver
	 * @param string $serverAddress
	 * @param string $dbName
	 * @param string $charSet
	 * @param string $login
	 * @param string $password
	 * @param array $options - additional options for PDO object
	 */
	public function connect($options = array())
	{
		try {
			$parameters = "{$this->dbDriver}:host={$this->serverAddress};dbname={$this->dbName};charset={$this->charSet};";
			$this->options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$this->charSet}";
			if (!empty($options) && is_array($options)) {
				$this->options = array_merge($this->options, $options);
			}
			$this->PDO = new PDO($parameters, $this->login, $this->password, $this->options);
			$this->PDO->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
			$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			throw new Exception($e->getMessage(), $e->getCode());
		}
	}

	public static function lastInsertId()
	{
		return self::$instance->PDO->lastInsertId();
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
	 * @return \KWDDB
	 */
	public function select($args = NULL)
	{
		if (!is_array($args))
			$args = func_get_args();
		$this->what = $args;
		$this->queryType = 'select';
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
	 * @return \KWDDB
	 * @throws Exception
	 */
	public function insert($all)
	{
		$all = func_get_args();
		if (is_array($all[0])) {
			$this->what = array_keys($all[0]);
		}
		foreach ($all as $args) {
			if (is_array($args)) {
				array_push($this->values, $args);
				$this->queryType = 'insert';
			} else {
				throw new Exception('SQL error - insert() - args is not an array');
			}
		}

		return $this;
	}

	/**
	 * sets table to update
	 * accepts string table name parameter
	 * @param string $args
	 * @return \KWDDB
	 */
	public function update($args)
	{
		$this->queryType = 'update';
		return $this->from($args);
	}

	/**
	 * set values for update
	 * accepts array of paired values where
	 * key = field name
	 * value = field value
	 * @param array $args (key=>pair)
	 * @return \KWDDB
	 * @throws Exception
	 */
	public function set($args)
	{
		if (is_array($args)) {
			$this->what = array_keys($args);
			$this->values = $args;
		} else {
			throw new Exception('SQL error - update - set() - args is not an array');
		}

		return $this;
	}

	/**
	 * set table from wich to delete
	 * accepts string table name parameter
	 * @param string $args
	 * @return \KWDDB
	 */
	public function delete($args)
	{
		$this->queryType = 'delete';
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
	 * @return \KWDDB
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
	 * @return \KWDDB
	 */
	public function into($args)
	{
		return $this->from($args);
	}

	/**
	 * write argument contents to where clause
	 * @param string $cond
	 * @return \KWDDB
	 */
	public function where($cond)
	{
		$this->where = $cond;
		return $this;
	}

	/**
	 * sets fields by wich ORDER CLAUSE will work
	 * accepts array of strings each of wich is field name
	 * OR
	 * any number of arguments each of wich is a string contains field name
	 * @param array $args
	 * @return \KWDDB
	 */
	public function orderBy($args)
	{
		if (!is_array($args))
			$args = func_get_args();
		$this->orderBy = $args;
		return $this;
	}

	/**
	 * sets the direction of sorting
	 * default is ASC
	 *
	 * accepts strings (ASC | DESC)
	 * @param string $dir
	 * @return \KWDDB
	 */
	public function orderDirection($dir)
	{
		$this->orderDirection = ($dir == 'ASC' || $dir == 'DESC') ? $dir : 'ASC';
		return $this;
	}

	/**
	 * sets limit parameter of query
	 * accepts int value of limit
	 * OR
	 * int value of limit , int value of offset
	 * @param int $args
	 * @return \KWDDB
	 */
	public function limit($args)
	{
		if (!is_array($args))
			$args = func_get_args();
		$this->limit = implode(',', $args);
		return $this;
	}

	/**
	 * prepare and execute litteraly formed query
	 *
	 * if optional $prepareOnly parameter is set to TRUE
	 * when query wont be executed immidietly
	 * instead PDOStatement::prepare() will be called and
	 * PDOStatement object will be returned with prepared statement
	 * @param $prepareOnly = FALSE
	 * @return PDOStatement
	 */
	public function _exec($prepareOnly = FALSE)
	{
		//set default values to registers
		$error = 0;
		$this->readyValueSet = array();
		//sorting cols alphabetically
		sort($this->what);
		//enclose cols and table names in backquots
		$this->what = array_map('backquoEnclose', $this->what);
		$this->tables = array_map('backquoEnclose', $this->tables);

		try {
			switch ($this->queryType) {
				case 'select':
					$mode = (empty($this->modificant)) ? "" : implode(",", $this->modificant);
					$cols = (empty($this->what)) ? "*" : implode(",", $this->what);
					$tables = (empty($this->tables)) ? $error = 1 : implode(",", $this->tables);
					$cond = (empty($this->where)) ? "" : " where " . $this->where;
					$order = (empty($this->orderBy)) ? "" : " order by " . implode(",", $this->orderBy) . " " . $this->orderDirection;
					$limit = (empty($this->limit)) ? "" : " limit " . $this->limit;

					$this->query = "select " . $mode . " " . $cols . " from " . $tables . $cond . $order . $limit;
					break;
				case 'insert': {
						$cols = (empty($this->what)) ? "*" : implode(",", $this->what);
						$tables = (empty($this->tables)) ? $error = 1 : implode(",", $this->tables);

						if (is_array($this->values[0])) {
							foreach ($this->values as $row) {
								//sort values array alphabetically by keys
								ksort($row);
								//make keys like ":key"
								if (!$error) {
									$callback = 'attachKeyColon';
									$vals = array();
									array_walk($row, $callback, array(&$vals));
									array_push($this->readyValueSet, $vals);
								} else {
									$error = 1;
									break;
								}
							}
							$values = implode(',', array_keys($vals));
						} else {
							$error = 1;
						}

						$this->query = "insert into $tables($cols) values ($values)";
						break;
					}
				case 'update': {
						//tables
						$tables = (empty($this->tables)) ? $error = 1 : implode(",", $this->tables);
						//set clause
						$set = array();
						foreach ($this->values as $key => $value) {
							array_push($set, "`$key` = :$key");
						}
						$set = implode(',', $set);
						//values array (make indexes like ":key")
						array_walk($this->values, 'attachKeyColon', array(&$this->readyValueSet));
						//where clause
						$cond = (empty($this->where)) ? "" : " where " . $this->where;
						$limit = (empty($this->limit)) ? "" : " limit " . $this->limit;

						$this->query = "update " . $tables . " set " . $set . $cond . $limit;
						break;
					}
				case 'delete': {
						$tables = (empty($this->tables)) ? $error = 1 : implode(",", $this->tables);
						$cond = (empty($this->where)) ? "" : " where " . $this->where;
						$limit = (empty($this->limit)) ? "" : " limit " . $this->limit;

						$this->query = "delete from " . $tables . $cond . $limit;
						break;
					}
				default:
					break;
			}
			//prepare query
			$this->lastQuery = $this->PDO->prepare($this->query);
		} catch (PDOException $exc) {
			throw new Exception("DB :: " . __METHOD__ . " - Failed to prepare query :: {$this->query}\r" . $exc->getMessage(), $exc->getCode());
		}

		try {
			if (!$prepareOnly) {
				if (is_array($this->readyValueSet[0])) {
					foreach ($this->readyValueSet as $params) {
						$this->lastQuery->execute($params);
						$this->queryesTotal++;
					}
				} else {
					$this->lastQuery->execute($this->readyValueSet);
					$this->queryesTotal++;
				}
			}
		} catch (PDOException $exc) {
			throw new Exception("DB :: " . __METHOD__ . " - Failed to execute query :: {$this->query}\r" . $exc->getMessage(), $exc->getCode());
		}

		if ($this->lastQuery instanceof PDOStatement) {
			return $this->lastQuery;
		} else {
			throw new Exception("DB :: " . __METHOD__ . " - Failed to prepare and execute query :: {$this->query}");
		}
	}

	/**
	 * execute single query
	 * @param string $query
	 * @return PDOStatement
	 */
	public function execute($query)
	{
		try {
			$this->lastQuery = $this->PDO->query($query);
			$this->queryesTotal++;
		} catch (PDOException $exc) {
			throw new Exception("DB :: " . __METHOD__ . " - Failed to prepare and execute query :: {$query}\r" . $exc->getMessage(), $exc->getCode());
		}

		return $this->lastQuery;
	}

	public function fetchIntoObject($query, $obj)
	{
		$statement = $this->PDO->prepare($query);
		$statement->setFetchMode(PDO::FETCH_INTO, $obj);
		return $statement->fetch();
	}

	public function fetchObject($query, $class, $params = NULL)
	{
		$data = $this->execute($query);
		return $data->fetchObject($class, $params);
	}

	public function fetchObjectsArray($query, $class, $params = NULL)
	{
		$data = $this->execute($query);
		return $data->fetchAll(PDO::FETCH_CLASS, $class, $params);
	}

	public function fetchArray($query, $wnum = FALSE)
	{
		$data = $this->execute($query);
		$wnum = ($wnum) ? PDO::FETCH_BOTH : PDO::FETCH_ASSOC;
		return $data->fetchAll($wnum);
	}

	public static function quoEnclose($elem)
	{
		$elem = $this->PDO->quote($elem);
		return $elem;
	}

	public static function scobeEnclose($elem)
	{
		$elem = "($elem)";
		return $elem;
	}

	public static function escapeChars($adapter, $elem)
	{
		$elem = htmlspecialchars($elem);
		//$elem = mysql_real_escape_string($elem, $adapter);
		return $elem;
	}

	/**
	 * returns active PDO object
	 * @return PDO
	 */
	public function getPDO()
	{
		return $this->PDO;
	}

	/**
	 * return queryes counter value
	 * @return int
	 */
	public function getQueryesTotal()
	{
		return $this->queryesTotal;
	}

	public function __toString()
	{
		if ($this->lastQuery instanceof PDOStatement) {
			return "last query :: {$this->lastQuery->queryString}; queryes total :: {$this->queryesTotal}";
		} else {
			return "queryes total :: {$this->queryesTotal}";
		}
	}

}

//for PHP < 5.3 compatibility

function quoEnclose($elem)
{
	$elem = KWDCore::getInstance()->sql->getPDO()->quote($elem);
	return $elem;
}

function backquoEnclose($elem)
{
	$elem = "`$elem`";
	return $elem;
}

function scobeEnclose($elem)
{
	$elem = "($elem)";
	return $elem;
}

function attachKeyColon($value, $key, $return)
{
	$return[0][":$key"] = $value;
}

?>