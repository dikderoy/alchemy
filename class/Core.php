<?php

/**
 * Core of Alchemy Framework
 *
 * @package Alchemy Framework
 * @version 1.0.0
 * @author Deroy aka Roman Bulgakov
 */
class Core extends SingletoneModel
{

	/**
	 * instance of Core
	 * @var Core
	 */
	private static $instance;

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
	 * holds all system output
	 * it can by a string of rendered data or an IView object
	 * @var \string|IView output string or configured view object
	 */
	public $output = NULL;

	/**
	 * returns singleton instance of Class
	 * @return Core
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * magic __toString() method
	 * @return string
	 */
	public function __toString()
	{
		return "Core Object";
	}

	/**
	 * @return \IRouter
	 */
	public function getRouter()
	{
		return $this->router;
	}

	/**
	 * initiates session handler
	 */
	protected function sessionInit()
	{
		session_start();
	}

	/**
	 * initiates core processes, prepares environment, etc.
	 * @throws Exception
	 */
	public function init()
	{
		try {
			Db::getInstance(Registry::getInstance());
			$this->sessionInit();

			if (Registry::getInstance()->userSupport) {
				//here comes getUser , isUserAuthorized etc...
				User::getUser();
			}

			$this->router = new RouterGet();
			Registry::setRouter($this->router);
			if ($this->router instanceof IRouter) {
				$this->router->parseRequest();
				Registry::getInstance()->currentController = $this->router->getController();
				Registry::getInstance()->currentAction = $this->router->getAction();
				Registry::getInstance()->currentPageId = $this->router->getId();
			} else {
				throw new Exception('router class does not implements IRouter interface', 500);
			}
			throw new Exception(0,0);
		} catch (DbException $exc) {
			$this->error = TRUE;
			Registry::set('initException', $exc);
		} catch (Exception $exc) {
			$this->error = TRUE;
			Registry::set('initException', $exc);
		}
	}

	/**
	 * actually runs a process on provided data
	 */
	public function run()
	{
		$registry = Registry::getInstance();
		try {
			if ($this->error) {
				$this->initErrorHandler(Registry::get('initException'));
			}
			$this->output = Controller::runController($registry->currentController, $registry->currentAction, $registry->currentPageId);
		} catch (Exception $exc) {
			$this->output = Controller::runController('ControllerError', $exc->getCode(), $exc);
		}
	}

	/**
	 * finalizes process execution, displays result
	 * @throws Exception
	 */
	public function finish()
	{
		if ($this->output instanceof IView) {
			if (Registry::getInstance()->showDebug) {
				$this->output->showDebug(
					$this->router->getParamsArray(),
					Registry::getCurrentUser()->__toArray(array('login', '__isAuthorized', '__isLoadedObject'))
				);
			}
			$this->output->displayGenerated();
		} elseif (!empty($this->output)) {
			print $this->output;
		} else {
			throw new Exception('Error 500 : Failed to get View', 500);
		}
	}

	/**
	 * processes init error
	 * @param Exception $exc
	 */
	protected function initErrorHandler(Exception $exc)
	{
		Registry::set('initError', $exc->getMessage());
	}

}