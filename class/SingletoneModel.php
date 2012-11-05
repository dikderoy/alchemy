<?php

/**
 * describes and defines basic singletone constructions
 *
 * @author Deroy
 */
abstract class SingletoneModel
{

	protected static $instance;  // object instance

	/**
	 * protect from creation by new Singleton
	 */

	protected function __construct()
	{
		/* ... @return Singleton */
	}

	/**
	 * protect from creation  by cloning
	 */
	protected final function __clone()
	{
		/* ... @return Singleton */
	}

	/**
	 * protect from creation by unserialize
	 */
	protected final function __wakeup()
	{
		/* ... @return Singleton */
	}

	/**
	 * returns singleton instance of Class
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

}