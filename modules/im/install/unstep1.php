<?php
IncludeModuleLangFile(__FILE__);
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="hidden" name="id" value="im">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="step" value="2">
	<?echo CAdminMessage::ShowMessage(GetMessage("MOD_UNINST_WARN"))?>
	<p><?echo GetMessage("MOD_UNINST_SAVE")?></p>
	<p><input type="checkbox" name="savedata" id="savedata" value="Y" checked><label for="savedata"><?echo GetMessage("MOD_UNINST_SAVE_TABLES")?></label></p>
	<p><?echo GetMessage("MOD_UNINST_SAVE_EMAIL")?></p>
	<p><input type="checkbox" name="saveemails" id="saveemails" value="Y" checked><label for="saveemails"><?echo GetMessage("MOD_UNINST_SAVE_EMAILS")?></label></p>
	<input type="submit" name="inst" value="<?echo GetMessage("MOD_UNINST_DEL")?>">
</form>