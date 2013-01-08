<?php

/**
 * IView implementation
 * abstraction for Smarty Template Engine
 *
 * @package Alchemy Framework
 * @version 1.0.0
 * @author Deroy aka Roman Bulgakov
 */
class HTMLView extends Smarty implements IView
{
	/**
	 * defines whatever to show debug info at the end of output
	 * @var bool
	 */
	protected $showDebugOnOutput = FALSE;

	/**
	 * holds debug info until display() is called
	 * @var string
	 */
	public $debugCache;

	/**
	 * reports was template loaded from cache or not
	 * [correct value if used isCached()]
	 * @var bool
	 */
	protected $isLoadedFromCache = FALSE;

	/**
	 * template name
	 * @var string
	 */
	public $templateName;

	/**
	 * creates and configures Smarty object
	 */
	public function __construct()
	{
		parent::__construct();

		$this->setTemplateDir(Registry::getInstance()->rootDirectory . '/templates/');
		$this->setCompileDir(Registry::getInstance()->rootDirectory . '/templates/compiled/');
		$this->setConfigDir(Registry::getInstance()->rootDirectory . '/templates/config/');
		$this->setCacheDir(Registry::getInstance()->rootDirectory . '/templates/cache/');

		if (Registry::getInstance()->cachingEnabled) {
			$this->caching = Smarty::CACHING_LIFETIME_CURRENT;
		} else {
			$this->disableCaching();
			//$this->force_compile = TRUE;
		}
	}

	/**
	 * set template name to load
	 * @param string $templateName
	 */
	public function setTemplateName($templateName)
	{
		$this->templateName = $templateName;
	}

	/**
	 * get template name, set by setTemplateName()
	 * @return string
	 */
	public function getTemplateName()
	{
		return $this->templateName;
	}

	/**
	 * get cache id
	 * @return null|string
	 */
	public function getCacheId()
	{
		return $this->cache_id;
	}

	/**
	 * assign cache id
	 * @param string $settedCacheId
	 * @return bool|void
	 */
	public function setCacheId($settedCacheId)
	{
		$this->cache_id = $settedCacheId;
	}

	public function isCached($template = NULL, $cache_id = NULL, $compile_id = NULL, $parent = NULL)
	{
		$this->isLoadedFromCache = parent::isCached($template, $cache_id, $compile_id, $parent);
		return $this->isLoadedFromCache;
	}


	/**
	 * disable caching functions of this instance of Smarty
	 */
	public function disableCaching()
	{
		$this->caching = Smarty::CACHING_OFF;
	}

	/**
	 * extend current template with another
	 * @param string $template
	 */
	public function extend($template)
	{
		if (!empty($this->templateName)) {
			$this->templateName = "extends:{$this->templateName}|{$template}";
		} else {
			$this->templateName = $template;
		}
	}

	/**
	 * return output as string
	 * @param array $data - data to render with
	 * @return string
	 */
	public function render($data)
	{
		$this->assign($data);
		return $this->fetch($this->templateName);
	}

	/**
	 * render and display output
	 */
	public function displayGenerated()
	{
		$this->display($this->templateName);
		if(Registry::getInstance()->showDebug){
			print $this->debugCache;
		}
	}

	/**
	 * display debug at the end of template
	 */
	public function showDebug()
	{
		$args = func_get_args();

		//$this->extend('debugInfo.tpl');

		ob_start();
		print '<pre>';
		$times = Registry::getInstance()->calculateExecutionStatistics();
		echo "exec time:{$times['executionTime']}sec".
		"\nmemory:{$times['memoryPeakUsage']}".
		"\ndbQueries:{$times['dbQueriesTotal']}\r".
		"\nisLoadedFromCache:";
		echo ($this->isLoadedFromCache)?'true':'false';
		echo "\r\n";
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
			print "OUTPUT VARDUMP\r\n";
			var_dump($this->tpl_vars);
		}
		print '</pre>';
		$contents = ob_get_contents();
		ob_end_clean();
		$this->debugCache = $contents;
	}

}