<?
if(!IsModuleInstalled("currency"))
{
	echo CAdminMessage::ShowMessage(GetMessage("SALE_INSTALL_CURRENCY"));
	?>
	<form action="<?echo $APPLICATION->GetCurPage()?>">
	<p>
		<input type="hidden" name="lang" value="<?echo LANG?>">
		<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">	
	</p>
	<form>
	<?
}
else
{
	?>
	<form action="<?= $APPLICATION->GetCurPage()?>" name="sale_install">
	<?=bitrix_sessid_post()?>
		<input type="hidden" name="lang" value="<?= LANG ?>">
		<input type="hidden" name="id" value="sale">
		<input type="hidden" name="install" value="Y">
		<input type="hidden" name="step" value="2">

		<script language="JavaScript">
		<!--
		function ChangeInstallPublic(val)
		{
			document.sale_install.public_dir.disabled = !val;
			document.sale_install.public_rewrite.disabled = !val;
		}
		//-->
		</script>

		<table cellpadding="3" cellspacing="0" border="0" width="0%">
			<tr>
				<td><input type="checkbox" name="install_public" value="Y" id="install_public" OnClick="ChangeInstallPublic(this.checked)"></td>
				<td><p><label for="install_public"><?= GetMessage("SIM_COPY_PUBLIC_FILES") ?></label></p></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<table cellpadding="3" cellspacing="0" border="0" width="0%">
						<tr>
							<td><p><?= GetMessage("SIM_COPY_FOLDER") ?></p></td>
							<td><input type="input" name="public_dir" value="personal" size="40"></td>
						</tr>
						<tr>
							<td><p><label for="public_rewrite"><?= GetMessage("SALE_INSTALL_PUBLIC_REW") ?>:</label></p></td>
							<td><input type="checkbox" name="public_rewrite" value="Y" id="public_rewrite"></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>		

		<script language="JavaScript">
		<!--
		ChangeInstallPublic(false);
		//-->
		</script>
		<br>
		<input type="submit" name="inst" value="<?= GetMessage("MOD_INSTALL")?>">
	</form>
	<?
}
?>