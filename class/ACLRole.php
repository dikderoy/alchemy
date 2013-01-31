<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Deroy
 * Date: 30.01.13
 * Time: 21:04
 * To change this template use File | Settings | File Templates.
 */
class ACLRole extends ObjectModel
{
	protected $__dbTable = 'acl_role';
	protected $__fieldDefinitions = array(
		'id'   => array(
			self::FP_TYPE      => self::F_TYPE_INT,
			self::FP_SIZE      => 10,
			self::FP_REQUIRED  => TRUE
		),
		'name' => array(
			self::FP_TYPE      => self::F_TYPE_STRING,
			self::FP_SIZE      => 50,
			self::FP_VALIDATOR => 'isValidObjectName',
			self::FP_REQUIRED  => TRUE
		)
	);

	public $id;
	public $name;

	public function getPermissions()
	{

	}

}
