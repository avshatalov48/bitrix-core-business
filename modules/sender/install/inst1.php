<?
IncludeModuleLangFile(__FILE__);
?>
<form action="<?echo $APPLICATION->GetCurPage()?>" name="form1">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?echo LANGUAGE_ID?>">
	<input type="hidden" name="id" value="sender">
	<input type="hidden" name="install" value="Y">
	<input type="hidden" name="step" value="2">
<table cellpadding="3" cellspacing="0" border="0" width="0%">
		<tr>
			<td></td>
			<td></td>
		</tr>
	</table>
<br>
	<input type="submit" name="inst" value="<?echo GetMessage("MOD_INSTALL")?>">
</form>