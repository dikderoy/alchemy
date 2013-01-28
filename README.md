Alchemy Framework v.1.0
=======================

This is a lightweight framework for personal use and skill demonstration

NOTE: This product at its state on 28/01/2013 is still not yet ready for commercial out of box use

* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 

# About Project
## Objectives pursued
Create easy, not monstrous framework, which allows to quickly develop and deploy simple MVC applications

# Naming Rules:
## 1.Controllers
### Controller&lt;NameOfController&gt;.php
 for controllers which allowed to be called directly from querystring
 e.g. "http://example.com/NameOfController/NameOfAction/".
### &lt;NameOfSuperContoller&gt;Controller.php
 for super controllers which is needed only as Proxy classes - to redefine existing or
 add new behavior and should be extended from class/Controller.php.
 Those controllers arent available for direct call from querystring.

Controller files must be put into one of the following folders:
* /controller/ - basic and super controllers - if you decide to keep framework structure clean, do not place your controllers here
* /controller_project/ - user defined controllers and super controllers

### Overload Policy:
 if controllers with same &lt;NameOfController&gt; appeares in both folders then
 only one from /controller_project/ will be loaded into runtime enveronment.
## 2.Actions
Name of action method in controller consists of two parts:
1. action prefix
2. name of action
both are defined in Controller class or subclass of Controller as:
1. action prefix is defined with $actionPrefix protected property
2. name of action defined by you
e.g. action should look like this:

	public function actionShow($data)
	{
		.... some code ...
	}
	
where "action" is an action prefix
and Show is an action name

* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 

MORE INFO IS COMING SOON