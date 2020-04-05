<?
if(!check_bitrix_sessid()) return;

global $obModule;
if(!is_object($obModule)) return;

if(is_array($obModule->errors) && count($obModule->errors))
	echo CAdminMessage::ShowMessage(Array("TYPE"=>"ERROR", "MESSAGE" =>GetMessage("MOD_UNINST_ERR"), "DETAILS"=>implode("<br>", $obModule->errors), "HTML"=>true));
else
	echo CAdminMessage::ShowNote(GetMessage("MOD_UNINST_OK"));
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
<form>