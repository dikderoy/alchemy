<?php

/**
 * routing interface
 * @author Deroy
 */
interface IRouter
{

	/**
	 * parses request
	 */
	public function parseRequest();

	/**
	 * construct a link to specified controller[,action,[id]]
	 * according to rules of link parsing
	 * @param string $controller
	 * @param null|string $action
	 * @param null|string|int $id
	 * @return string
	 */
	public function makeLink($controller, $action = NULL, $id = NULL);

	/**
	 * fetches a string of GET parameters attached to link
	 * @param array $list
	 * @return string
	 */
	public function getParamString($list);

	/**
	 * returns request data
	 */
	public function getRequest();

	/**
	 * returns request parsed data as array
	 */
	public function getParamsArray();

	/**
	 * returns controller name parsed from query
	 */
	public function getController();

    /**
     * returns default controller name
     * @return string
     */
    public function getDefaultController();

	/**
	 * returns action name parsed from query
	 */
	public function getAction();

	/**
	 * return item id parsed from query
	 */
	public function getId();
}