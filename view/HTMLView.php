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

	public $settedCacheId = NULL;

	public function __construct()
	{
		parent::__construct();

		$this->setTemplateDir(Registry::getInstance()->rootDirectory . '/templates/');
		$this->setCompileDir(Registry::getInstance()->rootDirectory . '/templates/compiled/');
		$this->setConfigDir(Registry::getInstance()->rootDirectory . '/templates/config/');
		$this->setCacheDir(Registry::getInstance()->rootDirectory . '/templates/cache/');

		if(Registry::getInstance()->cachingEnabled) {
			$this->caching = Smarty::CACHING_LIFETIME_CURRENT;
		}
		//$this->force_compile = TRUE;
	}

	public function setTemplateName($templateName)
	{
		$this->templateName = $templateName;
	}

	public function getTemplateName()
	{
		return $this->templateName;
	}

	public function getCacheId()
	{
		return $this->settedCacheId;
	}

	public function setCacheId($settedCacheId)
	{
		$this->settedCacheId = $settedCacheId;
	}


	public function extend($template)
	{
		if (!empty($this->templateName)) {
			$this->templateName = "extends:{$this->templateName}|{$template}";
		} else {
			$this->templateName = $template;
		}
	}

	public function render($data)
	{
		$this->cache_id = $this->settedCacheId;
		$this->assign($data);
		return $this->fetch($this->templateName);
	}

	public function displayGenerated()
	{
		$this->cache_id = $this->settedCacheId;
		return $this->display($this->templateName);
	}

	public function showDebug()
	{
		$args = func_get_args();

		$this->extend('debugInfo.tpl');

		ob_start();
		print '<pre>';
		if (Registry::getInstance()->showEnveronmentDebug) {
			print "session:\r\n";
			var_dump($_SESSION);
			print "get\r\n";
			var_dump($_GET);
			print "post\r\n";
			var_dump($_POST);
			print "cookie\r\n";
			var_dump($_COOKIE);
		}
		print "external gived debug vars\r\n";
		foreach ($args as $item) {
			var_dump($item);
		}

		if (Registry::getInstance()->showResponseVardump) {
			var_dump($this->output);
		}
		print '</pre>';
		$contents = ob_get_contents();
		ob_end_clean();

		$this->assign('vardump_enveronment', $contents);
		$this->assign('calculation_times', Registry::getInstance()->calculateExecutionStatistics());
	}

}