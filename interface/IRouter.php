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
	 * returns request data
	 */
	public function getRequest();

	/**
	 * returns controller name parsed from query
	 */
	public function getController();

	/**
	 * returns action name parsed from query
	 */
	public function getAction();

	/**
	 * return item id parsed from query
	 */
	public function getId();
}