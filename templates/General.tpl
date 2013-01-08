<!DOCTYPE html>
<html>
<head>
{block name="head"}
	<meta charset="utf-8">
	<title>{$page_title}</title>
	<meta name="description" content="{$page_description}">
	<meta name="author" content="Deroy">

	<!-- Le styles -->
	<link href="/css/bootstrap.css" rel="stylesheet">
	<link href="/css/bootstrap-responsive.css" rel="stylesheet">
	{literal}
		<style>
			body {
				padding-top: 60px;
			}
		</style>
	{/literal}
	<script type="text/javascript" src="/js/jquery-1.8.3.min.js"></script>
	<script type="text/javascript" src="/js/bootstrap.min.js"></script>
	<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
{/block}
{block name="head-extended"}
{/block}
</head>
<body>
{block name="body"}
	{block name="top-navbar"}
	<div class="navbar navbar-fixed-top navbar-inverse">
		<div class="navbar-inner">
			<div class="container">
				<a class="brand" href="/">{$site_title}</a>
				<ul class="nav">
					<li class="active"><a href="{$home_link}"><i class="icon-th-large"></i>Home</a></li>
					{*
					format for $menu is array of arrays with following structure:
						[
							link => {href contents},
							name => {name and title contents},
							current => TRUE|FALSE
						]
					*}
					{foreach $menu as $item}
						{if $item.current}
						<li class="active"><a href="{$item.link}">{$item.name}</a></li>
						{else}
						<li><a href="{$item.link}">{$item.name}</a></li>
						{/if}
					{/foreach}
				</ul>
			</div>
		</div>
	</div>
	{/block}
	{block name="main"}
	<div class="container">
		{if isset($error_info)}
			{block name="error"}
				<div class="alert alert-error">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					{$error_info}
				</div>
			{/block}
		{/if}
		{block name="content"}
		{/block}
	</div>
	{/block}
{/block}
{block name="debug"}
{/block}
</body>
</html>