<?php

/**
 * Description of ControllerError
 *
 * @author Deroy
 */
class ControllerError extends HTMLController
{

	public function __construct()
	{
		parent::__construct();
		$this->isCacheable = TRUE;
		$this->setActionTemplate('404', 'ErrorWrapper.tpl');
	}

	public function actionDefault($exc)
	{
		$this->noActionExceptionHandler($exc);
	}

	protected function action404($exc)
	{
		$this->noActionExceptionHandler($exc);
	}

}