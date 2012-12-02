<?php

/**
 * Universal Settings Box (USB)
 * holds your settings as self-extendable object
 * use of property "settings" is forbidden
 *
 * @author Deroy
 */
class Config extends Structure
{

	public function __construct($settings)
	{
		$this->setConfig($settings);
	}

	public function get($name)
	{
		return $this->{$name};
	}

	public function set($name, $value)
	{
		$this->{$name} = $value;
	}

}