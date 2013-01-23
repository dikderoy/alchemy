<?php

/**
 * model of User connected to a system
 * used for resolve user-specific behavior
 *
 * @package Alchemy Framework
 * @version 1.0.0
 * @author Deroy aka Roman Bulgakov
 */
class User extends ObjectModel
{

	const SEC_TOKEN_NAME = 'sec_token';

	protected $identifier = 'uid';
	protected $__dbTable = 'user';
	protected $__fieldDefinitions = array(
		'uid'           => array(
			self::FP_TYPE     => self::F_TYPE_STRING,
			self::FP_SIZE     => 64,
			self::FP_REQUIRED => TRUE
		),
		'login'         => array(
			self::FP_TYPE      => self::F_TYPE_STRING,
			self::FP_SIZE      => 64,
			self::FP_REQUIRED  => TRUE,
			self::FP_VALIDATOR => 'isValidObjectName'
		),
		'password'      => array(
			self::FP_TYPE      => self::F_TYPE_STRING,
			self::FP_SIZE      => 64,
			self::FP_REQUIRED  => TRUE,
			self::FP_VALIDATOR => 'isValidName'
		),
		'securityToken' => array(
			self::FP_TYPE => self::F_TYPE_STRING,
			self::FP_SIZE => 64,
		),
		'accessLevel'   => array(
			self::FP_TYPE     => self::F_TYPE_INT,
			self::FP_SIZE     => 5,
			self::FP_REQUIRED => TRUE
		),
		'privileges'    => array(
			self::FP_TYPE => self::F_TYPE_ARRAY,
		),
		'name'          => array(
			self::FP_TYPE      => self::F_TYPE_STRING,
			self::FP_SIZE      => 256,
			self::FP_REQUIRED  => TRUE,
			self::FP_VALIDATOR => 'isValidName'
		),
		'surname'       => array(
			self::FP_TYPE      => self::F_TYPE_STRING,
			self::FP_SIZE      => 256,
			self::FP_VALIDATOR => 'isValidName'
		),
		'fathername'    => array(
			self::FP_TYPE      => self::F_TYPE_STRING,
			self::FP_SIZE      => 256,
			self::FP_VALIDATOR => 'isValidName'
		),
		'email'         => array(
			self::FP_TYPE      => self::F_TYPE_STRING,
			self::FP_SIZE      => 256,
			self::FP_REQUIRED  => TRUE,
			self::FP_VALIDATOR => 'isValidEmail'
		),
		'phone'         => array(
			self::FP_TYPE => self::F_TYPE_STRING,
			self::FP_SIZE => 64,
		),
		'cityId'        => array(
			self::FP_TYPE => self::F_TYPE_INT,
			self::FP_SIZE => 64,
		),
		'addressId'     => array(
			self::FP_TYPE => self::F_TYPE_INT,
			self::FP_SIZE => 64,
		),
		'info'          => array(
			self::FP_TYPE => self::F_TYPE_STRING,
			self::FP_SIZE => 5000,
		)
	);
	public $uid;
	public $login;
	protected $password;
	protected $securityToken;
	public $accessLevel;
	public $privileges;
	public $name;
	public $surname;
	public $fathername;
	public $email;
	public $phone;
	public $cityId;
	public $addressId;
	public $info;

	/**
	 * shows whatever recognized is user passed authorization
	 * @var bool
	 */
	protected $__isAuthorized = FALSE;

	/**
	 * attaches PREFIX to table name configured
	 * creates user instance
	 *
	 * WARNING: this should only be used to create blank instances of User
	 * to load user use getUser(),getUserByLogin(),getUserByUID() instead
	 * @param null|string $id
	 */
	public function __construct($id = NULL)
	{
		$this->__dbTable = __DBPREFIX__ . $this->__dbTable;
		parent::__construct($id);
	}

	/**
	 * finds out who is current user and returns a link to its Object
	 * (if user is not registered returns Public User using static::getPublicUser())
	 * @param string $login
	 * @return User
	 */
	public static function getUser($login = NULL)
	{
		if ($login) {
			Registry::setCurrentUser(User::getUserByLogin($login));
		} //session has user uid
		elseif (!empty($_SESSION['user']['uid']) && !empty($_COOKIE[self::SEC_TOKEN_NAME])) {
			Registry::setCurrentUser(User::getUserByUID($_SESSION['user']['uid']));
		} else {
			$_SESSION['user'] = NULL;
			Registry::setCurrentUser(static::getPublicUser());
		}

		return Registry::getCurrentUser();
	}

	/**
	 * returns instance of User configured as Public User
	 * @return User
	 */
	public static function getPublicUser()
	{
		$publicUser = new static();
		$publicUser->accessLevel = 4;
		$publicUser->login = 'Guest';
		$publicUser->name = 'Guest';
		return $publicUser;
	}

