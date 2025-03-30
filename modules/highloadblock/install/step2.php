<?php
if (!check_bitrix_sessid())
{
	return;
}

/** @global CMain $APPLICATION */

IncludeModuleLangFile(__FILE__);
$ex = $APPLICATION->GetException();
if ($ex)
{
	CAdminMessage::ShowMessage([
		"TYPE" => "ERROR",
		"MESSAGE" => GetMessage("MOD_INST_ERR"),
		"DETAILS" => $ex->GetString(),
		"HTML" => true,
	]);
}
else
{
	CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
}
?>
<form action="<?= $APPLICATION->GetCurPage() ?>">
	<input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
	<input type="submit" name="" value="<?= GetMessage("MOD_BACK") ?>">
<form>