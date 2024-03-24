<?php
/** @var CMain $APPLICATION */
?>
<form action="<?php echo $APPLICATION->GetCurPage();?>">
	<?php echo bitrix_sessid_post(); ?>
	<input type="hidden" name="lang" value="<?php echo LANGUAGE_ID ?>">
	<input type="hidden" name="id" value="bitrixcloud">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="step" value="2">
	<?php echo CAdminMessage::ShowMessage(GetMessage('MOD_UNINST_WARN')); ?>
	<p><?php echo GetMessage('MOD_UNINST_SAVE'); ?></p>
	<p><input type="checkbox" name="save_tables" id="save_tables" value="Y" checked><label for="save_tables"><?php echo GetMessage('MOD_UNINST_SAVE_TABLES'); ?></label></p>
	<input type="submit" name="inst" value="<?php echo GetMessage('MOD_UNINST_DEL'); ?>">
</form>
