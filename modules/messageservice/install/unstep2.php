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
}
else
{
	echo CAdminMessage::ShowNote(Loc::getMessage("MOD_UNINST_OK"));
}
?>
<form action="<?= $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?= LANG?>">
	<input type="submit" name="" value="<?= Loc::getMessage("MOD_BACK")?>">
</form>
