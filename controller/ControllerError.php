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
		$this->isCacheable = TRUE;
		$this->viewClass = 'HTMLView';

		$this->setActionTemplate('noActionExceptionHandler', 'ErrorWrapper.tpl');
		$this->setActionTemplate('404', 'ErrorWrapper.tpl');
	}

	protected function defaultErrorHandler($exc)
	{
		$this->noActionExceptionHandler($exc);
	}

	protected function dbErrorHandler($exc)
	{
		$this->noActionExceptionHandler($exc);
	}

	protected function noActionExceptionHandler($exc)
	{
		if ($exc instanceof Exception) {
			$this->data['content'] = $exc->getMessage();
		} else {
			$this->data['content'] = "Some wild Error Occured!!!";
		}
	}

	public function actionDefault($exc)
	{
		$this->noActionExceptionHandler($exc);
	}

	protected function actionDefaultErrorHandler($exc)
	{
		return $exc->getMessage();
	}

	protected function action404($exc)
	{
		$this->noActionExceptionHandler($exc);
	}

}