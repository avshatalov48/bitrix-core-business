<?php
/* @var CMain APPLICATION */
IncludeModuleLangFile(__FILE__);
?>
<form action="<?php echo $APPLICATION->GetCurPage()?>" name="form1">
<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?php echo LANGUAGE_ID?>">
	<input type="hidden" name="id" value="subscribe">
	<input type="hidden" name="install" value="Y">
	<input type="hidden" name="step" value="2">
<script language="JavaScript">
<!--
function ChangeInstallPublic(val)
{
	document.form1.public_dir.disabled = !val;
	document.form1.public_rewrite.disabled = !val;
}
//-->
</script>
<table cellpadding="3" cellspacing="0" border="0" width="0%">
		<tr>
			<td><input type="checkbox" name="install_auto_templates" id="install_auto_templates" value="Y" checked></td>
			<td><p><label for="install_auto_templates"><?php echo GetMessage('inst_templates')?></label></p></td>
		</tr>
		<tr>
			<td><input type="checkbox" name="install_public" value="Y" id="id_install_public" OnClick="ChangeInstallPublic(this.checked)"></td>
			<td><p><label for="id_install_public"><?= GetMessage('COPY_PUBLIC_FILES') ?></label></p></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<table cellpadding="3" cellspacing="0" border="0" width="0%">
					<tr>
						<td><p><?= GetMessage('COPY_FOLDER') ?></p></td>
						<td><input type="input" name="public_dir" id="public_dir" value="personal" size="40"></td>
					</tr>
					<tr>
						<td><p><label for="public_rewrite"><?= GetMessage('INSTALL_PUBLIC_REW') ?>:</label></p></td>
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
	<input type="submit" name="inst" value="<?php echo GetMessage('MOD_INSTALL')?>">
</form>
