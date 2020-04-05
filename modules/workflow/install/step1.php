<?if(!check_bitrix_sessid()) return;?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?echo LANGUAGE_ID?>">
	<input type="hidden" name="id" value="workflow">
	<input type="hidden" name="install" value="Y">
	<input type="hidden" name="step" value="2">
	<p><input type="submit" name="inst" value="<?echo GetMessage("MOD_INSTALL")?>"></p>
<form>
