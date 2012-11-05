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
	protected $rendered;
	protected $outputType;
	protected $viewClass;

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
				throw new ControllerException('noAction', "action $actionName not implemented");
			}
			$this->beforeAction($actionName);
			$this->data = $this->{$this->actionPrefix . $actionName}($data);
			if($this->data === FALSE) {
				throw new ControllerException("action `$actionName` returned bad result", $actionName);
			}
			$this->afterAction($actionName);
		} catch (ControllerException $exc) {
			$handler = $exc->getHandler();
			if (empty($handler)) {
				$handler = "defaultErrorHandler";
			}
			if (!method_exists($this, $handler)) {
				throw new Exception("Fatal Exception :: Error Handler `{$handler}` in class `" . __CLASS__ . "`not found!", E_CORE_ERROR);
			}
			$this->error = $this->{$handler}($exc);
		} catch (DbException $dbExc) {
			$this->error = $this->dbErrorHandler($dbExc);
		}
	}

	protected abstract function defaultErrorHandler($exc);

	protected abstract function defaultActionErrorHandler($exc);

	protected abstract function dbErrorHandler($exc);
}