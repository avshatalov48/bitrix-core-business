<?
foreach (GetModuleEvents('sale', 'OnModuleUnInstall', true) as $arEvent)
{
	if (strlen($arEvent["TO_CLASS"]) <= 0)
		$arEvent["CALLBACK"] = $arEvent["TO_METHOD"];
	ExecuteModuleEventEx($arEvent);
}

if ($ex = $APPLICATION->GetException())
{
	CAdminMessage::ShowMessage(GetMessage('SALE_UNINSTALL_IMPOSSIBLE').'<br />'.$ex->GetString());
	?>
	<form action="<? echo $APPLICATION->GetCurPage(); ?>">
		<p>
			<input type="hidden" name="lang" value="<? echo LANGUAGE_ID; ?>">
			<input type="submit" name="" value="<? echo GetMessage('MOD_BACK'); ?>">
		</p>
	</form>
	<?
}
else
{
	?>
	<form action="<?echo $APPLICATION->GetCurPage()?>">
	<?=bitrix_sessid_post()?>
		<input type="hidden" name="lang" value="<?echo LANGUAGE_ID?>">
		<input type="hidden" name="id" value="sale">
		<input type="hidden" name="uninstall" value="Y">
		<input type="hidden" name="step" value="2">
		<?echo CAdminMessage::ShowMessage(GetMessage("MOD_UNINST_WARN"))?>
		<p><?echo GetMessage("MOD_UNINST_SAVE")?></p>
		<p><input type="checkbox" name="savedata" id="savedata" value="Y" checked><label for="savedata"><?echo GetMessage("MOD_UNINST_SAVE_TABLES")?></label></p>
		<p><input type="checkbox" name="saveemails" id="saveemails" value="Y"><label for="saveemails"><?echo GetMessage("MOD_UNINST_SAVE_EVENTS")?></label></p>
		<input type="submit" name="inst" value="<?echo GetMessage("MOD_UNINST_DEL")?>">
	</form>
	<?
}