<?php

/**
 * describes and defines basic singletone constructions
 *
 * @author Deroy
 */
abstract class SingletoneModel
{

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

}