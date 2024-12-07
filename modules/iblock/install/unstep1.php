<?php

/**
 * @global CMain $APPLICATION
 */

IncludeModuleLangFile(__FILE__);

foreach (GetModuleEvents('iblock', 'OnModuleUnInstall', true) as $arEvent)
{
	ExecuteModuleEventEx($arEvent);
}

$ex = $APPLICATION->GetException();
if ($ex):
	$message = new CAdminMessage(GetMessage('MOD_UNINST_IMPOSSIBLE'), $ex);
	?>
	<form action="<?= $APPLICATION->GetCurPage() ?>">
		<?= $message->show() ?>
		<input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
		<input type="submit" name="inst" value="<?= htmlspecialcharsbx(GetMessage('MOD_UNINST_BACK')) ?>">
	<form>
<?php
else:
?>
	<form action="<?= $APPLICATION->GetCurPage() ?>">
		<?=bitrix_sessid_post(); ?>
		<input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
		<input type="hidden" name="id" value="iblock">
		<input type="hidden" name="uninstall" value="Y">
		<input type="hidden" name="step" value="2">
		<?php
		CAdminMessage::ShowMessage(GetMessage('MOD_UNINST_WARN'));
		?>
		<p><?= GetMessage('MOD_UNINST_SAVE') ?></p>
		<p><input type="checkbox" name="savedata" id="savedata" value="Y" checked><label for="savedata"><?= GetMessage('MOD_UNINST_SAVE_TABLES') ?></label></p>
		<input type="submit" name="inst" value="<?= htmlspecialcharsbx(GetMessage('MOD_UNINST_DEL')) ?>">
	</form>
<?php
endif;
