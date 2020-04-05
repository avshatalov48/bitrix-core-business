<?php
if(!check_bitrix_sessid()) return;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__DIR__ . '/index.php');

if($GLOBALS["errors"]===false):
	CAdminMessage::ShowNote(Loc::getMessage("MOD_UNINST_OK"));
else:
	CAdminMessage::ShowMessage(Array("TYPE"=>"ERROR", "MESSAGE" =>Loc::getMessage("MOD_UNINST_ERR"), "DETAILS"=>implode("<br>", $GLOBALS["errors"]), "HTML"=>true));
endif;

?><form action="<?=$APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="submit" name="" value="<?=Loc::getMessage("MOD_BACK")?>">
<form>