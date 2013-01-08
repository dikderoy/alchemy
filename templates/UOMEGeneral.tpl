{extends file="General.tpl"}
{block name="content"}
<div class="page-header"><h1>{$page_title}</h1></div>
<div class="row-fluid">
	<div class="span8">
		<div class="well well-small">
			{block name="control"}{/block}
		</div>
		<div>
			{block name="inner-content"}{/block}
		</div>
	</div>
	<div class="span4 well well-small">
		{foreach $object_structure as $field}
			{if $field@first}
			<table class="table table-bordered">
				<thead>
				<tr>
					<th colspan="2">Object Class</th>
					<td colspan="3">{$object_classname}</td>
				</tr>
				<tr>
					<th>#</th>
					<th>Field</th>
					<th>Type</th>
					<th>Size</th>
					<th>req.</th>
				</tr>
				</thead>
			<tbody>
			{/if}
		<tr>
			<td>{$field@index+1}</td>
			<th>{$field@key}</th>
			<td>{$field.type}</td>
			<td>{$field.size}</td>
			<td>
				{if $field.required}
					<span class="badge badge-important">yes</span>
					{else}
					<span class="badge">no</span>
				{/if}
			</td>
		</tr>
			{if $field@last}
			</tbody>
			</table>
			{/if}
			{foreachelse}
			<div class="alert alert-info">
				Object structure info is not available
			</div>
		{/foreach}
	</div>
</div>
{/block}