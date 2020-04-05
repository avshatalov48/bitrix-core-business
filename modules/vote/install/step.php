<?
IncludeModuleLangFile(__FILE__);
if ($GLOBALS["install_step"] == 2):
	if(!check_bitrix_sessid()) 
		return;
	if($ex = $APPLICATION->GetException())
		echo CAdminMessage::ShowMessage(Array(
			"TYPE" => "ERROR",
			"MESSAGE" => GetMessage("MOD_INST_ERR"),
			"DETAILS" => $ex->GetString(),
			"HTML" => true,
		));
	else
		echo CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
	
	if (strlen($public_dir)>0) :
	?>
	<p><?=GetMessage("MOD_DEMO_DIR")?></p>
	<table border="0" cellspacing="0" cellpadding="3">
		<tr>
			<td align="center"><p><b><?=GetMessage("MOD_DEMO_SITE")?></b></p></td>
			<td align="center"><p><b><?=GetMessage("MOD_DEMO_LINK")?></b></p></td>
		</tr>
		<?
		$sites = CSite::GetList($by, $order, Array("ACTIVE"=>"Y"));
		while($site = $sites->GetNext())
		{
			?>
			<tr>
				<td width="0%"><p>[<?=$site["ID"]?>] <?=$site["NAME"]?></p></td>
				<td width="0%"><p><a href="<?if(strlen($site["SERVER_NAME"])>0) echo "http://".$site["SERVER_NAME"];?><?=$site["DIR"].$public_dir?>/vote_list.php"><?=$site["DIR"].$public_dir?>/vote_list.php</a></p></td>
			</tr>
			<?
		}
		?>
	</table>
	<?
	endif;
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>" />
	<input type="submit" name="" value="<?=GetMessage("MOD_BACK")?>" />
</form>
<?
	return;
endif;

?>
<form action="<?=$APPLICATION->GetCurPage()?>" name="form1">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>" />
<input type="hidden" name="id" value="vote" />
<input type="hidden" name="install" value="Y" />
<input type="hidden" name="step" value="2" />
<script language="JavaScript">
<!--
function ChangeInstallPublic(val)
{
	document.form1.public_dir.disabled = !val;
	document.form1.public_rewrite.disabled = !val;
}
//-->
</script>

<p class="vote-install-fields">
	<table cellpadding="3" cellspacing="0" border="0" width="0%">
		<tr>
			<td colspan="2">
				<input type="checkbox" name="install_public" value="Y" id="id_install_public" onclick="ChangeInstallPublic(this.checked)">
				<label for="id_install_public"><?= GetMessage("COPY_PUBLIC_FILES") ?></label></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<div class="vote-install-field vote-install-field-copy">
					<label for="public_dir"><?=GetMessage("COPY_FOLDER")?></label>
					<input type="input" name="public_dir" id="public_dir" value="vote" size="40" />
				</div>
				<div class="vote-install-field vote-install-field-rewrite">
					<input type="checkbox" name="public_rewrite" id="public_rewrite" value="Y" />
					<label for="public_rewrite"><?=GetMessage("INSTALL_PUBLIC_REW")?></label>
				</div>
			</td>
		</tr>
	</table>
</p>
<style>
	p.vote-install-fields table, p.vote-install-fields td,  p.vote-install-fields label{font-size:100%;}
</style>
<script language="JavaScript">
<!--
ChangeInstallPublic(false);
//-->
</script>
<br />
<input type="submit" name="inst" value="<?= GetMessage("MOD_INSTALL")?>" />
</form>