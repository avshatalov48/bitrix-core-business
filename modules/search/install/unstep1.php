<?IncludeModuleLangFile(__FILE__);?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
	<input type="hidden" name="id" value="search">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="step" value="2">
	<?echo CAdminMessage::ShowMessage(GetMessage("MOD_UNINST_WARN"))?>
	<p><?echo GetMessage("MOD_UNINST_SAVE")?></p>
	<p><label><input type="checkbox" name="savedata" id="savedata" value="Y" checked><?echo GetMessage("MOD_UNINST_SAVE_TABLES")?></label></p>
	<p><label><input type="checkbox" name="savestat" id="savestat" value="Y" checked><?echo GetMessage("SEARCH_SAVE_STAT_TABLES")?></label></p>
	<input type="submit" name="inst" value="<?echo GetMessage("MOD_UNINST_DEL")?>">
</form>