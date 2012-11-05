<?php

/**
 * Core of Alchemy Framework
 *
 * @author Deroy
 */
class Core extends SingletoneModel
{
	/**
	 * SQL connection object
	 * @var Db
	 */
	private $db;

	/**
	 * Session Handler object
	 * @var Session
	 */
	private $session;

	/**
	 * Routing Module object
	 * (must implement IRouter interface)
	 * @var IRouter
	 */
	private $router;

	/**
	 * indicates whatever error occurs
	 * @var bool
	 */
	public $error = FALSE;

	/**
	 * Config object there all service information is stored
	 * @var Config
	 */
	public $registry;

	protected function sessionInit()
	{

	}

	public function init()
	{
		try {
			$this->registry = new Config();
			$this->registry->set('executionTime', microtime(TRUE));

			Db::getInstance()->setDbParameters(SystemConfig::getInstance());

			$this->router = new Router();
			$this->router->parseRequest();

			$this->sessionInit();

			if(SystemConfig::getInstance()->userSupport) {
				//here comes getUser , isUserAuthorized etc...
			}
		} catch (Exception $exc) {
			$this->error = TRUE;
			$this->registry->errorException = $exc;
		} catch (DbException $exc) {
			$this->error = TRUE;
			$this->registry->errorException = $exc;
		}
	}

	public function run()
	{
		if($this->error) {
			$this->initErrorHandler($this->registry->errorException);
		}
	}

	protected function initErrorHandler($exc)
	{

	}
}