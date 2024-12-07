<?php
IncludeModuleLangFile(__FILE__);

/**
 * @global CMain $APPLICATION
 */
?>
<form action="<?= $APPLICATION->GetCurPage() ?>" name="form1">
	<script>
	function ChangeInstallPublic(val, pr)
	{
		const pd = 'public_dir_'+pr;
		const pdr = 'public_rewrite_'+pr;
		document.getElementById(pd).disabled = !val;
		document.getElementById(pdr).disabled = !val;
	}
	</script>
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
	<input type="hidden" name="id" value="iblock">
	<input type="hidden" name="install" value="Y">
	<input type="hidden" name="step" value="2">
	<table cellpadding="3" cellspacing="0" border="0" width="0%">
		<tr>
			<td>&nbsp;</td>
			<td>
				<table cellpadding="3" cellspacing="0" border="0">
				<tr>
					<td><input type="checkbox" name="news" id="news" value="Y" OnClick="ChangeInstallPublic(this.checked, 'n')"></td>
					<td><p><label for="news"><?= GetMessage("IBLOCK_INSTALL_NEWS") ?></label></p></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><p><?= GetMessage("IBLOCK_INSTALL_PUBLIC_DIR") ?>:<input type="text" name="news_dir" value="news" size="20" id="public_dir_n"></p></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><p><label for="public_rewrite_n"><?= GetMessage("INSTALL_PUBLIC_REW") ?>:</label>&nbsp;<input type="checkbox" name="public_rewrite_n" value="Y" id="public_rewrite_n"></p></td>
				</tr>
				<tr>
					<td><input type="checkbox" name="catalog" value="Y" OnClick="ChangeInstallPublic(this.checked, 'c')" id="catalog"></td>
					<td><p><label for="catalog"><?= GetMessage("IBLOCK_INSTALL_CATALOG") ?></label></p></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><p><?= GetMessage("IBLOCK_INSTALL_PUBLIC_DIR") ?>:<input type="text"  name="catalog_dir" value="catalog" size="20" id="public_dir_c"></p></td></tr>
				<tr>
					<td>&nbsp;</td>
					<td><p><label for="public_rewrite_c"><?= GetMessage("INSTALL_PUBLIC_REW") ?>:</label>&nbsp;<input type="checkbox" name="public_rewrite_c" value="Y" id="public_rewrite_c"></p></td>
				</tr>
				</table>
			</td>
		</tr>
	</table>
	<br>
	<input type="submit" name="inst" value="<?= GetMessage("MOD_INSTALL") ?>">
	<script>
	ChangeInstallPublic(false, 'c');
	ChangeInstallPublic(false, 'n');
	</script>
</form>
