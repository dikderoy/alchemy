<?php

class User extends ObjectModel
{

	const SEC_TOKEN_NAME = 'sec_token';

	protected $identificator = 'uid';
	protected $__dbTable = 'user';
	protected $__fieldDefinitions = array(
		'uid' => array(
			self::FP_TYPE => self::F_TYPE_STRING,
			self::FP_SIZE => 64,
			self::FP_REQUIRED => TRUE
		),
		'login' => array(
			self::FP_TYPE => self::F_TYPE_STRING,
			self::FP_SIZE => 64,
			self::FP_REQUIRED => TRUE,
			self::FP_VALIDATOR => 'isValidObjectName'
		),
		'password' => array(
			self::FP_TYPE => self::F_TYPE_STRING,
			self::FP_SIZE => 64,
			self::FP_REQUIRED => TRUE,
			self::FP_VALIDATOR => 'isValidName'
		),
		'securityToken' => array(
			self::FP_TYPE => self::F_TYPE_STRING,
			self::FP_SIZE => 64,
		),
		'accessLevel' => array(
			self::FP_TYPE => self::F_TYPE_INT,
			self::FP_SIZE => 5,
			self::FP_REQUIRED => TRUE
		),
		'privileges' => array(
			self::FP_TYPE => self::F_TYPE_ARRAY,
		),
		'name' => array(
			self::FP_TYPE => self::F_TYPE_STRING,
			self::FP_SIZE => 256,
			self::FP_REQUIRED => TRUE,
			self::FP_VALIDATOR => 'isValidName'
		),
		'surname' => array(
			self::FP_TYPE => self::F_TYPE_STRING,
			self::FP_SIZE => 256,
			self::FP_VALIDATOR => 'isValidName'
		),
		'fathername' => array(
			self::FP_TYPE => self::F_TYPE_STRING,
			self::FP_SIZE => 256,
			self::FP_VALIDATOR => 'isValidName'
		),
		'email' => array(
			self::FP_TYPE => self::F_TYPE_STRING,
			self::FP_SIZE => 256,
			self::FP_REQUIRED => TRUE,
			self::FP_VALIDATOR => 'isValidEmail'
		),
		'phone' => array(
			self::FP_TYPE => self::F_TYPE_STRING,
			self::FP_SIZE => 64,
		),
		'cityId' => array(
			self::FP_TYPE => self::F_TYPE_INT,
			self::FP_SIZE => 64,
		),
		'addressId' => array(
			self::FP_TYPE => self::F_TYPE_INT,
			self::FP_SIZE => 64,
		),
		'info' => array(
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

	public function __construct($id = NULL)
	{
		$this->__dbTable = __DBPREFIX__ . $this->__dbTable;
		parent::__construct($id);
	}

	/**
	 * finds out who is current user and returns a link to its Object
	 *
	 * @return User
	 */
	public static function getUser($login = NULL)
	{
		if ($login) {
			Registry::setCurrentUser(User::getUserByLogin($login));
		}
		//session has user uid
		elseif (!empty($_SESSION['user']['uid']) && !empty($_COOKIE[self::SEC_TOKEN_NAME])) {
			Registry::setCurrentUser(User::getUserByUID($_SESSION['user']['uid']));
		} else {
			$_SESSION['user'] = NULL;
			Registry::setCurrentUser(new PublicUser());
		}

		return Registry::getCurrentUser();
	}

	/**
	 * return User instance by Login
	 * @param string $id
	 * @return User
	 */
	public static function getUserByLogin($word)
	{
		//get actual class name
		$className = get_called_class();
		//create a blank instance of class to get access to properties
		$propertyStack = new $className();
		//perform ID param check
		if ($propertyStack->onConstructCheck($word)) {
			$res = Db::select()->from($propertyStack->__dbTable)->where(array('login' => $word))->limit(1)->execute();
			$instance = $res->fetchObject($className);
			if ($instance instanceof $className) {
				$instance->__isLoadedObject = TRUE;
				$instance->deconservateFields();
				return $instance;
			}
		}
		return new PublicUser();
	}

	/**
	 * return User instance by UID
	 * @param string $word
	 * @return User
	 */
	public static function getUserByUID($uid)
	{
		return self::protectedInstanceLoad($uid);
	}

	/**
	 * authorizing user who tryes to get access
	 * @param string $login
	 * @param string $pass
	 * @return int (1 on success | 0 on fail)
	 */
	public static function authorize($login, $pass)
	{
		if (empty($login)) {
			return FALSE;
		}
		$pretendent = self::getUserByLogin($login);
		//usual auth process (getbylogin from DB , check pass, etc...)
		if (!$pretendent->isRegistered()) {
			return FALSE;
		} elseif ($pretendent->login == $login && $pretendent->checkPass($pass)) {
			return $pretendent->startSession();
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
	 * returns full user name consists of surname, name, fathername field's values separated by space
	 * @return type
	 */
	public function getFullName()
	{
		return "{$this->surname} {$this->name} {$this->fathername}";
	}

	public function isRegistered()
	{
		return $this->__isLoadedObject;
	}

	public function isAuthorized()
	{
		if ($this->checkSecurityToken($_COOKIE[self::SEC_TOKEN_NAME]) && $this->isRegistered()) {
			return $this->__isAuthorized = TRUE;
		}
		return $this->__isAuthorized = FALSE;
	}

	/**
	 * initialize authorized user session
	 * sets php-session and cookie variables to recognize user in future as 'authorized''
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
	 *  user will no longer be recognized as 'authorized''
	 */
	public function endSession()
	{
		$this->destroySecurityToken();
		session_destroy();
		$this->__isAuthorized = FALSE;
		Registry::setCurrentUser(new PublicUser());
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
	 * @param type $password
	 * @return type
	 */
	public function generatePswdHash($password)
	{
		return md5($password);
	}

	/**
	 * generates new random password of given length consisting of [A-Za-z0-9] simbols
	 * @param int $length
	 * @return string
	 */
	public function generatePassword($length)
	{
		list($usec, $sec) = explode(' ', microtime());
		$rand_ofset = (float) $sec + ((float) $usec * 100000);
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
	 * @param type $pass
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
	 * @param int $privilege_id
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

/**
 * represent a Public User connected to a system
 */
class PublicUser extends User
{

	public function __construct()
	{
		parent::__construct();
		$this->accessLevel = 4;
		$this->login = 'Guest';
		$this->name = 'Guest';
	}

}