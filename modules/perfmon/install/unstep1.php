<?php
/* @var CMain APPLICATION */
?>
<form action="<?php echo $APPLICATION->GetCurPage()?>">
<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
	<input type="hidden" name="id" value="perfmon">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="step" value="2">
	<?php
	$message  = new CAdminMessage(GetMessage('MOD_UNINST_WARN'));
	echo $message->Show();
	?>
	<p><?php echo GetMessage('MOD_UNINST_SAVE')?></p>
	<p><input type="checkbox" name="savedata" id="savedata" value="Y" checked><label for="savedata"><?php echo GetMessage('MOD_UNINST_SAVE_TABLES')?></label></p>
	<input type="submit" name="inst" value="<?php echo GetMessage('MOD_UNINST_DEL')?>">
</form>
