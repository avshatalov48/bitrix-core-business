<?if(!check_bitrix_sessid()) return;
include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/lang/", "/install/index.php"));
if($GLOBALS["errors"]===false):
	CAdminMessage::ShowNote(GetMessage("MOD_UNINST_OK"));
else:
	CAdminMessage::ShowMessage(Array("TYPE"=>"ERROR", "MESSAGE" =>GetMessage("MOD_UNINST_ERR"), "DETAILS"=>implode("<br>", $GLOBALS["errors"]), "HTML"=>true));
endif;
?><form action="<?=$APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="submit" name="" value="<?=GetMessage("MOD_BACK")?>">
<form>