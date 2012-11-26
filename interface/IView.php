<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author Deroy
 */
interface IView
{
	public function render($data);

	public function assign($data);
}

?>
