<form action="<?= $APPLICATION->GetCurPage()?>" name="sonet_install">
<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?= LANG ?>">
	<input type="hidden" name="id" value="socialnetwork">
	<input type="hidden" name="install" value="Y">
	<input type="hidden" name="step" value="2">

	<script language="JavaScript">
	<!--
	function ChangeInstallPublic(val, lan)
	{
		var name1 = 'is404_' + lan;
		var name2 = 'public_path_' + lan;
		var name3 = 'public_rewrite_' + lan;
		document.getElementById(name1).disabled = !val;
		document.getElementById(name2).disabled = !val;
		document.getElementById(name3).disabled = !val;
	}
	//-->
	</script>
	<?
	$arSites= Array();
	$arSiteTabs = array();

	$dbSites = CSite::GetList(($b = ""), ($o = ""), Array("ACTIVE" => "Y"));
	while ($site = $dbSites->Fetch())
	{
		$arSites[] = Array("SITE_ID" => $site["LID"], "NAME" => $site["NAME"], "DIR" => $site["DIR"]);
		$arSiteTabs[] = array("DIV" => "opt_site_".$site["ID"], "TAB" => '['.$site["ID"].'] '.htmlspecialcharsbx($site["NAME"]), 'TITLE' => '');
	}

	$arSiteTabControl = new CAdminViewTabControl("siteTabControl", $arSiteTabs);

	?>
	<table class="list-table">
		<tr class="head">
			<td colspan="2"><?= GetMessage("SONET_INSTALL_TITLE") ?></td>
		</tr>
		<!--tr>
			<td width="50%" align="right"><label for="id_install_templates"><?=GetMessage("SONETP_INSTALL_EMAIL") ?>:</label></td>
			<td width="50%"><input type="checkbox" name="install_templates" value="Y" id="id_install_templates" checked></td>
		</tr-->
		<tr>
			<td width="50%" align="right"><label for="id_install_smiles"><?=GetMessage("SONETP_INSTALL_SMILES") ?>:</label></td>
			<td width="50%"><input type="checkbox" name="install_smiles" value="Y" id="id_install_smiles" checked></td>
		</tr>
	</table>
	<br/>
	<?
	$arSiteTabControl->Begin();

	foreach ($arSites as $arSite)
	{
		$siteIdValue = htmlspecialcharsbx($arSite["SITE_ID"]);

		$arSiteTabControl->BeginNextTab();
		?>
		<table class="list-table">
			<tr>
				<td width="50%" align="right"><?=GetMessage("SONETP_COPY_PUBLIC_FILES") ?>:</td>
				<td><input type="checkbox" name="install_site_id_<?=$siteIdValue?>" id="install_site_id_<?=$siteIdValue?>" value="<?=$siteIdValue?>" onclick="ChangeInstallPublic(this.checked, '<?=CUtil::JSEscape($arSite["SITE_ID"])?>')"></td>
			</tr>
			<tr>
				<td width="50%" align="right"><?=GetMessage("SONETP_INSTALL_404") ?>:</td>
				<td><input type="checkbox" name="is404_<?=$siteIdValue?>" id="is404_<?=$siteIdValue?>" value="Y" checked></td>
			</tr>
			<tr>
				<td width="50%" align="right"><?=GetMessage("SONETP_COPY_FOLDER") ?>:</td>
				<td><input type="text" name="public_path_<?=$siteIdValue?>" id="public_path_<?=$siteIdValue?>" value="club"></td>
			</tr>
			<tr>
				<td width="50%" align="right"><?= GetMessage("SONET_INSTALL_PUBLIC_REW") ?>:</td>
				<td><input type="checkbox" name="public_rewrite_<?=$siteIdValue?>" id="public_rewrite_<?=$siteIdValue?>" value="Y"></td>
			</tr>
		</table>
		<?
	}
	$arSiteTabControl->End();
	?>

	<script language="JavaScript">
	<!--
	<?foreach($arSites as $arSite):?>
		ChangeInstallPublic(false, '<?=CUtil::JSEscape($arSite["SITE_ID"]);?>');
	<?endforeach;?>
	//-->
	</script>
	<br>
	<input type="submit" name="inst" value="<?= GetMessage("MOD_INSTALL")?>">
</form>