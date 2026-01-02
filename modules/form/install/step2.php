<?php

/**
 * @global bool|array $errors
 * @global CMain $APPLICATION
 */

if(!check_bitrix_sessid())
{
	return;
}
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/install/install.php");

if (is_array($errors) && !empty($errors))
{
	CAdminMessage::ShowMessage(Array("TYPE" => "ERROR", "MESSAGE" => GetMessage("MOD_INST_ERR"), "DETAILS" => implode('<br>', $errors), "HTML" => true));
}
else
{
	CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
}
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
</form>
