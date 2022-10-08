<?
if(!check_bitrix_sessid()) return;
IncludeModuleLangFile(__FILE__);

if($ex = $APPLICATION->GetException())
{
	CAdminMessage::ShowMessage(array(
		"TYPE" => "ERROR",
		"MESSAGE" => GetMessage("MOD_INST_ERR"),
		"DETAILS" => $ex->GetString(),
		"HTML" => true,
	));
}
else
{
	CAdminMessage::ShowMessage(array(
		"TYPE" => "OK",
		"MESSAGE" => GetMessage("MOD_INST_OK"),
		"HTML" => true,
	));

	echo BeginNote();
	echo GetMessage("LOCATION_INSTALL_STEP1_INSTALL_KEY");
	echo EndNote();
}

?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
	<form>
