{$cf_id = str_replace('cf_', '', $k)}
<b>{$v.label|capitalize}:</b>
{if $v.type == Model_CustomField::TYPE_CHECKBOX}
	{if $v.value}{'common.yes'|devblocks_translate}{else}{'common.no'|devblocks_translate}{/if}
{elseif $v.type == Model_CustomField::TYPE_DATE}
	<abbr title="{$v.value|devblocks_date}">{$v.value|devblocks_prettytime}</abbr>
{elseif $v.type == Model_CustomField::TYPE_CURRENCY}
	{$currency = DAO_Currency::get($v.params.currency_id)}
	{if $currency}
	{$currency->symbol} {DevblocksPlatform::strFormatDecimal($v.value, $currency->decimal_at)} {$currency->code}
	{else}
	{$v.value}
	{/if}
{elseif $v.type == Model_CustomField::TYPE_DECIMAL}
	{$decimal_at = $v.params.decimal_at}
	{if $decimal_at}
	{DevblocksPlatform::strFormatDecimal($v.value, $decimal_at)}
	{else}
	{$v.value}
	{/if}
{elseif $v.type == Model_CustomField::TYPE_SINGLE_LINE}
	{$v.value|escape|devblocks_hyperlinks nofilter}
{elseif $v.type == Model_CustomField::TYPE_MULTI_LINE}
	{if $v.value}
		{if strlen($v.value) > 128 || false != strpos($v.value,"\n")}
		<span>
			{$v.value|truncate:128} [<a href="javascript:;" onclick="$(this).parent().next('div').fadeIn().end().hide();">expand</a>]
		</span>
		<div style="display:none;">
			{$v.value|escape|devblocks_hyperlinks|nl2br nofilter}
			<br>
			[<a href="javascript:;" onclick="$(this).parent().hide().prev('span').fadeIn();">collapse</a>]
		</div>
		{else}
			{$v.value}
		{/if}
	{/if}
{elseif $v.type == Model_CustomField::TYPE_URL}
	<a href="{$v.value}" target="_blank" rel="noopener">{$v.value}</a>
{elseif $v.type == Model_CustomField::TYPE_WORKER}
	{if !isset($workers)}{$workers = DAO_Worker::getAll()}{/if}
	{if isset($workers.{$v.value})}
		<ul class="bubbles">
			<li>
				<a href="javascript:;" class="cerb-peek-trigger" data-context="{CerberusContexts::CONTEXT_WORKER}" data-context-id="{$v.value}">{$workers.{$v.value}->getName()}</a>
			</li>
		</ul>
	{/if}
{elseif $v.type == Model_CustomField::TYPE_LIST}
	{if is_array($v.value)}
		{$v.value|implode:', '}
	{/if}
{elseif $v.type == Model_CustomField::TYPE_MULTI_CHECKBOX}
	{if is_array($v.value)}
		{$v.value|implode:', '}
	{/if}
{elseif $v.type == Model_CustomField::TYPE_LINK}
	{$link_context_ext = Extension_DevblocksContext::get($v.params.context)}
	{if $link_context_ext && $v.value}
		{$link_meta = $link_context_ext->getMeta($v.value)}
		{if $link_meta && ($link_context_ext->id == CerberusContexts::CONTEXT_APPLICATION || $v.value)}
			<ul class="bubbles">
			<li class="bubble-gray">
				{if $link_context_ext->id == CerberusContexts::CONTEXT_APPLICATION}
				<img src="{devblocks_url}c=avatars&context=app&context_id={$v.value}{/devblocks_url}?v={$link_meta.updated}" style="height:16px;width:16px;vertical-align:middle;border-radius:16px;">
				{else}
					{if $v.value}
						{if $link_context_ext->id == CerberusContexts::CONTEXT_BOT}
						<img src="{devblocks_url}c=avatars&context=bot&context_id={$v.value}{/devblocks_url}?v={$link_meta.updated}" style="height:16px;width:16px;vertical-align:middle;border-radius:16px;">
						{elseif $link_context_ext->id == CerberusContexts::CONTEXT_WORKER}
						<img src="{devblocks_url}c=avatars&context=worker&context_id={$v.value}{/devblocks_url}?v={$link_meta.updated}" style="height:16px;width:16px;vertical-align:middle;border-radius:16px;">
						{elseif $link_context_ext->id == CerberusContexts::CONTEXT_CONTACT}
						<img src="{devblocks_url}c=avatars&context=contact&context_id={$v.value}{/devblocks_url}?v={$link_meta.updated}" style="height:16px;width:16px;vertical-align:middle;border-radius:16px;">
						{elseif $link_context_ext->id == CerberusContexts::CONTEXT_ORG}
						<img src="{devblocks_url}c=avatars&context=org&context_id={$v.value}{/devblocks_url}?v={$link_meta.updated}" style="height:16px;width:16px;vertical-align:middle;border-radius:16px;">
						{elseif $link_context_ext->id == CerberusContexts::CONTEXT_ADDRESS}
						<img src="{devblocks_url}c=avatars&context=address&context_id={$v.value}{/devblocks_url}?v={$link_meta.updated}" style="height:16px;width:16px;vertical-align:middle;border-radius:16px;">
						{elseif $link_context_ext->id == CerberusContexts::CONTEXT_GROUP}
						<img src="{devblocks_url}c=avatars&context=group&context_id={$v.value}{/devblocks_url}?v={$link_meta.updated}" style="height:16px;width:16px;vertical-align:middle;border-radius:16px;">
						{/if}
					{/if}
				{/if}
				
				{if $link_meta.permalink}
					<a href="javascript:;" class="cerb-peek-trigger" data-context="{$v.params.context}" data-context-id="{$v.value}">{$link_meta.name|truncate:64}</a>
				{else}
					{$link_meta.name|truncate:64}
				{/if}
			</li>
			</ul>
		{/if}
	{/if}
{elseif $v.type == Model_CustomField::TYPE_FILE}
	{$file_id = $v.value}
	{$file = DAO_Attachment::get($file_id)}
	{if $file}
	<ul class="bubbles">
		<li>
			<a href="javascript:;" class="cerb-peek-trigger" data-context="{CerberusContexts::CONTEXT_ATTACHMENT}" data-context-id="{$file->id}">{$file->name} ({$file->storage_size|devblocks_prettybytes})</a>
		</li>
	</ul>
	{/if}
{elseif $v.type == Model_CustomField::TYPE_FILES}
	{foreach from=$v.value item=file_id name=files}
		{$file = DAO_Attachment::get($file_id)}
		{if $file}
		<a href="{devblocks_url}c=files&id={$file->id}&file={$file->name|escape:'url'}{/devblocks_url}" target="_blank" rel="noopener">{$file->name}</a> ({$file->storage_size|devblocks_prettybytes}){if !$smarty.foreach.files.last}, {/if}
		{/if}
	{/foreach}
{else}
	{$v.value}
{/if}