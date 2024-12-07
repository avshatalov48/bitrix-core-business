<form action="<?echo $APPLICATION->GetCurPage()?>">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?echo LANG?>">
<input type="hidden" name="id" value="learning">
<input type="hidden" name="install" value="Y">
<input type="hidden" name="step" value="2">
<script>
<!--
function ChangeInstallPublic(val)
{
	document.getElementById("template_id").disabled = !val;
	for (i = 1;;i++)
	{
		if (document.getElementById("copy_"+i))
		{
			document.getElementById("copy_"+i).disabled = !val;
			document.getElementById("path_"+i).disabled = !val;
		}
		else
		{
			return;
		}
	}
}

function OnChangeSite()
{
	for (i = 1;;i++)
	{
		if (document.getElementById("copy_"+i))
		{
			if (document.getElementById("copy_"+i).checked)
			{
				document.getElementById("template_id").disabled = false;
				return;
			}
		}
		else
		{
			document.getElementById("template_id").disabled = true;
			return;
		}
	}
}
//-->
</script>
	<table cellpadding="3" cellspacing="0" border="0" width="0%">
		<tr>
			<td><input type="checkbox" name="install_public" value="Y" id="id_install_public" OnClick="ChangeInstallPublic(this.checked)"></td>
			<td><p><label for="id_install_public"><?=GetMessage("COPY_PUBLIC_FILES") ?></label></p></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<table border="0" cellspacing="1" cellpadding="1">
				<?
				$i = 0;
				$sites = CSite::GetList('', '', Array("ACTIVE"=>"Y"));
				while($site = $sites->Fetch()):$i++;?>
					<tr>
						<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
						<td><input type="checkbox" id="copy_<?=$i?>" name="copy_<?echo $site["LID"]?>" value="Y" checked onclick="OnChangeSite();"></td>
						<td><p><?echo htmlspecialcharsbx($site["NAME"])?>&nbsp;</p></td>
						<td><input type="text" id="path_<?=$i?>" name="path_<?echo $site["LID"]?>" value="<?echo $site["DIR"]?>learning/" size="30"></td>
					</tr>
				<?endwhile?>
					<tr>
						<td colspan="3" align="right"><p><?=GetMessage("LEARNING_INSTALL_TEMPLATE_NAME")?></p></td>
						<td colspan="2"><input type="text" name="template_id" id="template_id" value="learning" size="30"></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<script>
	<!--
	ChangeInstallPublic(false);
	//-->
	</script>

<br>
<input type="submit" name="inst" value="<?= GetMessage("MOD_INSTALL")?>">
</form>