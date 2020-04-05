<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<tr>
	<td align="right" width="40%" valign="top"><span class="adm-required-field"><?= GetMessage("BPCA_PD_PHP") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField('text', 'execute_code', $arCurrentValues['execute_code'], array('rows' => 20, 'cols' => 70))?>
	</td>
</tr>