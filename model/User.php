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
		'uid'            => array(
			self::FP_TYPE     => self::F_TYPE_STRING,
			self::FP_SIZE     => 64,
			self::FP_REQUIRED => TRUE
		),
		'login'          => array(
			self::FP_TYPE      => self::F_TYPE_STRING,
			self::FP_SIZE      => 64,
			self::FP_REQUIRED  => TRUE,
			self::FP_VALIDATOR => 'isValidObjectName'
		),
		'password'       => array(
			self::FP_TYPE      => self::F_TYPE_STRING,
			self::FP_SIZE      => 64,
			self::FP_REQUIRED  => TRUE,
			self::FP_VALIDATOR => 'isValidName'
		),
		'security_token' => array(
			self::FP_TYPE => self::F_TYPE_STRING,
			self::FP_SIZE => 64,
		),
		'name'           => array(
			self::FP_TYPE      => self::F_TYPE_STRING,
			self::FP_SIZE      => 256,
			self::FP_REQUIRED  => TRUE,
			self::FP_VALIDATOR => 'isValidName'
		),
		'surname'        => array(
			self::FP_TYPE      => self::F_TYPE_STRING,
			self::FP_SIZE      => 256,
			self::FP_VALIDATOR => 'isValidName'
		),
		'middle_name'    => array(
			self::FP_TYPE      => self::F_TYPE_STRING,
			self::FP_SIZE      => 256,
			self::FP_VALIDATOR => 'isValidName'
		),
		'email'          => array(
			self::FP_TYPE      => self::F_TYPE_STRING,
			self::FP_SIZE      => 256,
			self::FP_REQUIRED  => TRUE,
			self::FP_VALIDATOR => 'isValidEmail'
		),
		'phone'          => array(
			self::FP_TYPE => self::F_TYPE_STRING,
			self::FP_SIZE => 64,
		),
		'city_id'        => array(
			self::FP_TYPE => self::F_TYPE_INT,
			self::FP_SIZE => 64,
		),
		'address_id'     => array(
			self::FP_TYPE => self::F_TYPE_INT,
			self::FP_SIZE => 64,
		),
		'info'           => array(
			self::FP_TYPE => self::F_TYPE_STRING,
			self::FP_SIZE => 5000,
		)
	);
	public $uid;
	public $login;
	protected $password;
	protected $security_token;
	public $name;
	public $surname;
	public $middle_name;
	public $email;
	public $phone;
	public $city_id;
	public $address_id;
	public $info;

	/**
	 * shows whatever recognized is user passed authorization
	 * @var bool
	 */
	protected $__isAuthorized = FALSE;

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
		$pretender = new static(NULL, array('login' => $word));
		if ($pretender->isRegistered()) {
			return $pretender;
		}
		return static::getPublicUser();
	}

	/**
	 * return User instance by UID
	 *
	 * if UID is not found - returns instance of Public User
	 * @param string $uid
	 * @return User
	 */
	public static function getUserByUID($uid)
	{
		$candidate = new static($uid);
		if ($candidate->isRegistered()) {
			return $candidate;
		} else {
			return static::getPublicUser();
		}
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
		$pretender = self::getUserByLogin($login);
		if (!$pretender->isRegistered()) {
			return FALSE;
		} elseif ($pretender->login == $login && $pretender->checkPass($pass)) {
			return $pretender->startSession();
		} else {
			$pretender->endSession();
		}
		return FALSE;
	}

	/**
	 * sets new password (generates hash for given string)
	 * @param string $word
	 */
	public function setPassword($word)
	{
		$this->password = $this->generatePasswordHash($word);
	}

	/**
	 * fake setter for security_token
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
		$list = array($this->surname, $this->name, $this->middle_name);
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
		$this->security_token = md5(uniqid(self::SEC_TOKEN_NAME . microtime(), TRUE));

		if ($this->save(array('security_token'))) {
			setcookie(self::SEC_TOKEN_NAME, $this->security_token, time() + Registry::getInstance()->cookiesLifetime, '/');
			$_COOKIE[self::SEC_TOKEN_NAME] = $this->security_token;
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
		if ($this->security_token == $token) {
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
	public function generatePasswordHash($password)
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
		$rand_offset = (float)$sec + ((float)$usec * 100000);
		srand($rand_offset);

		$alphabet = "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM";
		$len_alphabet = strlen($alphabet);
		$token = "";
		for ($i = 0; $i < $length; $i++) {
			$token .= $alphabet[rand(0, $len_alphabet)];
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
		if ($this->generatePasswordHash($pass) == $this->password) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

}