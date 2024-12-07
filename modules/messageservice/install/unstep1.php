<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

if (isset($messageservice_installer_errors) && is_array($messageservice_installer_errors) && (count($messageservice_installer_errors) > 0))
{
	$errors = "";
	foreach ($messageservice_installer_errors as $e)
	{
		$errors .= htmlspecialcharsbx($e)."<br>";
	}
	\CAdminMessage::ShowMessage([
		'TYPE' => "ERROR",
		'MESSAGE' => Loc::getMessage("MOD_UNINST_ERR"),
		'DETAILS' => $errors,
		'HTML' => true
	]);
	?>
	<form action="<?= $APPLICATION->GetCurPage()?>">
		<input type="hidden" name="lang" value="<?= LANG?>">
		<input type="submit" name="" value="<?= Loc::getMessage("MOD_BACK")?>">
	</form>
	<?
}
else
{
	?>
	<form action="<?= $APPLICATION->GetCurPage()?>">
		<?=bitrix_sessid_post()?>
		<input type="hidden" name="lang" value="<?= LANGUAGE_ID?>">
		<input type="hidden" name="id" value="messageservice">
		<input type="hidden" name="uninstall" value="Y">
		<input type="hidden" name="step" value="2">
		<? CAdminMessage::ShowMessage(Loc::getMessage("MOD_UNINST_WARN"))?>
		<p><?= Loc::getMessage("MOD_UNINST_SAVE")?></p>
		<p><input type="checkbox" name="savedata" id="savedata" value="Y" checked><label for="savedata"><?= Loc::getMessage("MOD_UNINST_SAVE_TABLES")?></label></p>
		<input type="submit" name="inst" value="<?= Loc::getMessage("MOD_UNINST_DEL")?>">
	</form>
	<?
}
