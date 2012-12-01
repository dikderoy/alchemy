<?php

/**
 * Core of Alchemy Framework
 *
 * @author Deroy
 */
class Core extends SingletoneModel implements ISingletone
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

	public function __toString()
	{
		return "Core Object";
	}

	protected function sessionInit()
	{
		session_start();
	}

	public function init()
	{
		try {
			$this->router = new Router();
			if ($this->router instanceof IRouter) {
				$this->router->parseRequest();
				Registry::getInstance()->currentController = $this->router->getController();
				Registry::getInstance()->currentAction = $this->router->getAction();
			} else {
				throw new Exception('router class does not implements IRouter interface', 500);
			}

			Db::getInstance(Registry::getInstance());
			$this->sessionInit();

			if (Registry::getInstance()->userSupport) {
				//here comes getUser , isUserAuthorized etc...
			}
		} catch (Exception $exc) {
			$this->error = TRUE;
			//$this->initErrorHandler($exc);
			Registry::set('initException', $exc);
		} catch (DbException $exc) {
			$this->error = TRUE;
			//$this->initErrorHandler($exc);
			Registry::set('initException', $exc);
		}
	}

	public function run()
	{
		try {
			if ($this->error) {
				$this->initErrorHandler(Registry::get('initException'));
			} else {
				$this->output = Controller::runController(Registry::getInstance()->currentController, Registry::getInstance()->currentAction,  $this->router->getId());
			}
		} catch (Exception $exc) {
			$this->output = Controller::runController('ControllerError', $exc->getCode(), $exc);
		}
	}

	public function finish()
	{
		if($this->output instanceof IView) {
			$this->output->displayGenerated();
		} else {
			print $this->output;
		}

		if (Registry::getInstance()->showEnveronmentDebug) {
			print '<pre>';
			print_r(Registry::getInstance()->calculateExecutionStatistics());
			var_dump($_SESSION, $_GET, $_POST, $_COOKIE);
			print '</pre>';
		}

		if (Registry::getInstance()->showResponseVardump) {
			print '<pre>';
			var_dump($this->output);
			print '</pre>';
		}
	}

	protected function initErrorHandler($exc)
	{
		Registry::set('initError', $exc->getMessage());
	}

}