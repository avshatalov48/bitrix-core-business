<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<tr>
	<td align="right" width="40%" valign="top"><span class="adm-required-field"><?= GetMessage("BPWHA_PD_TEXT") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("text", 'handler', $arCurrentValues['handler'], Array('rows'=> 7, 'cols' => 50))?>
	</td>
</tr>