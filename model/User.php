<?php

class User extends ObjectModel
{
	protected $identificator = 'uid';
	protected $__dbTable = 'user';
	protected $__dbFields = array(
		'uid',
		'login',
		'password',
		'securityToken',
		'accessLevel',
		'privileges',
		'name',
		'surname',
		'fathername',
		'email',
		'phone',
		'city',
		'address',
		'firmName',
		'firmType',
		'info'
	);

	protected $__dbFieldsValidators = array(
		'login' => "isValidObjectName" ,
		'password' => "isValidPassword",
		'accessLevel' => "isInt",
		'name' => "isValidName",
		'surname' => "isValidName",
		'fathername' => "isValidName",
		'email'  => "isValidEmail",
		'city' => "isValidName",
		'address' => "isValidName",
		'firmName' => "isValidName",
		'firmType' => "isValidName",
		'info' => "isValidName"
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
	public $city;
	public $address;
	public $firmName;
	public $firmType;
	public $info;

	/**
	 * defines whatever user registred or guest
	 * @var bool
	 */
	public $isRegistered = TRUE;

	/**
	 * shows whatever recognized is user passed authorization
	 * @var bool
	 */
	public $isAuthorized = FALSE;


	/**
	 * return User instance by Login
	 * @param string $word
	 * @return KWDUser
	 */
	public static function getUserByLogin($word)
	{
		$statement = Db::getInstance()->select()->from(__DBPREFIX__."user")->where("login = :login")->limit(1)->_exec(TRUE);
		$statement->execute(array(':login' => $word));
		$obj = $statement->fetchObject(get_called_class());
		//$obj->__isLoadedObject  = TRUE;
		return $obj;
	}

	/**
	 * return User instance by UID
	 * @param string $word
	 * @return KWDUser
	 */
	public static function getUserByUID($word)
	{
		/*
		$statement = Db::getInstance()->select()->from(__DBPREFIX__."user")->where("uid = :uid")->limit(1)->_exec(TRUE);
		$statement->execute(array(':uid' => $word));
		return $statement->fetchObject(get_called_class());
		 */
		return new self($word);
	}

	/**
	 * inicialization of KWDUser Object
	 * @param string|int $id db_object_id
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);
		$this->privileges = explode(',', $this->privileges);
	}

	/**
	 * returns current user's UID
	 * @return string
	 */
	public function getUID()
	{
		return $this->uid;
	}

	/**
	 * returns current user's login
	 * @return string
	 */
	public function getLogin()
	{
		return $this->login;
	}

	/**
	 * sets new password (generates hash for given string)
	 * @param string $word
	 */
	public function setPassword($word)
	{
		$this->password = $this->Core->Security->generatePswdHash($word);
	}

	/**
	 * returns full user name consists of surname, name, fathername field's values separated by space
	 * @return type
	 */
	public function getFullName()
	{
		return "{$this->surname} {$this->name} {$this->fathername}";
	}

	/**
	 * initialize authorized user session
	 * sets php-session and cookie variables to recognize user in future as 'authorized''
	 * @return boolean
	 */
	public function initiateSession()
	{
		if($this->createSecurityToken()) {
			$this->isAuthorized = TRUE;
			$_SESSION['user'] = array('uid' => $this->uid);
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
		$this->isAuthorized = FALSE;
	}

	/**
	 * creates security token and writes it to DB
	 */
	public function createSecurityToken()
	{
		$token = md5(uniqid('kwd_stoken_'.time(), TRUE));
		$statement = Db::getInstance()->update(__DBPREFIX__."user")->set(array('securityToken'=>$token))->where("uid = :uid")->limit(1)->_exec(TRUE);

		if($statement->execute(array(':uid' => $this->uid))) {
			setcookie("sec_token", $token, time() + KWDConfig::$COOKIE_LIFETIME, '/');
			$this->securityToken = $token;
			$this->Core->env_cookie['sec_token'] = $token;
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
		setcookie("sec_token", "", time() - KWDConfig::$COOKIE_LIFETIME - 1000);
		$this->Core->env_cookie['sec_token'] = NULL;
		//$this->securityToken = NULL;
	}

	/**
	 * checks if given secToken is valid
	 * @param string $token
	 * @return boolean
	 */
	public function checkSecurityToken($token)
	{
		if($this->securityToken == $token) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * checks if given password is valid
	 * @param type $pass
	 * @return boolean
	 */
	public function checkPass($pass)
	{
		if($this->Core->Security->generatePswdHash($pass) == $this->password) {
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
	public function hasPrivilege($privilege_id)
	{
		if(in_array($privilege_id, $this->privileges)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function save()
	{
		$save = array();

		$save['uid']		 = uniqid();
		$save['login']	   = $this->login;
		$save['password']	= $this->password;
		$save['accessLevel'] = $this->accessLevel;
		$save['privileges']  = implode(',', $this->privileges);
		$save['name']		= $this->name;
		$save['surname']	 = $this->surname;
		$save['fathername']  = $this->fathername;
		$save['email']	   = $this->email;
		$save['info']		= $this->info;
		$save['city']		= $this->city;
		$save['phone']	   = $this->phone;
		$save['address']	 = $this->address;
		$save['firmName']	= $this->firmName;
		$save['firmType']	= $this->firmType;

		/*
		  $req = array(
		  'what' => array_keys($save),
		  'into' => 'kwd_users',
		  'values' => array($save)
		  );

		  $query = KWDDB::constructQuery($req,'insert');
		  $data = KWDCore::getInstance()->sql->execute($query);
		 */
		$res = KWDCore::getDBC()->insert($save)->into('kwd_users')->_exec();

		if($res->rowCount() >= 1) {
			$this->id = KWDCore::getDBC()->getPDO()->lastInsertId();
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function update()
	{
		$save = array();

		$save['login']	   = $this->login;
		if(!empty($this->password)) {
			$save['password']	= $this->password;
		}
		$save['accessLevel'] = $this->accessLevel;
		$save['privileges']  = implode(',', $this->privileges);
		$save['name']		= $this->name;
		$save['surname']	 = $this->surname;
		$save['fathername']  = $this->fathername;
		$save['email']	   = $this->email;
		$save['info']		= $this->info;
		$save['city']		= $this->city;
		$save['phone']	   = $this->phone;
		$save['address']	 = $this->address;
		$save['firmName']	= $this->firmName;
		$save['firmType']	= $this->firmType;

		$res = KWDCore::getDBC()->update("kwd_users")->set($save)->where("uid = '{$this->uid}'")->_limit(1)->_exec();

		if($res->rowCount() >= 1) {
			$this->id = KWDCore::getDBC()->getPDO()->lastInsertId();
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function delete()
	{
		$query = array(
				'from' => 'kwd_users',
				'where' => array("uid = '{$this->uid}'"),
				'glue' => ' and ',
				'limit' => 1
		);

		$query = KWDDB::constructQuery($req, 'delete');
		$data  = KWDCore::getInstance()->sql->execute($query);

		if(!empty($data)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function __toArray()
	{
		$array = array();
		$not_push = array(
				"Core",
				"uid",
				"password",
				"securityToken",
				"isRegistered",
				"isAuthorized",
				"accessLevel",
				"privileges"
		);

		foreach($this as $key => $value) {
			if(!in_array($key, $not_push)) {
				$array[$key] = $value;
			}
		}

		return $array;
	}

}

/**
 * represent a Public User connected to a system
 */
class KWDPublicUser extends KWDUser
{

	public function __construct()
	{
		parent::__construct();
		$this->accessLevel = 4;
		$this->isRegistered = FALSE;
		$this->isAuthorized = FALSE;
		$this->login = 'Guest';
		$this->name = 'Guest';
	}

	/**
	 * return new instance
	 * @return \KWDPublicUser
	 */
	public static function getUser()
	{
		return new KWDPublicUser();
	}

}

?>