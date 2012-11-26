<?php

/**
 * Description of BasicConfig
 *
 * @author Deroy
 */
abstract class BasicConfig
{
	/**
	 * container property - forbidden to use from outside of class
	 * raises error if accessed from outside
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @var bool  defines whatever to perform debug operations or not
	 */
	public $debug = FALSE;


	public final function __set($name, $value)
	{
		if ($name == 'settings') {
			throw new Exception('illegal use of Config::settings property!!!');
		}

		if (property_exists($this, $name)) {
			$this->{$name} = $value;
		} else {
			$this->settings[$name] = $value;
		}
	}

	public final function __get($name)
	{
		if (property_exists($this, $name)) {
			return $this->{$name};
		}

		if (array_key_exists($name, $this->settings)) {
			return $this->settings[$name];
		}

		return FALSE;
	}

	/**
	 * sets variables in class depending on keys in given array
	 * @param array $settings key paired values to set within object
	 */
	public function setConfig($settings)
	{
		if(empty($settings)) {
			return;
		}
		
		foreach ($settings as $key => $value) {
			$this->{$key} = $value;
		}
	}
}