{extends file="General.tpl"}
{block name="main"}
<div class="container">
	<div class="row-fluid">
		<div class="span2 well myface">
			<img src="/img_project/index/face.jpg">
			<address>
				<strong>Bulgakov Roman</strong><br>
				PHP Developer<br>
			</address>
		</div>

		<div class="span10 well well-small">
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