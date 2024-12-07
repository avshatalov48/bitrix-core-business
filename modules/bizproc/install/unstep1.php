<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (isset($bizproc_installer_errors) && is_array($bizproc_installer_errors) && (count($bizproc_installer_errors) > 0))
{
	$errors = "";
	foreach ($bizproc_installer_errors as $e)
		$errors .= htmlspecialcharsbx($e)."<br>";
	CAdminMessage::ShowMessage(Array("TYPE"=>"ERROR", "MESSAGE" =>GetMessage("MOD_UNINST_ERR"), "DETAILS"=>$errors, "HTML"=>true));
	?>
	<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
	</form>
	<?
}
else
{
	?>
	<form action="<?echo $APPLICATION->GetCurPage()?>">
	<?=bitrix_sessid_post()?>
		<input type="hidden" name="lang" value="<?echo LANGUAGE_ID?>">
		<input type="hidden" name="id" value="bizproc">
		<input type="hidden" name="uninstall" value="Y">
		<input type="hidden" name="step" value="2">
		<?CAdminMessage::ShowMessage(GetMessage("MOD_UNINST_WARN"))?>
		<p><?echo GetMessage("MOD_UNINST_SAVE")?></p>
		<p><input type="checkbox" name="savedata" id="savedata" value="Y" checked><label for="savedata"><?echo GetMessage("MOD_UNINST_SAVE_TABLES")?></label></p>
		<input type="submit" name="inst" value="<?echo GetMessage("MOD_UNINST_DEL")?>">
	</form>
	<?
}
?>