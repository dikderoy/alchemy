<?php

/**
 * Create, Manage and Control database structures in MySQL
 * using PDO
 *
 * @category Database Control Set
 * @package Alchemy Framework
 * @version 2.0.0
 * @author Deroy aka Roman Bulgakov
 * @uses PDO PHP Data Objects class
 * @uses PDOException PDO exception class to catch PDO exceptions
 * @uses DbQuery support query management class
 * @uses DbException exception class
 */
class Db extends SingletoneModel implements ISingletone
{

	const Q_TYPE_FREEFORM = 1;
	const Q_TYPE_SELECT = 2;
	const Q_TYPE_UPDATE = 3;
	const Q_TYPE_INSERT = 4;
	const Q_TYPE_DELETE = 5;
	const SORT_ASC = 6;
	const SORT_DESC = 7;

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
	 * last executed query result object
	 * @var DbQuery
	 */
	public $lastQuery;

	/**
	 * keeps track how much querys was executed by this instance
	 * @var int
	 */
	protected $queriesTotal = 0;

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
	 * return queries counter value
	 * @return int
	 */
	public function getQueryesTotal()
	{
		return $this->queriesTotal;
	}

	/**
	 * add queryes reported by $o to query counter value
	 * @param DbQuery $o
	 */
	public function reportQueryes(DbQuery $o)
	{
		$this->queriesTotal += $o->getExecuteCount();
	}

	public function __toString()
	{
		return "queryes total :: {$this->queriesTotal}";
	}

	/**
	 * execute freeform instant query without preparation
	 * @param string $query
	 * @return DbQuery
	 */
	public static function query($query)
	{
		self::getInstance()->lastQuery = new DbQuery();
		return self::getInstance()->lastQuery->instantExecute($query);
	}

	/**
	 * start construction of select
	 *
	 * sets select clause of query
	 * accepts array () where:
	 * each element is column name
	 * @param array $args
	 * @param $_..
	 * @return DbQuery
	 */
	public static function select($args = NULL)
	{
		self::$instance->lastQuery = new DbQuery();
		return self::$instance->lastQuery->select($args);
	}

	/**
	 * start construction of insert
	 *
	 * sets insert what and values clauses of query
	 * accepts array of paired values where
	 * key = field name
	 * value = field value
	 * @param array $args
	 * @return DbQuery
	 * @throws DbException
	 */
	public static function insert($args)
	{
		self::$instance->lastQuery = new DbQuery();
		return self::$instance->lastQuery->insert($args);
	}

	/**
	 * start construction of update
	 *
	 * sets table to update
	 * accepts string table name parameter
	 * @param string $args
	 * @return DbQuery
	 */
	public static function update($args)
	{
		self::$instance->lastQuery = new DbQuery();
		return self::$instance->lastQuery->update($args);
	}

	/**
	 * start construction of delete
	 *
	 * set table from wich to delete
	 * accepts string table name parameter
	 * @param string $args
	 * @return DbQuery
	 */
	public static function delete($args)
	{
		self::$instance->lastQuery = new DbQuery();
		return self::$instance->lastQuery->delete($args);
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