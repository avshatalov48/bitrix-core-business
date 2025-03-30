<?php
/** @global CMain $APPLICATION */

if (!check_bitrix_sessid())
{
	return;
}

$ex = $APPLICATION->GetException();
if ($ex)
{
	CAdminMessage::ShowMessage([
		"TYPE" => "ERROR",
		"MESSAGE" => GetMessage("MOD_UNINST_ERR"),
		"DETAILS" => $ex->GetString(),
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
	<input type="submit" name="" value="<?= GetMessage("MOD_BACK") ?>">
<form>
