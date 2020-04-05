<?
if(!check_bitrix_sessid()) return;

if($ex = $APPLICATION->GetException())
{
	$message = new CAdminMessage(array(
		"TYPE" => "ERROR",
		"MESSAGE" => GetMessage("MOD_UNINST_ERR"),
		"DETAILS" => $ex->GetString(),
		"HTML" => true,
	));
	echo $message->Show();
}
else
{
	echo CAdminMessage::ShowNote(GetMessage("MOD_UNINST_OK"));
}
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
<form>
