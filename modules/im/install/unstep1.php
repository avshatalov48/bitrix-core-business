<?php

/**
 * @global CAllMain $APPLICATION
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

if ($exception = $APPLICATION->GetException())
{
	CAdminMessage::showMessage([
		'TYPE' => 'ERROR',
		'MESSAGE' => Loc::getMessage('MOD_UNINST_ERR'),
		'DETAILS' => $exception->getString(),
		'HTML' => true,
	]);
	?>
	<form action="<?= $APPLICATION->GetCurPage() ?>">
		<p>
			<input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
			<input type="submit" name="" value="<?= Loc::getMessage('MOD_BACK') ?>">
		</p>
	</form>
	<?php
}
else
{
?>
	<form action="<?= $APPLICATION->GetCurPage()?>">
		<?=bitrix_sessid_post()?>
		<input type="hidden" name="lang" value="<?= LANG?>">
		<input type="hidden" name="id" value="im">
		<input type="hidden" name="uninstall" value="Y">
		<input type="hidden" name="step" value="2">
		<?php CAdminMessage::ShowMessage(Loc::getMessage("MOD_UNINST_WARN"))?>
		<p><?= Loc::getMessage("MOD_UNINST_SAVE")?></p>
		<p><input type="checkbox" name="savedata" id="savedata" value="Y" checked><label for="savedata"><?= Loc::getMessage("MOD_UNINST_SAVE_TABLES")?></label></p>
		<p><?= Loc::getMessage("MOD_UNINST_SAVE_EMAIL")?></p>
		<p><input type="checkbox" name="saveemails" id="saveemails" value="Y" checked><label for="saveemails"><?= Loc::getMessage("MOD_UNINST_SAVE_EMAILS")?></label></p>
		<input type="submit" name="inst" value="<?= Loc::getMessage("MOD_UNINST_DEL")?>">
	</form>
	<?php
}
