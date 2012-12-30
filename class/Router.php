<?php

/**
 * represents basic implemetation of Routing module
 */
abstract class Router implements IRouter
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

	abstract public function parseRequest();

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