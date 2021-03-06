<b>{'common.context'|devblocks_translate|capitalize}:</b> <i>(e.g. "ticket")</i>
<div style="margin-left:10px;margin-bottom:10px;">
	<input type="text" name="{$namePrefix}[context]" class="placeholders" spellcheck="false" style="width:100%;" value="{$params.context}" placeholder="e.g. ticket">
</div>

<b>{'common.query'|devblocks_translate}:</b> <i>(e.g. name:"Exact Match")</i>
<div style="margin-left:10px;margin-bottom:10px;">
	<textarea name="{$namePrefix}[query]" class="placeholders" spellcheck="false" style="width:100%;">{$params.query}</textarea>
</div>

<b>{'common.changeset'|devblocks_translate|capitalize}:</b> (JSON)
<div style="margin-left:10px;margin-bottom:10px;">
	<textarea name="{$namePrefix}[changeset_json]" class="placeholders" spellcheck="false" rows="5" style="width:100%;">{$params.changeset_json}</textarea>
</div>

<b>Also upsert records in simulator mode:</b>
<div style="margin-left:10px;margin-bottom:10px;">
	<label><input type="radio" name="{$namePrefix}[run_in_simulator]" value="1" {if $params.run_in_simulator}checked="checked"{/if}> {'common.yes'|devblocks_translate|capitalize}</label>
	<label><input type="radio" name="{$namePrefix}[run_in_simulator]" value="0" {if !$params.run_in_simulator}checked="checked"{/if}> {'common.no'|devblocks_translate|capitalize}</label>
</div>

<b>Save record dictionary to a placeholder named:</b><br>
<div style="margin-left:10px;margin-bottom:10px;">
	&#123;&#123;<input type="text" name="{$namePrefix}[object_placeholder]" value="{$params.object_placeholder|default:"_record"}" required="required" spellcheck="false" size="32" placeholder="e.g. _record">&#125;&#125;
</div>

<script type="text/javascript">
$(function() {
	var $action = $('#{$namePrefix}_{$nonce}');
});
</script>
