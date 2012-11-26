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
	public $error;

	/**
	 * holds data forged for request
	 * @var string
	 */
	public $rendered;

	/**
	 * defines whatever result of execution can be cached
	 * and further retrieved from cache
	 * @var bool
	 */
	public $isCacheable = FALSE;

	/**
	 * @var string - type of output (must be a value recognizable by viewClass)
	 */
	protected $outputType;

	/**
	 * @var string - holds name of viewClass used
	 */
	protected $viewClass;

	/**
	 * instance of classView
	 * @var object
	 */
	protected $view;

	/**
	 * construction method
	 *
	 * here a viewClass may be instantiated
	 */
	public abstract function __construct();

	/**
	 * method called before action run
	 * @abstract
	 */
	protected abstract function beforeAction($actionName);

	/**
	 * method called after action run
	 * @abstract
	 */
	protected abstract function afterAction($actionName);

	/**
	 * default action
	 * controller must have at list one action
	 * @abstract
	 * @var array $data - accepted work parameters or data
	 */
	public abstract function actionDefault($data);

	/**
	 * run an action method in failsafe enveronment and catch any wayward Exeptions thrown
	 * @final
	 * @param string $actionName - method keyname to run (not full method name!)
	 * @param array $data - data passed to action method
	 * @throws Exeption - in cause of fatal exeption this is thrown to catch it at higher levels of abstraction
	 * @throws ControllerException - this is thrown if action method does not exists or if it ends up with FALSE returned
	 */
	public final function runAction($actionName, $data)
	{
		try {
			if (!method_exists($this, $this->actionPrefix . $actionName)) {
				throw new ControllerException("action $actionName not implemented", 'noAction');
				$actionName = Registry::getInstance()->defaultAction;
			}
			$this->beforeAction($actionName);
			$this->data = $this->{$this->actionPrefix . $actionName}($data);
			if ($this->data === FALSE) {
				throw new ControllerException("action `$actionName` returned bad result", $actionName);
			}
			$this->afterAction($actionName);
		} catch (ControllerException $exc) {
			$handler = $exc->getHandler();
			if (empty($handler)) {
				$handler = "defaultErrorHandler";
			}
			if (!method_exists($this, $handler)) {
				throw new Exception("Fatal Exception :: Error Handler `{$handler}` in class `" . get_called_class() . "` not found! ", E_CORE_ERROR);
			}
			$this->error = $this->{$handler}($exc);
		} catch (DbException $dbExc) {
			$this->error = $this->dbErrorHandler($dbExc);
		}
	}

	public function renderOutput()
	{
		if (!($this->view instanceof $this->viewClass)) {
			$this->view = new $this->viewClass();
		}

		if ($this->view instanceof IView) {
			return $this->view->render($this->data);
		}

		return FALSE;
	}

	protected abstract function defaultErrorHandler($exc);

	protected abstract function defaultActionErrorHandler($exc);

	protected abstract function dbErrorHandler($exc);

	protected abstract function noActionExceptionHandler($exc);
}