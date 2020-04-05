<?
IncludeModuleLangFile(__FILE__);
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="hidden" name="id" value="pull">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="step" value="2">

	<?
	CModule::IncludeModule('pull');
	CPullOptions::ClearCheckCache();
	$arDependentModule = Array();
	$ar = CPullOptions::GetDependentModule();
	foreach ($ar as $key => $value)
		$arDependentModule[] = $value['MODULE_ID'];

	if (empty($arDependentModule)):?>
		<?echo CAdminMessage::ShowMessage(GetMessage("MOD_UNINST_WARN"))?>
	<?else:?>
		<?echo CAdminMessage::ShowMessage(GetMessage("PULL_WARNING_MODULE", Array('#BR#' => '<br />', '#MODULE#' => implode(", ", $arDependentModule))))?>
	<?endif;?>
	<p><?echo GetMessage("MOD_UNINST_SAVE")?></p>
	<p><input type="checkbox" name="savedata" id="savedata" value="Y" checked><label for="savedata"><?echo GetMessage("MOD_UNINST_SAVE_TABLES")?></label></p>
	<input type="submit" name="inst" value="<?echo GetMessage("MOD_UNINST_DEL")?>">
</form>