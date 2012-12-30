<?php

/**
 * Route using GET parameters
 * handles addresses like site.com[?controller=controllername][&action=actionname][&id=objectID][&anyparam=anyvalue]
 *
 * @author Deroy
 */
class RouterGet extends Router implements IRouter
{

	public function parseRequest()
	{
		$t = explode('?', $this->request);
		$this->request = explode('/', $t[0]);

		if ($this->request[1] == 'admin') {
			$this->isACP = TRUE;
		}

		$this->controller = $this->params['controller'] = ucfirst(strtolower($_GET['controller']));
		$this->action = $this->params['action'] = ucfirst(strtolower($_GET['action']));
		$this->id = $this->params['id'] = $_GET['id'];
		$this->params['rest'] = $_GET;

		if (empty($this->controller)) {
			$this->controller = Registry::getInstance()->defaultController;
			//throw new Exception('controller parameter is empty, controller was set to default');
		}
	}

}