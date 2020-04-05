<? 
IncludeModuleLangFile(__FILE__); 
if ($GLOBALS["uninstall_step"] == 2):
	if(!check_bitrix_sessid()) 
		return;
	if($ex = $APPLICATION->GetException())
		echo CAdminMessage::ShowMessage(Array(
			"TYPE" => "ERROR",
			"MESSAGE" => GetMessage("MOD_UNINST_ERR"),
			"DETAILS" => $ex->GetString(),
			"HTML" => true,
		));
	else
		echo CAdminMessage::ShowNote(GetMessage("MOD_UNINST_OK"));
?>
<form action="<?=$APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>" />
	<input type="submit" name="" value="<?=GetMessage("MOD_BACK")?>" />
</form>
<?
	return;
endif;
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>" />
	<input type="hidden" name="id" value="vote" />
	<input type="hidden" name="uninstall" value="Y" />
	<input type="hidden" name="step" value="2" />
	<?echo CAdminMessage::ShowMessage(GetMessage("MOD_UNINST_WARN"))?>
	<p><?=GetMessage("MOD_UNINST_SAVE")?></p>
	<p>
		<input type="checkbox" name="savedata" id="savedata" value="Y" checked="checked" />
		<label for="savedata"><?=GetMessage("MOD_UNINST_SAVE_TABLES")?></label>
	</p>
	<p>
		<input type="checkbox" name="save_templates" id="save_templates" value="Y" checked="checked" />
		<label for="save_templates"><?=GetMessage("MOD_UNINST_SAVE_EVENTS")?></label></p>
	<input type="submit" name="inst" value="<?=GetMessage("MOD_UNINST_DEL")?>" />
</form>