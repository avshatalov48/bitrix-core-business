<?
if(!check_bitrix_sessid())
	return;
IncludeModuleLangFile(__FILE__);

$ex = $APPLICATION->GetException();
if ($ex)
{
	$msg = new CAdminMessage(array(
		"TYPE" => "ERROR",
		"MESSAGE" => GetMessage("MOD_INST_ERR"),
		"DETAILS" => $ex->GetString(),
		"HTML" => true,
	));
}
else
{
	$msg = new CAdminMessage(array(
		"TYPE" => "OK",
		"MESSAGE" => GetMessage("MOD_INST_OK"),
	));
}
$msg->Show();

?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
<form>