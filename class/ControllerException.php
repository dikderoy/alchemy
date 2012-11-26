<?php

/**
 * subclass of Exeption
 * Controller Exeption is exeption type wich should be thrown at error in controller methods
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

	public function __construct($message = "", $handler = NULL, $previous = NULL)
	{
		parent::__construct($message, E_WARNING, $previous);
		$this->handlerName = $handler;
	}

	public function getHandler()
	{
		return $this->handlerName.'ExceptionHandler';
	}

}