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
	protected $isACP = FALSE;

    /**
     * constructs an instance of Routing object
     */
    public function __construct()
	{
		$this->request = $_SERVER['REQUEST_URI'];
	}

    /**
     * parses a request into three components (may be less)
     *  - controller name
     *  - action name
     *  - object id (optional parameter its usage defined in each controller individually)
     * @return mixed
     */
    abstract public function parseRequest();

    /**
     * returns request string as it was given to interpreter
     * @return string
     */
    public function getRequest()
	{
		return $this->request;
	}

    /**
     * returns all available request params as array
     * @return array
     */
    public function getParamsArray()
	{
		return $this->params;
	}

    /**
     * returns controller name with prefix parsed from request string
     * @return string
     */
    public function getController()
	{
		return $this->controllerPrefix.$this->controller;
	}

    /**
     * returns default controller name with prefix
     * @return string
     */
    public function getDefaultController()
    {
        return $this->controllerPrefix.Registry::get('defaultController');
    }

    /**
     * returns action name parsed from request string
     * @return string
     */
    public function getAction()
	{
		return $this->action;
	}

    /**
     * returns object id (third parameter) parsed from request string
     * @return string|integer
     */
    public function getId()
	{
		return $this->id;
	}

}