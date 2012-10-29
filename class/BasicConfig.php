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
	 * @var string root directory of system/component
	 */
	public $base_path = NULL;

	/**
	 * @var bool  defines whatever to perform debug operations or not
	 */
	public $debug = FALSE;


	public final function __set($name, $value)
	{
		if ($name == 'settings') {
			throw new Exception('illegal use of Config::settings property!!!');
		}

		if (property_exists(__CLASS__, $name)) {
			$this->{$name} = $value;
		} else {
			$this->settings[$name] = $value;
		}
	}

	public final function __get($name)
	{
		if (property_exists(__CLASS__, $name)) {
			return $this->{$name};
		}

		if (array_key_exists($name, $this->settings)) {
			return $this->settings[$name];
		}

		return FALSE;
	}

	public function get($name)
	{
		return $this->{$name};
	}

	public function set($name, $value)
	{
		$this->{$name} = $value;
	}

	/**
	 * sets variables in class depending on keys in given array
	 * @param array $settings key paired values to set within object
	 */
	public function setConfig($settings)
	{
		foreach ($settings as $key => $value) {
			$this->{$key} = $value;
		}
	}
}