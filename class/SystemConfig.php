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
	public $db_driver;
	public $db_server;
	public $db_name;
	public $db_login;
	public $db_password;
	public $db_charset = 'utf8';

	/**
	 * app response settings
	 */
	public $site_encoding = 'utf8';
	public $html_doctype;

	/**
	 * @var bool defines whatever debug info (post, get, session, cookie arrays print_r()) must be shown or not
	 */
	public $show_enveronment_debug = FALSE;

	/**
	 * @var bool defines whatever debug var_dump() function executed on response data
	 */
	public $show_response_vardump = FALSE;

	/**
	 * @var int - define a lifetime of cookies in seconds
	 */
	public $cookies_lifetime = 40000;

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

	private function __construct($settings)
	{
		$this->setConfig($settings);
	}

	/**
	 * returns singleton instance of DB
	 * @return Db
	 */
	public static function getInstance($settings = NULL)
	{
		if (is_null(self::$instance)) {
			self::$instance = new self($settings);
		}

		return self::$instance;
	}

}