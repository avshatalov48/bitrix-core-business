<?
/** @global CMain $APPLICATION */
?>
<form action="<?echo $APPLICATION->GetCurPage();?>">
	<?echo bitrix_sessid_post(); ?>
	<input type="hidden" name="lang" value="<?echo LANGUAGE_ID ?>">
	<input type="hidden" name="id" value="b24connector">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="step" value="2">
	<?echo CAdminMessage::ShowMessage(GetMessage("MOD_UNINST_WARN")); ?>
	<input type="submit" name="inst" value="<?echo GetMessage("MOD_UNINST_DEL"); ?>">
</form>