	/**
	 * return User instance by Login
	 * @param string $word
	 * @return User
	 */
	public static function getUserByLogin($word)
	{
		//get actual class name
		$className = get_called_class();
		//create a blank instance of class to get access to properties
		$propertyStack = new static();
		//perform ID param check
		if ($propertyStack->onConstructCheck($word)) {
			$res = Db::select()->from($propertyStack->__dbTable)->where(array('login' => $word))->limit(1)->execute();
			$instance = $res->fetchObject($className);
			if ($instance instanceof static) {
				$instance->__isLoadedObject = TRUE;
				$instance->unpackFields();
				return $instance;
			}
		}
		return static::getPublicUser();
	}

	/**
	 * return User instance by UID
	 * @param string $uid
	 * @return User
	 */
	public static function getUserByUID($uid)
	{
		return self::protectedInstanceLoad($uid);
	}

	/**
	 * authorizing user who tries to get access
	 * @param string $login
	 * @param string $pass
	 * @return boolean
	 */
	public static function authorize($login, $pass)
	{
		if (empty($login) || empty($pass)) {
			return FALSE;
		}
		$pretendent = self::getUserByLogin($login);
		if (!$pretendent->isRegistered()) {
			return FALSE;
		} elseif ($pretendent->login == $login && $pretendent->checkPass($pass)) {
			return $pretendent->startSession();
		} else {
			$pretendent->endSession();
		}
		return FALSE;
	}

	/**
	 * sets new password (generates hash for given string)
	 * @param string $word
	 */
	public function setPassword($word)
	{
		$this->password = $this->generatePswdHash($word);
	}

	/**
	 * fake setter for securityToken
	 * this is needed to prevent automatic setting
	 * (token is not allowed to be set manually)
	 * @param $securityToken
	 */
	public function setSecurityToken($securityToken)
	{

	}

	/**
	 * returns full user name consists of surname, name, father's name field's values separated by space
	 * @return string
	 */
	public function getFullName()
	{
		$list = array($this->surname, $this->name, $this->fathername);
		$list = array_filter($list);
		return implode(" ", $list);
	}

	/**
	 * find out whatever this user is registered one
	 * (info is loaded from db)
	 * @return bool
	 */
	public function isRegistered()
	{
		return $this->__isLoadedObject;
	}

	/**
	 * find out whatever this user is passed authorization
	 * @return bool
	 */
	public function isAuthorized()
	{
		if (!$this->__isAuthorized) {
			if ($this->checkSecurityToken($_COOKIE[self::SEC_TOKEN_NAME]) && $this->isRegistered()) {
				$this->__isAuthorized = TRUE;
			}
		}
		return $this->__isAuthorized;
	}

	/**
	 * initialize authorized user session
	 * sets php-session and cookie variables to recognize user in future as 'authorized'
	 * @return boolean
	 */
	public function startSession()
	{
		if ($this->createSecurityToken()) {
			$this->__isAuthorized = TRUE;
			$_SESSION['user'] = array('uid' => $this->uid);
			Registry::setCurrentUser($this);
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 *  ends user session (erases php-session and cookies)
	 *  user will no longer be recognized as 'authorized'
	 */
	public function endSession()
	{
		$this->destroySecurityToken();
		session_destroy();
		$this->__isAuthorized = FALSE;
		Registry::setCurrentUser(static::getPublicUser());
	}

	/**
	 * creates security token and writes it to DB
	 */
	public function createSecurityToken()
	{
		$this->securityToken = md5(uniqid(self::SEC_TOKEN_NAME . microtime(), TRUE));

		if ($this->save(array('securityToken'))) {
			setcookie(self::SEC_TOKEN_NAME, $this->securityToken, time() + Registry::getInstance()->cookiesLifetime, '/');
			$_COOKIE[self::SEC_TOKEN_NAME] = $this->securityToken;
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * sets sec_token cookie entry to NULL to initiate its destruction
	 */
	public function destroySecurityToken()
	{
		setcookie(self::SEC_TOKEN_NAME, "NULL", time() - Registry::getInstance()->cookiesLifetime - 10000);
		$_COOKIE[self::SEC_TOKEN_NAME] = NULL;
	}

	/**
	 * checks if given secToken is valid
	 * @param string $token
	 * @return boolean
	 */
	public function checkSecurityToken($token)
	{
		if ($this->securityToken == $token) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * generates word hash (currently MD5 Hash is used)
	 * @param string $password
	 * @return string
	 */
	public function generatePswdHash($password)
	{
		return md5($password);
	}

	/**
	 * generates new random password of given length consisting of [A-Za-z0-9] symbols
	 * @param int $length
	 * @return string
	 */
	public function generatePassword($length)
	{
		list($usec, $sec) = explode(' ', microtime());
		$rand_ofset = (float)$sec + ((float)$usec * 100000);
		srand($rand_ofset);

		$alfa = "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM";
		$len_alfa = strlen($alfa);
		$token = "";
		for ($i = 0; $i < $length; $i++) {
			$token .= $alfa[rand(0, $len_alfa)];
		}
		return $token;
	}

	/**
	 * checks if given password is valid
	 * @param string $pass
	 * @return boolean
	 */
	public function checkPass($pass)
	{
		if ($this->generatePswdHash($pass) == $this->password) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * check whatever user has privilege by id
	 * @param int $privilegeID
	 * @return boolean
	 */
	public function hasPrivilege($privilegeID)
	{
		if (in_array($privilegeID, $this->privileges)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

}