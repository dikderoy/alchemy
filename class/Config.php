<?php

/**
 * Universal Settings Box (USB)
 * holds your settings as self-extendable object
 * use of property "settings" is forbidden
 *
 * @author Deroy
 */
class Config
{

	/**
	 * container property - forbidden to use from outside of class
	 * raises error if accessed from outside
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @var string root directory of system
	 */
	protected $base_path;
	protected $debug;

	/**
	 * database config values
	 */
	protected $db_driver;
	protected $db_server;
	protected $db_name;
	protected $db_login;
	protected $db_password;
	protected $db_charset = 'utf8';
	/**
	 * app response settings
	 */
	protected $site_encoding = 'utf8';
	protected $html_doctype;

	/**
	 * @var bool defines whatever debug info (post, get, session, cookie arrays print_r()) must be shown or not
	 */
	protected $show_enveronment_debug = FALSE;

	/**
	 * @var bool defines whatever debug var_dump() function executed on response data
	 */
	protected $show_response_vardump = TRUE;

	/**
	 * @var int - define a lifetime of cookies in seconds
	 */
	protected $cookies_lifetime = 40000;

	public function __construct($settings)
	{
		foreach ($settings as $key => $value) {
			$this->{$key} = $value;
		}
	}

	public function __set($name, $value)
	{
		if ($name == 'settings') {
			throw new Exception('illegal use of Config::settings property!!!');
		}

		if (property_exists(__CLASS__, $name)) {
			$this->{$name} = $value;
		} else {
			$this->settings[$name] = $value;
		}
	}

	public function __get($name)
	{
		if (property_exists(__CLASS__, $name)) {
			return $this->{$name};
		}

		if (array_key_exists($name, $this->settings)) {
			return $this->settings[$name];
		}

		return FALSE;
	}

}