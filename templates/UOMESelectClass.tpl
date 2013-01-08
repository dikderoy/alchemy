{extends file="UOMEGeneral.tpl"}
{block name="control"}
	<form class="form-horizontal" action="{$form_action}" method="POST">
		<div class="control-group">
			<label class="control-label" for="className">Class Name:</label>
			<div class="controls">
				<input type="text" id="className" placeholder="Class Name" name="class_name" required="required">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="objectID">Object ID:</label>
			<div class="controls">
				<input type="text" id="objectID" placeholder="object ID" name="object_id">
			</div>
		</div>
		<div class="form-actions">
			<button type="submit" class="btn btn-primary">Search</button>
			<button type="reset" class="btn">Clear</button>
		</div>
	</form>
{/block}