<?php

/**
 * Abstraction for Controller Type
 *
 * represents basic methods to work with controllers
 * and their properties
 * @package Alchemy Framework
 * @version 1.0.0
 * @author Deroy aka Roman Bulgakov
 *
 * @uses ControllerException
 * @uses ControllerActionError
 * @uses DbException
 */
abstract class Controller
{

	/**
	 * prefix from which action methods of this controller should start
	 * @var string
	 */
	protected $actionPrefix = 'action';

	/**
	 * defines will logic allow fallback to default controller/action
	 * @var bool
	 */
	protected $allowFallbackToDefault = FALSE;

	/**
	 * holds data generated during action execution
	 * @var array
	 */
	protected $data;

	/**
	 * holds description of error and description of circumstances under wich it has occured
	 * given by ErrorHandler which processed this error
	 * @var string
	 */
	protected $error;

	/**
	 * holds data defining which sub-template to use for each of defined actions
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
	 * run an action method in fail-safe environment and catch any wayward Exceptions thrown
	 * @final
	 * @param string $actionName - method keyname to run (not full method name!)
	 * @param mixed $data - data passed to action method
	 * @param null $actionPrefix
	 * @return bool
	 * @throws ControllerException - this is thrown if action method does not exists or if it ends up with FALSE returned
	 * @throws ControllerActionError
	 * @throws Exception - in cause of fatal exception this is thrown to catch it at higher levels of abstraction
	 */
	public final function runAction($actionName, $data = NULL, $actionPrefix = NULL)
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
				if ($this->allowFallbackToDefault) {
					throw new ControllerException("action `$actionName` not implemented", 'noAction');
				}
				//or fallback to default action
				$actionName = Registry::getInstance()->defaultAction;
			}
			$this->beforeAction($actionName);
			if ($this->isCached($actionName, Registry::getInstance()->currentPageId)) {
				return TRUE;
			}
			$exec_state = $this->{$actionPrefix . $actionName}($data);
			//throw actionName CException if data catched from action equals to FALSE
			//(you can also threw this exception type inside of action and define specific *ErrorHandler method)
			if ($exec_state === FALSE) {
				throw new ControllerActionError("action `$actionName` returned bad result", $actionPrefix . $actionName);
			}
			$this->afterAction($actionName);
		} catch (ControllerActionError $exc) {
			$handler = $exc->getHandler($this);
			$this->error = $this->{$handler}($exc);
		} catch (ControllerException $exc) {
			$handler = $exc->getHandler($this);
			$this->runAction($handler, $exc, '');
		} catch (DbException $dbExc) {
			$this->runAction('dbErrorHandler', $dbExc, '');
		}
		return TRUE;
	}

	/**
	 * runs controller logic (creates its instance and calls runAction method)
	 * also checks controller existence before creation
	 * @param $controllerName
	 * @param $actionName
	 * @param null $itemId
	 * @return bool|IView
	 */
	public static final function runController($controllerName, $actionName, $itemId = NULL)
	{
		if (!class_exists($controllerName)) {
			$controllerName = Core::getInstance()->getRouter()->getDefaultController();
		}
		$c = new $controllerName();
		if ($c instanceof Controller) {
			$c->runAction($actionName, $itemId);
		}
		return $c->getView();
	}

	/**
	 * checks if result of current action is cacheable and available from cache if so
	 * @param $actionName - action name to check cache for
	 * @param null $id - object id to check cache for (optional third request parameter not cacheId)
	 * @return bool
	 */
	public function isCached($actionName, $id = NULL)
	{
		$res = FALSE;
		if ($this->isCacheable && ($this->view instanceof IView)) {
			if (empty($id)) {
				$cache_id = $actionName;
			} else {
				$cache_id = $actionName . $id;
			}
			$this->view->setCacheId($cache_id);
			$res = $this->view->isCached($this->view->getTemplateName(), $cache_id);
		} else {
			$this->view->disableCaching();
		}
		return $res;
	}

	/**
	 *
	 * @param string $template - template name
	 * @return bool
	 */
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

	/**
	 * if view is initiated with initView()
	 * returns instance of IView and assigns generated data and error to it
	 * @return bool|IView
	 */
	public function getView()
	{
		if ($this->view instanceof IView) {
			$this->view->assign($this->data);
			$this->view->assign('error_info', $this->error);
			return $this->view;
		}

		return FALSE;
	}

	/**
	 * returns given action template
	 * @param string $actionName
	 * @return string
	 */
	public final function getActionTemplate($actionName)
	{
		if (array_key_exists($actionName, $this->actionTemplates)) {
			return $this->actionTemplates[$actionName];
		} else {
			return $this->actionTemplates['default'];
		}
	}

	/**
	 * sets association between given action and given template
	 * @param string $actionName
	 * @param string $template
	 */
	public final function setActionTemplate($actionName, $template)
	{
		$this->actionTemplates[$actionName] = $template;
	}

	/**
	 * returns action prefix configured for this controller
	 * @return string
	 */
	public final function getActionPrefix()
	{
		return $this->actionPrefix;
	}

	/**
	 * default action
	 * controller must have at list one action
	 * @var mixed $data - accepted work parameters or data
	 * @return bool
	 */
	public abstract function actionDefault($data);

	/**
	 * action error exception handler for actionDefault
	 * @param Exception $exc
	 * @return string contains info or result of error handling - further assigned to `error_info` in IView object
	 */
	protected abstract function actionDefaultErrorHandler($exc);

	/**
	 * action error exception handler used then some action handler is missing (not implemented)
	 * @param Exception $exc
	 * @return string contains info or result of error handling - further assigned to `error_info` in IView object
	 */
	protected abstract function defaultErrorHandler($exc);

	/**
	 * database error exception handler
	 * executed as separate action
	 * @param Exception $exc
	 * @return bool
	 */
	protected abstract function dbErrorHandler($exc);

	/**
	 * executed if requested action not found on controller and $allowFallbackToDefault is FALSE
	 * executed as separate action
	 * @param Exception $exc
	 * @return bool
	 */
	protected abstract function noActionExceptionHandler($exc);
}