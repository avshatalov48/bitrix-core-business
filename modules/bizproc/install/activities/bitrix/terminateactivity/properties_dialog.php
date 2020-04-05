<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<tr>
	<td align="right" width="40%" valign="top"><?= GetMessage("BPTA1_PD_STATE_TITLE") ?>:</td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'state_title', $arCurrentValues['state_title'], array('size'=>'50'))?>
	</td>
</tr>