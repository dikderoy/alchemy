{extends file="UOMEGeneral.tpl"}
{block name="control"}
<form class="form-horizontal" action="{$form_action}" method="POST">
	<fieldset>
		<legend>Select fields to display in table:</legend>
		<div class="row-fluid">
		<div class="control-group span4">
			{foreach $object_structure as $field}
				<label class="checkbox">
					<input type="checkbox" value="{$field@key}" name="fields[]">
					{$field@index+1} {$field@key}
					{if $field.required}
						<span class="badge badge-important">required</span>
					{/if}
				</label>
				{if $field@iteration is div by ceil($field@total/3)}
				</div>
				<div class="control-group span4">
				{/if}
			{/foreach}
		</div>
		</div>
		<div class="form-actions">
			<button type="submit" class="btn btn-primary">Apply</button>
			<button type="reset" class="btn">Clear</button>
		</div>
	</fieldset>
</form>
{/block}
{block name="inner-content"}
	<a href="{$object_edit_link}" class="btn btn-block btn-inverse">Create New Object of {$object_classname} class</a>
	{foreach $list as $object}
		{if $object@first}
		<div class="well well-small">Total Objects listed:{$object@total}</div>
		<table class="table table-hover">
			<thead>
			<tr>
				<th></th>
				{foreach $object as $field}
					<th>{$field@key}</th>
				{/foreach}
			</tr>
			</thead>
		<tbody>
		{/if}
	<tr>
		<td>
			<a href="{$object_edit_link}{$object.{$object_identifier}}" class="btn">
				<i class="icon-edit"></i>
			</a>
		</td>
		{foreach $object as $field}
			<td>{$field}</td>
		{/foreach}
	</tr>
		{if $object@last}
		</tbody>
		</table>
		{/if}
		{foreachelse}
	<div class="alert alert-info">
		Object List is not available
	</div>
	{/foreach}
{/block}