<?php

/**
 * Description of ControllerError
 *
 * @author Deroy
 */
class ControllerError extends Controller
{
	public function __construct()
	{
		$this->viewClass = 'HTML5PageView';
	}

	public function beforeAction($actionName)
	{

	}

	public function afterAction($actionName)
	{

	}

	public function defaultActionErrorHandler($exc)
	{

	}

	public function defaultErrorHandler($exc)
	{

	}

	public function dbErrorHandler($exc)
	{

	}

	public function noActionExceptionHandler($exc)
	{
		$this->data =  '404 page not found! =(';
	}

	public function actionDefault($data)
	{

	}


}