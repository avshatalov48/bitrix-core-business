<form action="<?php echo $APPLICATION->GetCurPage()?>" name="form1">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?php echo LANG?>">
<input type="hidden" name="id" value="search">
<input type="hidden" name="install" value="Y">
<input type="hidden" name="step" value="2">
	<script>
	function ChangeInstallPublic(val)
	{
		document.form1.public_dir.disabled = !val;
		document.form1.public_rewrite.disabled = !val;
	}
	</script>

	<table cellpadding="3" cellspacing="0" border="0" width="0%">
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
						<td><input type="input" name="public_dir" value="search" size="40"></td>
					</tr>
					<tr>
						<td><p><label for="id_public_rewrite"><?= GetMessage('INSTALL_PUBLIC_REW') ?>:</label></p></td>
						<td><input type="checkbox" name="public_rewrite" value="Y" id="id_public_rewrite"></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<script>
	ChangeInstallPublic(false);
	</script>
	<br>
	<?php $connection = \Bitrix\Main\Application::getConnection(); ?>
	<?php if (CModule::IncludeModule('cluster') && $connection->getType() === 'mysql'):?>
	<p><?php echo GetMessage('SEARCH_INSTALL_DATABASE')?><select name="DATABASE">
		<option value=""><?php echo GetMessage('SEARCH_MAIN_DATABASE')?></option><?php
		$rsDBNodes = CClusterDBNode::GetListForModuleInstall();
		while ($arDBNode = $rsDBNodes->Fetch()):
		?><option value="<?php echo $arDBNode['ID']?>"><?php echo htmlspecialcharsbx($arDBNode['NAME'])?></option><?php
		endwhile;
		?></select></p>
	<br>
	<?php endif;?>
	<input type="submit" name="inst" value="<?= GetMessage('MOD_INSTALL')?>">
</form>
