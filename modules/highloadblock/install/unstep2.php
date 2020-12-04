<?
if (!check_bitrix_sessid())
	return;

if ($ex = $APPLICATION->GetException())
	CAdminMessage::ShowMessage(array(
		"TYPE" => "ERROR",
		"MESSAGE" => GetMessage("MOD_UNINST_ERR"),
		"DETAILS" => $ex->GetString(),
		"HTML" => true,
	));
else
	CAdminMessage::ShowNote(GetMessage("MOD_UNINST_OK"));
?>
<form action="<?echo $APPLICATION->GetCurPage(); ?>">
	<input type="hidden" name="lang" value="<?echo LANGUAGE_ID; ?>">
	<input type="submit" name="" value="<?echo GetMessage("MOD_BACK"); ?>">
<form>
