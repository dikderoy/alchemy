<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Deroy
 * Date: 30.01.13
 * Time: 21:49
 * To change this template use File | Settings | File Templates.
 */
class ACLResource extends ObjectModel
{
	protected $__dbTable = 'acl_resource';
	protected $__fieldDefinitions = array(
		'id'    => array(
			self::FP_TYPE     => self::F_TYPE_INT,
			self::FP_SIZE     => 10,
			self::FP_REQUIRED => TRUE
		),
		'name'  => array(
			self::FP_TYPE      => self::F_TYPE_STRING,
			self::FP_SIZE      => 50,
			self::FP_VALIDATOR => 'isValidObjectName',
			self::FP_REQUIRED  => TRUE
		),
		'class' => array(
			self::FP_TYPE      => self::F_TYPE_STRING,
			self::FP_SIZE      => 50,
			self::FP_VALIDATOR => 'isValidObjectName',
			self::FP_REQUIRED  => TRUE
		)
	);

	public $id;
	public $name;
	public $class;

	/**
	 * get all attributes related to this resource
	 * @return array[]|bool
	 */
	public function getAttributes()
	{
		$query = Db::select()->from(__DBPREFIX__ . 'acl_attribute');
		$query->whereComplex(
			'resource_id = :this or resource_id = :all',
			array(
				':this' => $this->getId(),
				':all'  => 0
			));
		$query->execute();
		return $query->fetchArrayCollection();
	}

	/**
	 * get all action which can performed over this resource
	 * @return array[]|bool
	 */
	public function getActions()
	{
		$query = Db::select()->from(__DBPREFIX__ . 'acl_action');
		$query->whereComplex(
			'resource_id = :this or resource_id = :all',
			array(
				':this' => $this->getId(),
				':all'  => 0
			));
		$query->execute();
		return $query->fetchArrayCollection();
	}
}
