<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Deroy
 * Date: 30.01.13
 * Time: 20:49
 * To change this template use File | Settings | File Templates.
 */
class ACL
{
	/**
	 * find out whatever requested action is allowed
	 * @param string $action
	 * @param string|object $resource
	 * @param null|User $user
	 */
	public function can($action, $resource, $user = NULL)
	{

	}

	/**
	 * grand user|role a permission to do given action over given resource type.
	 * actually creates new permission record in Db.
	 * @param string $action
	 * @param string|object $resource
	 * @param null|User|string $role
	 */
	public function allow($action, $resource, $role = NULL)
	{

	}

	/**
	 * returns a list of permission rules assigned to given user
	 * @param null|User $user
	 */
	public function getUserPermissions($user = NULL)
	{

	}
}
