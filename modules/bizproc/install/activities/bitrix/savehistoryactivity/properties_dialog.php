<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<?
?>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPSHA_PD_NAME") ?>:<br /><small><?= GetMessage("BPSHA_PD_NAME_ALT") ?></small></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'sh_name', $arCurrentValues['sh_name'], Array('size'=> 46))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPSHA_PD_USER") ?>:</td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("user", 'sh_user_id', $arCurrentValues['sh_user_id'], Array('rows'=> 1))?>
	</td>
</tr>