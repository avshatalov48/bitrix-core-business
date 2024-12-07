<form action="<?= $APPLICATION->GetCurPage()?>" name="blog_install">
<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?= LANG ?>">
	<input type="hidden" name="id" value="blog">
	<input type="hidden" name="install" value="Y">
	<input type="hidden" name="step" value="2">

	<script>
	function ChangeInstallPublic(val, lan)
	{
		var name1 = 'is404_'+lan;
		var name2 = 'public_path_'+lan;
		var name3 = 'public_rewrite_'+lan;
		document.getElementById(name1).disabled = !val;
		document.getElementById(name2).disabled = !val;
		document.getElementById(name3).disabled = !val;
		
	}
	</script>
	<?
	$arSites= Array();
	$dbSites = CSite::GetList('', '', Array("ACTIVE" => "Y"));
	while ($site = $dbSites->GetNext())
	{ 
		$arSites[] = Array("SITE_ID" => $site["LID"], "NAME" => $site["NAME"], "DIR" => $site["DIR"]);
	}
	?>
	<table class="list-table">
	<tr class="head">
		<td> </td>
	<?foreach($arSites as $fSite):?>
			<td><?=$fSite["NAME"]?></td>
	<?endforeach;?>
	</tr>
	<tr>
		<td><?=GetMessage("BLI_COPY_PUBLIC_FILES") ?>:</td>
		<?foreach($arSites as $fSite):?>
			<td><input type="checkbox" name="install_public_<?=$fSite["SITE_ID"]?>" value="Y" onclick="ChangeInstallPublic(this.checked, '<?=CUtil::JSEscape($fSite["SITE_ID"])?>')"></td>
		<?endforeach;?>
	</tr>
	<tr>
		<td><?=GetMessage("BLI_INSTALL_404") ?>:</td>
		<?foreach($arSites as $fSite):?>
			<td><input type="checkbox" name="is404_<?=$fSite["SITE_ID"]?>" id="is404_<?=$fSite["SITE_ID"]?>" value="Y" checked></td>
		<?endforeach;?>
	</tr>
	<tr>
		<td><?=GetMessage("BLI_COPY_FOLDER") ?>:</td>
		<?foreach($arSites as $fSite):?>
			<td><input type="text" name="public_path_<?=$fSite["SITE_ID"]?>" id="public_path_<?=$fSite["SITE_ID"]?>" value="blog"></td>
		<?endforeach;?>
	</tr>			
	<tr>
		<td><?= GetMessage("BLOG_INSTALL_PUBLIC_REW") ?>:</td>
		<?foreach($arSites as $fSite):?>
			<td><input type="checkbox" name="public_rewrite_<?=$fSite["SITE_ID"]?>" id="public_rewrite_<?=$fSite["SITE_ID"]?>" value="Y"></td>
		<?endforeach;?>
	</tr>
	</table>
	<p>
		<input type="checkbox" name="install_templates" value="Y" id="install_templates" checked>&nbsp;<label for="install_templates"><?=GetMessage("BLI_INSTALL_EMAIL") ?></label><br />
		<input type="checkbox" name="install_smiles" value="Y" id="install_smiles" checked>&nbsp;<label for="install_smiles"><?=GetMessage("BLI_INSTALL_SMILES") ?></label>
	</p>

	<script>
	<?foreach($arSites as $fSite):?>
		ChangeInstallPublic(false, '<?=CUtil::JSEscape($fSite["SITE_ID"]);?>');
	<?endforeach;?>
	</script>
	<br>
	<input type="submit" name="inst" value="<?= GetMessage("MOD_INSTALL")?>">
</form>