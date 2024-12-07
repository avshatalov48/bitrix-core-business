<?php

/**
 * @global CMain $APPLICATION
 */

if (!check_bitrix_sessid())
{
	return;
}

global $obModule;
if (!is_object($obModule))
{
	return;
}

if (!empty($obModule->errors) && is_array($obModule->errors))
{
	CAdminMessage::ShowMessage([
		"TYPE" => "ERROR",
		"MESSAGE" => GetMessage("MOD_UNINST_ERR"),
		"DETAILS" => implode("<br>", $obModule->errors),
		"HTML" => true,
	]);
}
else
{
	CAdminMessage::ShowNote(GetMessage("MOD_UNINST_OK"));
}
?>
<form action="<?= $APPLICATION->GetCurPage() ?>">
	<input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
	<input type="submit" name="" value="<?= htmlspecialcharsbx(GetMessage("MOD_BACK")) ?>">
<form>