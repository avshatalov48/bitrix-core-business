<?IncludeModuleLangFile(__FILE__);
if(!check_bitrix_sessid())
		return;

global $obModule;
if($obModule->errors != false)
	CAdminMessage::ShowMessage(Array("TYPE"=>"ERROR", "MESSAGE" =>GetMessage("MOD_INST_ERR"), "DETAILS"=>implode("<br/>", $obModule->errors), "HTML"=>true));
else
	CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
?>
<form action="<?=$APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="submit" name="" value="<?=GetMessage("MOD_BACK")?>">
<form>
