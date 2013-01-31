<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Deroy
 * Date: 30.01.13
 * Time: 21:15
 * To change this template use File | Settings | File Templates.
 */
class ACLPermission extends ObjectModel
{
	protected $__dbTable = 'acl_permission';
	protected $__fieldDefinitions = array(
		'id'           => array(
			self::FP_TYPE     => self::F_TYPE_INT,
			self::FP_SIZE     => 10,
			self::FP_REQUIRED => TRUE
		),
		'role_id'      => array(
			self::FP_TYPE     => self::F_TYPE_INT,
			self::FP_SIZE     => 10,
			self::FP_REQUIRED => TRUE
		),
		'action_id'    => array(
			self::FP_TYPE     => self::F_TYPE_INT,
			self::FP_SIZE     => 10,
			self::FP_REQUIRED => TRUE
		),
		'attribute_id' => array(
			self::FP_TYPE     => self::F_TYPE_INT,
			self::FP_SIZE     => 10,
			self::FP_REQUIRED => TRUE
		),
		'resource_id'  => array(
			self::FP_TYPE     => self::F_TYPE_INT,
			self::FP_SIZE     => 10,
			self::FP_REQUIRED => TRUE
		),
		'status'       => array(
			self::FP_TYPE     => self::F_TYPE_BOOL,
			self::FP_SIZE     => 1,
			self::FP_REQUIRED => TRUE
		)
	);

	public $id;
	public $role_id;
	public $action_id;
	public $attribute_id;
	public $resource_id;
	public $status;

}
