<?php

/**
 * Description of BasicConfig
 *
 * @author Deroy
 */
abstract class Structure
{
	/**
	 * container property - forbidden to use from outside of class
	 * raises error if accessed from outside
	 * @var array
	 */
	protected $__struct = array();

	/**
	 * @var bool  defines whatever to perform debug operations or not
	 */
	public $debug = FALSE;


	public final function __set($name, $value)
	{
		if ($name == '__struct') {
			throw new Exception('illegal use of Config::settings property!!!');
		}

		if (property_exists($this, $name)) {
			$this->{$name} = $value;
		} else {
			$this->__struct[$name] = $value;
		}
	}

	public final function __get($name)
	{
		if (property_exists($this, $name)) {
			return $this->{$name};
		}

		if (array_key_exists($name, $this->__struct)) {
			return $this->__struct[$name];
		}

		return FALSE;
	}

	public final function __isset($name)
	{
		if (property_exists($this, $name)) {
			return isset($this->{$name});
		}

		if (array_key_exists($name, $this->__struct)) {
			return isset($this->__struct[$name]);
		}

		return FALSE;
	}

	public final function __unset($name)
	{
		if ($name == '__struct') {
			throw new Exception('illegal use of Config::settings property!!!');
		}

		if (property_exists($this, $name)) {
			$this->{$name} = NULL;
		} else {
			unset($this->__struct[$name]);
		}
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