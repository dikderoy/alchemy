<?php

/**
 * Router depends on directory like routing
 * e.g. handle addresses like site.com[/controller][/action][/id][-any other shit you'd like to attach]
 *
 * @author Deroy
 */
class RouterDir extends Router implements IRouter
{

	public function parseRequest()
	{
		$t = explode('?', $this->request);
		$this->request = explode('/', $t[0]);

		if ($this->request[1] == 'admin') {
			$this->isACP = TRUE;
			$index = 2;
		} else {
			$index = 1;
		}

		$this->controller = $this->params['controller'] = ucfirst(strtolower($this->request[$index]));
		$this->action = $this->params['action'] = ucfirst(strtolower($this->request[$index + 1]));
		$this->id = $this->params['id'] = $this->request[$index + 2];
		$this->params['rest'] = $t[1];

		if (empty($this->controller)) {
			$this->controller = Registry::getInstance()->defaultController;
			//throw new Exception('controller parameter is empty, controller was set to default');
		}
	}

	/**
	 * construct a link to specified controller[,action,[id]]
	 * according to rules of link parsing
	 * @param string $controller
	 * @param null|string $action
	 * @param null|string|int $id
	 * @return string
	 */
	public function makeLink($controller, $action = NULL, $id = NULL)
	{
		$link = '/'.$controller;
		if($action!==NULL) {
			$link .= '/'.$action;
		}
		if($id!==NULL) {
			$link .= '/'.$id;
		}
		return $link;
	}


}