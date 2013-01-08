<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Deroy
 * Date: 04.01.13
 * Time: 13:28
 * To change this template use File | Settings | File Templates.
 */
class ControllerUOME extends HTMLController
{
	/**
	 * @var UniversalObjectModelEditor
	 */
	protected $model;

	public function __construct()
	{
		parent::__construct();
		$this->setActionTemplate('default', 'UOMESelectClass.tpl');
		$this->setActionTemplate('Select', 'UOMESelectClass.tpl');
		$this->setActionTemplate('List', 'UOMEListObjects.tpl');
		$this->setActionTemplate('Edit', 'UOMEEditObject.tpl');
		$this->setActionTemplate('Save', 'UOMEEditObject.tpl');
	}

	protected function beforeAction($actionName)
	{
		parent::beforeAction($actionName);

		$this->data['site_title'] = 'Universal Object Editor';
		$this->data['home_link'] = '/index_testrun.php?controller=UOME';
		$this->data['menu'] = array(
			0 => array('name' => 'Select Class', 'link' => '/index_testrun.php?controller=UOME&action=Default'),
			1 => array('name' => 'List Objects', 'link' => '/index_testrun.php?controller=UOME&action=List')
		);


		if (!empty($_SESSION['class_name'])) {
			$this->model = new UniversalObjectModelEditor($_SESSION['class_name']);
			$this->data['object_structure'] = $this->model->getObjectFields();
			$this->data['object_classname'] = $this->model->className;
		}
	}

	/**
	 * default action
	 * controller must have at list one action
	 * @var mixed $data - accepted work parameters or data
	 * @return bool
	 */
	public function actionDefault($data)
	{
		$this->data['page_title'] = 'Select Class to work with';
	}

	public function actionSelect()
	{
		$this->data['page_title'] = 'Select Class to work with';
		$this->data['form_action'] = '?controller=UOME&action=select';
		$this->model = new UniversalObjectModelEditor($_POST['class_name']);
		$_SESSION['class_name'] = $this->model->className;
		unset($_SESSION['list_fields']);
		$this->data['object_structure'] = $this->model->getObjectFields();
		$this->data['object_classname'] = $this->model->className;
		if (!empty($_POST['object_id'])) {
			$this->runAction('Edit',$_POST['object_id']);
		} else {
			$this->runAction('List');
		}
	}

	public function actionList()
	{
		$this->data['page_title'] = 'Listing Objects of Class';
		$this->data['form_action'] = '?controller=UOME&action=List';
		$fields = NULL;
		if(!empty($_POST['fields'])) {
			$fields = $_POST['fields'];
			$_SESSION['list_fields'] = $fields;
		} elseif(!empty($_SESSION['list_fields'])) {
			$fields = $_SESSION['list_fields'];
		} else {
			throw new ControllerActionError('select fields to display first!');
		}
		$this->data['object_identifier'] = $this->model->infoObject->getIdFieldName();
		$this->data['list'] = $this->model->getObjectsList($fields);
		$this->data['object_edit_link'] = '?controller=UOME&action=Edit&id=';
		$this->data['object_identifier'] = $this->model->infoObject->getIdFieldName();

	}

	public function actionEdit($data)
	{
		$this->data['page_title'] = 'Edit object fields';
		$this->data['form_action'] = '?controller=UOME&action=Select';
		$this->data['form_action2'] = '?controller=UOME&action=Save';
		if(empty($data)) {
			throw new ControllerActionError('No Object ID set: new Object will be created');
		}
		$this->data['object_identifier'] = $this->model->infoObject->getIdFieldName();
		$this->data['object_instance'] = $this->model->getObject($data);
	}

	public function actionSave()
	{
		$this->data['page_title'] = 'Check object fields assign';
		$this->data['form_action'] = '?controller=UOME&action=Select';
		$this->data['form_action2'] = '?controller=UOME&action=Save';
		if(empty($_POST)){
			throw new ControllerActionError('Data not received');
		}
		$this->data['object_identifier'] = $this->model->infoObject->getIdFieldName();
		$this->data['object_instance'] = $this->model->saveChanges($_POST);
	}

}
