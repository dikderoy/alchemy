<?php

/**
 * Description of Controller
 *
 * @author Deroy
 */
abstract class Controller
{

	/**
	 * prefix from wich action methods of this controller should start
	 * @var string
	 */
	protected $actionPrefix = 'action';

	/**
	 * holds data generated during action execution
	 * @var array
	 */
	protected $data;

	/**
	 * holds description of error and description of circumstances under wich it has occured
	 * given by ErrorHandler wich processed this error
	 * @var string
	 */
	protected $error;

	/**
	 * holds data defining wich subtemplate to use for each of defined actions
	 * @var array
	 */
	protected $actionTemplates = array(
		'default' => 'defaultAction.tpl'
	);

	/**
	 * defines whatever result of execution can be cached
	 * and further retrieved from cache
	 * @var bool
	 */
	protected $isCacheable = FALSE;

	/**
	 * @var string - type of output (must be a value recognizable by viewClass)
	 */
	protected $outputType;

	/**
	 * @var string - holds name of viewClass used
	 */
	protected $viewClass;

	/**
	 * instance of IView implementation class
	 * @var IView
	 */
	protected $view;

	/**
	 * method called before action run
	 * @abstract
	 */
	protected function beforeAction($actionName)
	{
		$this->initView($this->getActionTemplate($actionName));
	}

	/**
	 * method called after action run
	 * @abstract
	 */
	protected function afterAction($actionName)
	{

	}

	/**
	 * run an action method in failsafe enveronment and catch any wayward Exeptions thrown
	 * @final
	 * @param string $actionName - method keyname to run (not full method name!)
	 * @param array $data - data passed to action method
	 * @throws Exeption - in cause of fatal exeption this is thrown to catch it at higher levels of abstraction
	 * @throws ControllerException - this is thrown if action method does not exists or if it ends up with FALSE returned
	 */
	public final function runAction($actionName, $data, $actionPrefix = NULL)
	{
		//infinity recursion loop protection
		static $recursion_level = 1;
		if ($recursion_level < 3) {
			$recursion_level++;
		} else {
			return FALSE;
		}

		try {
			//assign actual action prefix value
			if ($actionPrefix === NULL) {
				$actionPrefix = $this->actionPrefix;
			}
			//make sure what method exists
			if (!method_exists($this, $actionPrefix . $actionName)) {
				//throw noAction CException
				//throw new ControllerException("action `$actionName` not implemented", 'noAction');
				//or fallback to default action
				$actionName = Registry::getInstance()->defaultAction;
			}
			$this->beforeAction($actionName);
			if ($this->isCached()) {
				return TRUE;
			}
			$exec_state = $this->{$actionPrefix . $actionName}($data);
			//throw actionName CException if data catched from action equals to FALSE
			//(you can also trew this exception type inside of action and define specific *ErrorHandler method)
			if ($exec_state === FALSE) {
				throw new ControllerActionError("action `$actionName` returned bad result", $actionName);
			}
			$this->afterAction($actionName);
		} catch (ControllerActionError $exc) {
			$handler = $exc->getHandler($this);
			if (Validate::isValidObjectName($handler)) {
				$this->error = $this->{$handler}($exc);
			} else {
				throw new Exception("Invalid handler name - `$handler` on " . __LINE__ . " in " . __FILE__);
			}
		} catch (ControllerException $exc) {
			$handler = $exc->getHandler($this);
			if (Validate::isValidObjectName($handler)) {
				$this->runAction($handler, $exc, '');
			} else {
				throw new Exception("Invalid handler name - `$handler` on " . __LINE__ . " in " . __FILE__);
			}
		} catch (DbException $dbExc) {
			$this->runAction('dbErrorHandler', $dbExc);
		}
	}

	public static final function runController($controllerName, $actionName, $itemId = NULL)
	{
		if (!Tools::includeFileIfExists($controllerName, 'controller/')) {
			$controllerName = Registry::getInstance()->defaultController;
		}
		$c = new $controllerName();
		if ($c instanceof Controller) {
			$c->runAction($actionName, $itemId);
		}

		return $c->getView();
	}

	public function isCached()
	{
		if ($this->isCacheable && ($this->view instanceof IView) && !Registry::getInstance()->showDebug) {
			if (empty(Registry::getInstance()->currentPageId)) {
				$cache_id = NULL;
			} else {
				$cache_id = Registry::getInstance()->currentPageId;
				$this->view->setCacheId($cache_id);
			}
			return $this->view->isCached($this->view->getTemplateName(), $cache_id);
		}
		return FALSE;
	}

	public function initView($template)
	{
		if (empty($this->view)) {
			$this->view = new $this->viewClass();
		}

		if ($this->view instanceof IView) {
			$this->view->setTemplateName($template);
			return TRUE;
		}

		return FALSE;
	}

	public function getView()
	{
		if ($this->view instanceof IView) {
			$this->view->assign($this->data);
			$this->view->assign('error_info', $this->error);
			return $this->view;
		}

		return FALSE;
	}

	public final function getActionTemplate($actionName)
	{
		if (array_key_exists($actionName, $this->actionTemplates)) {
			return $this->actionTemplates[$actionName];
		} else {
			return $this->actionTemplates['default'];
		}
	}

	public final function setActionTemplate($actionName, $template)
	{
		$this->actionTemplates[$actionName] = $template;
	}

	public final function getActionPrefix()
	{
		return $this->actionPrefix;
	}

	/**
	 * default action
	 * controller must have at list one action
	 * @var array $data - accepted work parameters or data
	 */
	public abstract function actionDefault($data);

	protected abstract function actionDefaultErrorHandler($exc);

	protected abstract function defaultErrorHandler($exc);

	protected abstract function dbErrorHandler($exc);

	protected abstract function noActionExceptionHandler($exc);
}