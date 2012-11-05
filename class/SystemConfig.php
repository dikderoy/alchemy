<?php

/**
 * SIngletone. Holds system settings durring the execution
 * @author Deroy
 */
class SystemConfig extends BasicConfig
{

	/**
	 * singleton instance of class
	 * @var SystemConfig
	 */
	private static $instance;

	/**
	 * database config values
	 */
	public $dbDriver;
	public $dbServer;
	public $dbName;
	public $dbLogin;
	public $dbPassword;
	public $dbCharset = 'utf8';

	public $userSupport = FALSE;

	/**
	 * app response settings
	 */
	public $siteEncoding = 'utf8';
	public $htmlDoctype = 'html5';

	/**
	 * @var bool defines whatever debug info (post, get, session, cookie arrays print_r()) must be shown or not
	 */
	public $showEnveronmentDebug = FALSE;

	/**
	 * @var bool defines whatever debug var_dump() function executed on response data
	 */
	public $showResponseVardump = FALSE;

	/**
	 * @var int - define a lifetime of cookies in seconds
	 */
	public $cookiesLifetime = 40000;

	/**
	 * protect from creation  by cloning
	 */
	private function __clone()
	{

	}

	/**
	 * protect from creation by unserialize
	 */
	private function __wakeup()
	{

	}

	private function __construct($settings)
	{
		$this->setConfig($settings);
	}

	/**
	 * returns singleton instance of DB
	 * @return SystemConfig
	 */
	public static function getInstance($settings = NULL)
	{
		if (is_null(self::$instance)) {
			self::$instance = new self($settings);
		}

		return self::$instance;
	}

	public static function get($name)
	{
		return self::$instance->{$name};
	}

	public static function set($name, $value)
	{
		self::$instance->{$name} = $value;
	}

}