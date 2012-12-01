<?php

/**
 * subclass of Exeption
 * ControllerActionError Exeption is exception type wich should be thrown at error in action controller methods
 * durring exception handling process controller will assign a handler depending on $handlerName property
 * given than exception was thrown
 *
 * handler must be descripted as one of the methods of controller wich catches exception
 *
 * code set to E_WARNING value
 *
 * @author Deroy
 */
class ControllerActionError extends ControllerException
{

	protected $handlerPostfix = 'ErrorHandler';

}