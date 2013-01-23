{extends file="UOMESelectClass.tpl"}
{block name="inner-content"}
<form class="form-horizontal" action="{$form_action2}" method="post">
	<fieldset>
		<legend>Edit object fields contents:</legend>
		{foreach $object_structure as $field}
			<div class="control-group">
				{if $field.required and $field@key!=$object_identifier}
					{$req_attr = 'required="required"'}
					{else}
					{$req_attr = ''}
				{/if}
				<label class="control-label" for="{$field@key}">{$field@key}:</label>
				<div class="controls">
					{if $field.type == 'array' or $field.type == 'object'}
						<span class="uneditable-input">Array|Object values not editable yet</span>
						{elseif $field.size>100}
						<textarea id="{$field@key}"
								  name="{$field@key}"
							{$req_attr}>{$object_instance.{$field@key}}</textarea>
						{else}
						<input id="{$field@key}" name="{$field@key}" type="text" {$req_attr}
							   value="{$object_instance.{$field@key}}">
					{/if}
				</div>
			</div>
		{/foreach}
	</fieldset>
	<div class="form-actions">
		<button type="submit" class="btn btn-primary">Apply Changes</button>
		<button type="reset" class="btn">Reset</button>
	</div>
</form>
{/block}