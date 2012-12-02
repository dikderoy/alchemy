<?php

/**
 * subclass of Exeption
 * Controller Exeption is exeption type wich is thrown at error in system controller methods
 * durring exeption handling process controller will assign a handler depending on $handlerName property
 * given than exeption was thrown
 *
 * handler must be descripted as one of the methods of controller wich catches exeption
 *
 * code set to E_WARNING value
 *
 * @author Deroy
 */
class ControllerException extends Exception
{

	/**
	 * @var string name of handler for this error
	 */
	protected $handlerName;

	/**
	 * @var string postfix attached to handerName
	 */
	protected $handlerPostfix = 'ExceptionHandler';

	public function __construct($message = "", $handler = NULL, $previous = NULL)
	{
		parent::__construct($message, E_WARNING, $previous);
		$this->handlerName = $handler;
	}

	public function getHandler(Controller $controller)
	{
		if (empty($this->handlerName)) {
			$handler = 'default' . $this->handlerPostfix;
		} else {
			$handler = $this->handlerName . $this->handlerPostfix;
		}

		if (!method_exists($controller, $handler)) {
			throw new Exception("Fatal Exception :: Error Handler `{$handler}` in class `" . get_class($controller) . "` not found! ", E_CORE_ERROR);
		}

		return (string)$handler;
	}

}