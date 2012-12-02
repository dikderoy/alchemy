<?php

class Router implements IRouter
{
	protected $controllerPrefix = 'Controller';

	public $request;
	public $params = array();
	public $controller;
	public $action;
	public $id;
	private $isACP = FALSE;

	public function __construct()
	{
		$this->request = $_SERVER['REQUEST_URI'];
	}

	public function parseRequest()
	{
		$t = explode('?', $this->request);
		$this->request = explode('/', $t[0]);

		if ($this->request[1] == 'admin') {
			$this->isACP = TRUE;
			$index = 2;
		} else {
			$index = 1;
		}

		$this->controller = $this->params['controller'] = ucfirst(strtolower($this->request[$index]));
		$this->action = $this->params['action'] = ucfirst(strtolower($this->request[$index + 1]));
		$this->id = $this->params['id'] = $this->request[$index + 2];
		$this->params['rest'] = $t[1];

		if (empty($this->controller)) {
			$this->controller = Registry::getInstance()->defaultController;
			throw new Exception('controller parameter is empty, controller was set to default');
		}
	}

	public function getRequest()
	{
		return $this->request;
	}

	public function getParamsArray()
	{
		return $this->params;
	}

	public function getController()
	{
		return $this->controllerPrefix.$this->controller;
	}

	public function getAction()
	{
		return $this->action;
	}

	public function getId()
	{
		return $this->id;
	}

}