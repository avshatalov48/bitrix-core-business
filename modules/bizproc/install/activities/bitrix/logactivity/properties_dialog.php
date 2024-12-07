<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$isRestricted = CBPRuntime::getRuntime()->getTrackingService() instanceof \Bitrix\Bizproc\Service\RestrictedTracking;

?>
<tr>
	<td align="right" width="40%" valign="top"><span class="adm-required-field"><?= GetMessage("BPCAL_PD_TEXT") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("text", 'text', $arCurrentValues['text'], Array('rows'=> 7, 'cols' => 50))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPCAL_PD_SET_VAR") ?>:</td>
	<td width="60%">
		<input type="checkbox" name="set_variable" value="Y"<?= ($arCurrentValues["set_variable"] == "Y") ? " checked" : "" ?>>
	</td>
</tr>
<?php if ($isRestricted):?>
	<tr>
		<td align="right" width="40%"></td>
		<td width="60%" valign="top">
			<div class="ui-alert ui-alert-warning ui-alert-icon-info ui-alert-xs" style="box-sizing: border-box">
				<span class="ui-alert-message"><?= GetMessage("BPCAL_PD_RESCTICTED_TRACKING") ?></span>
			</div>
		</td>
	</tr>
<?php endif; ?>