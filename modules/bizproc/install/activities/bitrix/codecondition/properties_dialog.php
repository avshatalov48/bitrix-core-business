<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<tr>
	<td align="right" width="40%" valign="top"><?= GetMessage("BPCC_PD_CODE") ?>:</td>
	<td width="60%" valign="top">
		<textarea rows="3" cols="40" name="php_code_condition"><?= htmlspecialcharsbx($arCurrentValues["php_code_condition"]) ?></textarea>
	</td>
</tr>