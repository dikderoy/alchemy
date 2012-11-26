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
	 * @var string
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
			$this->initErrorHandler($exc);
		} catch (DbException $exc) {
			$this->error = TRUE;
			$this->initErrorHandler($exc);
		}
	}

	public function run()
	{
		if ($this->error) {
			//$this->initErrorHandler(Registry::get('initException'));
		}

		try {
			$this->output = $this->runController(
					Registry::getInstance()->currentController,
					Registry::getInstance()->currentAction
			);
		} catch (Exception $exc) {
			$this->output = $this->runController('ControllerError', $exc->getCode());
		}
	}

	public function finish()
	{
		print $this->output;


		if(Registry::getInstance()->showEnveronmentDebug) {
			print '<pre>';
			print_r(Registry::getInstance()->calculateExecutionStatistics());
			var_dump($_SESSION,$_GET,$_POST,$_COOKIE);
			print '</pre>';
		}

		if(Registry::getInstance()->showResponseVardump) {
			print '<pre>';
			var_dump($this->output);
			print '</pre>';
		}

	}

	protected function runController($controllerName, $actionName)
	{
		if (!Tools::includeFileIfExists($controllerName, 'controller/')) {
			$controllerName = Registry::getInstance()->defaultController;
		}
		$c = new $controllerName();
		if ($c instanceof Controller) {
			$c->runAction($actionName, $this->router->getId());
		}

		return $c->renderOutput();
	}

	protected function initErrorHandler($exc)
	{
		//echo $exc->getTraceAsString();
		Registry::set('initError', $exc->getMessage());
	}

}