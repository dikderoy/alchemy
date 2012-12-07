<?php

class Db extends SingletoneModel implements ISingletone
{

	const Q_TYPE_SELECT = 1;
	const Q_TYPE_UPDATE = 2;
	const Q_TYPE_INSERT = 3;
	const Q_TYPE_DELETE = 4;
	const SORT_ASC = 5;
	const SORT_DESC = 6;

	/**
	 * instance of Db
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
	 * returns singleton instance of DB
	 * @return Db
	 */
	public static function getInstance($config = NULL)
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		if (self::$instance->PDO instanceof PDO) {
			return self::$instance;
		} elseif (self::$instance->hasDbParameters()) {
			self::$instance->connect();
			return self::$instance;
		} elseif (!empty($config)) {
			self::$instance->setDbParameters($config);
			self::$instance->connect();
			return self::$instance;
		} else {
			throw new DbException('error establishing connection to DB');
		}

		return self::$instance;
	}

	/**
	 * sets parameters for connection to a server
	 * @param array $options - additional options for PDO object
	 */
	public function setDbParameters($config, $options = array())
	{
		if (!($config instanceof Structure)) {
			return FALSE;
		}

		$this->dbDriver = $config->dbDriver;
		$this->serverAddress = $config->dbServer;
		$this->dbName = $config->dbName;
		$this->charSet = $config->dbCharset;
		$this->login = $config->dbLogin;
		$this->password = $config->dbPassword;
		$this->options = $options;
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
			throw new DbException($e->getMessage(), $e->getCode(), $e);
		}
	}

	public static function getLastInsertId()
	{
		return self::$instance->PDO->lastInsertId();
	}

	/**
	 * returns active PDO object
	 * @return PDO
	 */
	public static function getPDO()
	{
		return self::getInstance()->PDO;
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
			throw new DbException("DB :: " . __METHOD__ . " - Failed to prepare and execute query :: {$query}\r" . $exc->getMessage(), $exc->getCode(), $exc);
		}

		return $this->lastQuery;
	}

	public function fetchIntoObject($query, $obj, $params = NULL)
	{
		try {
			if ($query instanceof PDOStatement) {
				$statement = $query;
			} else {
				$statement = $this->PDO->prepare($query);
			}
			$statement->setFetchMode(PDO::FETCH_INTO, $obj);
			$statement->execute($params);
		} catch (PDOException $exc) {
			if ($query instanceof PDOStatement) {
				$query = $query->queryString;
			}
			throw new dbException("DB :: " . __METHOD__ . " - Failed to prepare and execute query :: {$query}\r" . $exc->getMessage(), $exc->getCode());
		}

		if ($statement->rowCount() > 0) {
			return $statement->fetch();
		}

		return FALSE;
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
		$elem = self::$instance->PDO->quote($elem);
		return $elem;
	}

	public static function scobeEnclose($elem)
	{
		$elem = "($elem)";
		return $elem;
	}

	public static function escapeChars($elem)
	{
		$elem = htmlspecialchars($elem);
		//$elem = mysql_real_escape_string($elem, $adapter);
		return $elem;
	}

	public static function backquoEnclose($elem)
	{
		$elem = "`$elem`";
		return $elem;
	}

	public static function attachKeyColon($value, $key, $return)
	{
		$return[0][":$key"] = $value;
	}

}