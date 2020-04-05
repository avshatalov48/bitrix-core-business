<?IncludeModuleLangFile(__FILE__);?>

<form action="<?echo $APPLICATION->GetCurPage()?>" name="form1">
<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="hidden" name="id" value="socialservices">
	<input type="hidden" name="install" value="Y">
	<input type="hidden" name="step" value="2">
	<input type="submit" name="inst" value="<?echo GetMessage("MOD_INSTALL")?>" class="adm-btn-save">
</form>
