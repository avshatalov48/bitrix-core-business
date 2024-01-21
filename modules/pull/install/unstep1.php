<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

?>
<form action="<?= $APPLICATION->GetCurPage()?>">
	<?= \bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?= LANG?>">
	<input type="hidden" name="id" value="pull">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="step" value="2">

	<?
	\Bitrix\Main\Loader::includeModule('pull');
	\CPullOptions::ClearCheckCache();
	$arDependentModule = [];
	$ar = \CPullOptions::GetDependentModule();
	foreach ($ar as $key => $value)
	{
		$arDependentModule[] = $value['MODULE_ID'];
	}

	if (empty($arDependentModule))
	{
		\CAdminMessage::ShowMessage(Loc::getMessage("MOD_UNINST_WARN"));
	}
	else
	{
		\CAdminMessage::ShowMessage(
			Loc::getMessage("PULL_WARNING_MODULE", ['#BR#' => '<br />', '#MODULE#' => implode(", ", $arDependentModule)])
		);
	}
	?>
	<p><?= Loc::getMessage("MOD_UNINST_SAVE")?></p>
	<p><input type="checkbox" name="savedata" id="savedata" value="Y" checked><label for="savedata"><?= Loc::getMessage("MOD_UNINST_SAVE_TABLES")?></label></p>
	<input type="submit" name="inst" value="<?= Loc::getMessage("MOD_UNINST_DEL")?>">
</form>