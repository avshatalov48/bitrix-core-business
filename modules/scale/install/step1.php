<?
/** @global CMain $APPLICATION */
IncludeModuleLangFile(__FILE__);
?>
<p><?echo GetMessage("SCALE_INSTALL"); ?></p>
<form action="<?echo $APPLICATION->GetCurPage(); ?>" name="form1">
<?echo bitrix_sessid_post(); ?>
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="id" value="scale">
<input type="hidden" name="install" value="Y">
<input type="hidden" name="step" value="2">
<input type="submit" name="inst" value="<?echo GetMessage("MOD_INSTALL"); ?>">
</form>