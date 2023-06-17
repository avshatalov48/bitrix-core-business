<?if(!check_bitrix_sessid()) return;?>
<?
global $errors;


if(!is_array($errors) && $errors == '' || is_array($errors) && count($errors) <= 0)
{
	echo CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
	if (COption::GetOptionString("intranet", "calendar_2", "N") != "Y")
		echo "<form action=\"/bitrix/admin/calendar_convert.php\"><input type=\"hidden\" name=\"lang\" value=\"".LANG."\" /><input type=\"submit\" value=\"".GetMessage("CAL_GO_CONVERT")."\" />";
}
else
{
	for($i=0; $i<count($errors); $i++)
		$alErrors .= $errors[$i]."<br>";

	echo CAdminMessage::ShowMessage(Array("TYPE"=>"ERROR", "MESSAGE" =>GetMessage("MOD_INST_ERR"), "DETAILS"=>$alErrors, "HTML"=>true));
}

if ($ex = $APPLICATION->GetException())
	echo CAdminMessage::ShowMessage(Array("TYPE" => "ERROR", "MESSAGE" => GetMessage("MOD_INST_ERR"), "HTML" => true, "DETAILS" => $ex->GetString()));
?>

<form action="<?=$APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="submit" name="" value="<?=GetMessage('MOD_BACK')?>">
<form>
