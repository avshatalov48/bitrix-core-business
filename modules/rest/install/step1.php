<?
if($ex = $APPLICATION->GetException()):
	CAdminMessage::ShowMessage(Array(
		"TYPE" => "ERROR",
		"MESSAGE" => GetMessage("MOD_INST_ERR"),
		"DETAILS" => $ex->GetString(),
		"HTML" => true,
));
else:
	if(function_exists('apache_get_modules') && !in_array('mod_rewrite', apache_get_modules()))
	{
		showError(GetMessage('REST_MOD_REWRITE_ERROR'));
	}
?>
<form action="<?echo $APPLICATION->GetCurPage()?>" name="form1" method="post">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?echo LANG?>" />
	<input type="hidden" name="id" value="rest" />
	<input type="hidden" name="install" value="Y" />
	<input type="hidden" name="step" value="2" />
	<input type="submit" name="inst" value="<?echo GetMessage("MOD_INSTALL")?>" />
</form>
<?
endif;