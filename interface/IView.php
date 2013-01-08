<?php

/**
 * @package Alchemy Framework
 * @author Deroy aka Roman Bulgakov
 */
interface IView
{

	/**
	 * sets template name
	 * @param string $name
	 */
	public function setTemplateName($name);

	/**
	 * gets template name setted by setTemplateName
	 * @return string
	 */
	public function getTemplateName();

	/**
	 * extend current template with another template
	 * works only for Smarty ancestors
	 * @param string $template
	 */
	public function extend($template);

	/**
	 * if one parameter is given, then it must be array of key=value pairs
	 * wich represents template vars and its values
	 * else
	 * first argument is template variable name
	 * second is its value
	 * @param \string|array $data
	 * @param \mixed $data2
	 */
	public function assign($data, $data2 = NULL);

	/**
	 * fetches and displays template
	 */
	public function displayGenerated();

	/**
	 * include debug info into template data
	 */
	public function showDebug();

	/**
	 * finds out whatever template is cached
	 * @param string $template
	 * @param string|integer|null $cache_id
	 * @param string|integer|null $compile_id
	 * @return bool
	 */
	public function isCached($template = NULL, $cache_id = NULL, $compile_id = NULL);

	/**
	 * set current view cache ID
	 * @param string $cacheID
	 * @return bool
	 */
	public function setCacheId($cacheID);

	/**
	 * disables caching functions on current instance
	 * @return void
	 */
	public function disableCaching();
}