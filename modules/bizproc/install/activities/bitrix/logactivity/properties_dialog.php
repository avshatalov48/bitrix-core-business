<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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