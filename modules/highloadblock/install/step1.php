<?
IncludeModuleLangFile(__FILE__); ?>
<p><?echo GetMessage("HLBLOCK_INSTALL"); ?></p>
<form action="<?echo $APPLICATION->GetCurPage(); ?>" name="form1">
<?echo bitrix_sessid_post(); ?>
<input type="hidden" name="lang" value="<?echo LANGUAGE_ID; ?>">
<input type="hidden" name="id" value="highloadblock">
<input type="hidden" name="install" value="Y">
<input type="hidden" name="step" value="2">
<input type="submit" name="inst" value="<?echo GetMessage("MOD_INSTALL"); ?>">
</form>