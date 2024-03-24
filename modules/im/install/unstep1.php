<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

?>
<form action="<?= $APPLICATION->GetCurPage()?>">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?= LANG?>">
	<input type="hidden" name="id" value="im">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="step" value="2">
	<? \CAdminMessage::ShowMessage(Loc::getMessage("MOD_UNINST_WARN"))?>
	<p><?= Loc::getMessage("MOD_UNINST_SAVE")?></p>
	<p><input type="checkbox" name="savedata" id="savedata" value="Y" checked><label for="savedata"><?= Loc::getMessage("MOD_UNINST_SAVE_TABLES")?></label></p>
	<p><?= Loc::getMessage("MOD_UNINST_SAVE_EMAIL")?></p>
	<p><input type="checkbox" name="saveemails" id="saveemails" value="Y" checked><label for="saveemails"><?= Loc::getMessage("MOD_UNINST_SAVE_EMAILS")?></label></p>
	<input type="submit" name="inst" value="<?= Loc::getMessage("MOD_UNINST_DEL")?>">
</form>