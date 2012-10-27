<?php

/**
 * Universal Settings Box (USB)
 * holds your settings as self-extendable object
 * use of property "settings" is forbidden
 *
 * @author Deroy
 */
class Config extends BasicConfig
{

	public function __construct($settings)
	{
		$this->setConfig($settings);
	}

}