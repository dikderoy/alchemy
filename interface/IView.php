<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author Deroy
 */
interface IView
{

	public function setTemplateName($name);

	public function getTemplateName();

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

	public function displayGenerated();

	public function showDebug();

	public function isCached($template, $cache_id = NULL, $compile_id = NULL);
}