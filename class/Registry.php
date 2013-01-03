<?php

/**
 * Singletone.
 * Holds system settings, parameters and SWAP during the execution
 * @package Alchemy Framework
 * @version 1.0.0
 * @author Deroy aka Roman Bulgakov
 */
class Registry extends Structure
{

	/**
	 * singleton instance of class
	 * @var Registry
	 */
	private static $instance;

	private $executionTime;
	private $memoryPeakUsage;

	/**
	 * @var string root directory of system/component
	 */
	public $rootDirectory = NULL;

	/*
	 * database config values
	 */
	public $dbDriver;
	public $dbServer;
	public $dbName;
	public $dbLogin;
	public $dbPassword;
	public $dbCharset = 'utf8';

	/**
	 * defines whatever smarty caching system is enabled
	 * @var bool
	 */
	public $cachingEnabled = FALSE;

	/**
	 * controller called if no controller parameter given
	 * @var string
	 */
	public $defaultController = 'Index';

	/**
	 * action called if no action parameter given
	 * @var string
	 */
	public $defaultAction = 'Default';

	/**
	 * current used controller (default = defaultController)
	 * @var string
	 */
	public $currentController = 'Index';

	/**
	 * current used action parameter (default = defaultAction)
	 * @var string
	 */
	public $currentAction = 'Default';

	/**
	 * current used pageId parameter(used for caching and retrieving from db a specified entry)
	 * @var integer|string
	 */
	public $currentPageId = NULL;

	/**
	 * encoding flag used in server- and meta-headers
	 * @var string
	 */
	public $siteEncoding = 'utf8';

	/**
	 * process user specific secure functions
	 * enable registring/signIn/logOut etc...
	 * @var bool
	 */
	public $userSupport = FALSE;

	/**
	 *holds current user if userSupport is TRUE
	 * @var User
	 */
	protected $currentUser = NULL;

	/**
	 * defines whatever to show any debug at all
	 * if set to FALSE no debug will be shown
	 * regardless to values of
	 *	$showEnveronmentDebug
	 *	$showResponseVardump
	 * @var bool
	 */
	public $showDebug = FALSE;

	/**
	 * defines whatever debug info (post, get, session, cookie arrays print_r()) must be shown or not
	 * @var bool
	 */
	public $showEnveronmentDebug = FALSE;

	/**
	 * defines whatever debug var_dump() function executed on response data
	 * @var bool
	 */
	public $showResponseVardump = FALSE;

	/**
	 * define a lifetime of cookies in seconds
	 * @var int
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
		$this->calculateExecutionStatistics();
		$this->setConfig($settings);
	}

	/**
	 * returns singleton instance of DB
     * @param $settings - array of settings to set (passed to __construct())
	 * @return Registry
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
		return self::getInstance()->{$name};
	}

	public static function set($name, $value)
	{
		self::getInstance()->{$name} = $value;
	}

	/**
	 * returns current user instance
	 * @return User
	 */
	public static function getCurrentUser()
	{
		return self::$instance->currentUser;
	}

	/**
	 * sets current user instance
	 * @param User $currentUser
	 */
	public static function setCurrentUser($currentUser)
	{
		self::$instance->currentUser = $currentUser;
	}

	/**
	 * on first call an initial value of time() is set
	 * on further calls returns exec statistics as array
	 * @return array|NULL execution info
	 */
	public function calculateExecutionStatistics()
	{
		if(empty($this->executionTime)) {
			$this->executionTime = microtime(TRUE);
		} else {
			$this->memoryPeakUsage = memory_get_peak_usage(TRUE);
			$res = array(
				'executionTime' => microtime(TRUE) - $this->executionTime,
				'memoryPeakUsage' => memory_get_peak_usage(TRUE),
				'dbQueriesTotal' => Db::getInstance()->getQueryesTotal()
			);

			return $res;
		}

        return FALSE;
	}

}