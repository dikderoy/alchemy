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
		$this->viewClass = 'HTMLView';

		$this->setActionTemplate('noActionExceptionHandler', 'ErrorWrapper.tpl');
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
		if($exc instanceof Exception) {
			$this->data['content'] =  $exc->getMessage();
		} else {
			$this->data['content'] =  "Some wild Error Occured!!!";
		}
	}

	public function actionDefault($data)
	{

	}


}