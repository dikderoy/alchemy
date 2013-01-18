{extends file="General.tpl"}
{block name="head-extended"}
<link href="/css_project/main.css" rel="stylesheet">
{/block}
{block name="main"}
<div class="container">
	<div class="row-fluid">
		<div class="span2 rounded rounded-well">
			<img src="/img_project/index/face.jpg">
			<address class="rounded-caption">
				<strong>Bulgakov Roman</strong><br>
				PHP Developer<br>
			</address>
		</div>

		<div class="span10">
			{if isset($error_info)}
				{block name="error"}
				<div class="alert alert-error">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					{$error_info}
				</div>
				{/block}
			{/if}
			{block name="content"}
			{$content}
			{/block}
		</div>
	</div>
</div>
{/block}