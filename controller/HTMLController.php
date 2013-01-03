<?php

/**
 * abstraction with defaults for rendering HTML pages
 *
 * @author Deroy
 */
abstract class HTMLController extends Controller
{

	public function __construct()
	{
		$this->viewClass = 'HTMLView';
		$this->setActionTemplate('noActionExceptionHandler', 'ErrorWrapper.tpl');
        $this->setActionTemplate('dbErrorHandler', 'ErrorWrapper.tpl');
	}

	protected function dbErrorHandler($exc)
	{
		$this->data['content'] = "Error 500: Database internal error occured \r\n";

		if ($exc instanceof Exception) {
			$this->data['content'] .= $exc->getMessage();
		}
	}

	protected function defaultErrorHandler($exc)
	{
		if ($exc instanceof Exception) {
			$data = $exc->getMessage();
		} else {
			$data = "Some wild Error Occured!!!";
		}

		return $data;
	}

	protected function actionDefaultErrorHandler($exc)
	{
		return $this->defaultErrorHandler($exc);
	}

	protected function noActionExceptionHandler($exc)
	{
		$this->data['content'] = "Error 404: Action not found on this controller \r\n";

		if ($exc instanceof Exception) {
			$this->data['content'] .= $exc->getMessage();
		}
	}

}