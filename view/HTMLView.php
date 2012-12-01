<?php

/**
 * Description of HTMLView
 *
 * @author Deroy
 */
class HTMLView extends Smarty implements IView
{
	/**
	 * template name
	 * @var string
	 */
	public $templateName;

	public function __construct()
	{
		parent::__construct();

		$this->setTemplateDir(Registry::getInstance()->rootDirectory . '/templates/');
		$this->setCompileDir(Registry::getInstance()->rootDirectory . '/templates/compiled/');
		$this->setConfigDir(Registry::getInstance()->rootDirectory . '/templates/config/');
		$this->setCacheDir(Registry::getInstance()->rootDirectory . '/templates/cache/');

		$this->force_compile = TRUE;
		//$this->debugging = TRUE;

	}

	public function setTemplateName($templateName)
	{
		$this->templateName = $templateName;
	}

	public function extend($template)
	{
		if(!empty($this->templateName)) {
			$this->templateName = "extends:{$this->templateName}|{$template}";
		} else {
			$this->templateName = $template;
		}
	}

	public function render($data)
	{
		$this->assign($data);
		return $this->fetch($this->templateName);
	}

	public function displayGenerated()
	{
		return $this->display($this->templateName);
	}

}