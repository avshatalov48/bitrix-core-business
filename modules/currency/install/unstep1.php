<?php
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
foreach (GetModuleEvents('currency', 'OnModuleUnInstall', true) as $arEvent)
{
	if ($arEvent["TO_CLASS"] == '')
		$arEvent["CALLBACK"] = $arEvent["TO_METHOD"];
	ExecuteModuleEventEx($arEvent);
}

$ex = $APPLICATION->GetException();
if ($ex)
{
	CAdminMessage::ShowMessage(GetMessage('CURRENCY_INSTALL_UNPOSSIBLE').'<br />'.$ex->GetString());
	?>
	<form action="<?= $APPLICATION->GetCurPage() ?>">
	<p>
		<input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
		<input type="submit" name="" value="<?= htmlspecialcharsbx(GetMessage('MOD_BACK')) ?>">
	</p>
	<form>
	<?php
}
else
{
	?>
	<form action="<?= $APPLICATION->GetCurPage() ?>">
	<?= bitrix_sessid_post() ?>
		<input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
		<input type="hidden" name="id" value="currency">
		<input type="hidden" name="uninstall" value="Y">
		<input type="hidden" name="step" value="2">
		<?php
		CAdminMessage::ShowMessage(GetMessage('MOD_UNINST_WARN'));
		?>
		<p><?= GetMessage('MOD_UNINST_SAVE') ?></p>
		<input type="hidden" name="savedata" id="savedata_N" value="N">
		<p><input type="checkbox" name="savedata" id="savedata" value="Y" checked><label for="savedata"><?= GetMessage('MOD_UNINST_SAVE_TABLES') ?></label></p>
		<input type="submit" name="inst" value="<?= htmlspecialcharsbx(GetMessage('MOD_UNINST_DEL')) ?>">
	</form>
	<?php
}
