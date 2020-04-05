<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("SNBPA_PD_OWNER_ID") ?>:</span></td>
	<td width="60%">
		<?
		if ($user->isAdmin())
		{
			echo CBPDocument::ShowParameterField("user", 'owner_id', $arCurrentValues['owner_id'], Array('rows'=> 1));
		}
		else
		{
			echo $user->getFullName();
		}
		?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("SNBPA_PD_USERS_TO") ?>:</span></td>
	<td width="60%">
		<input type="text" name="users_to" id="id_users_to" value="<?= htmlspecialcharsbx($arCurrentValues["users_to"]) ?>" size="40">
		<input type="button" value="..." onclick="BPAShowSelector('id_users_to', 'user');">
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class=""><?= GetMessage("SNBPA_PD_POST_TITLE") ?>:</span></td>
	<td width="60%">
		<input type="text" name="post_title" id="id_post_title" value="<?= htmlspecialcharsbx($arCurrentValues["post_title"]) ?>" size="40">
		<input type="button" value="..." onclick="BPAShowSelector('id_post_title', 'string');">
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("SNBPA_PD_POST_MESSAGE") ?>:</span></td>
	<td width="60%">
		<textarea name="post_message" id="id_post_message" rows="4" cols="40"><?= htmlspecialcharsbx($arCurrentValues["post_message"]) ?></textarea>
		<input type="button" value="..." onclick="BPAShowSelector('id_post_message', 'string');">
	</td>
</tr>
<tr>
	<td align="right" width="40%" valign="top"><?= GetMessage("SNBPA_PD_POST_SITE") ?>:</td>
	<td width="60%">
		<select name="post_site">
			<option value="">(<?= GetMessage("SNBPA_PD_POST_SITE_OTHER") ?>)</option>
			<?
			$b = $o = "";
			$expression = CBPDocument::IsExpression($arCurrentValues["post_site"]) ? htmlspecialcharsbx($arCurrentValues["post_site"]) : '';
			$dbSites = CSite::GetList($b, $o, Array("ACTIVE" => "Y"));
			while ($site = $dbSites->GetNext())
			{
				?><option value="<?= $site["LID"] ?>"<?= ($site["LID"] == $arCurrentValues["post_site"]) ? " selected" : ""?>>[<?= $site["LID"] ?>] <?= $site["NAME"] ?></option><?
			}
			?>
		</select><br>
		<input type="text" name="post_site_x" id="id_post_site_x" size="30" value="<?= $expression ?>" />
		<input type="button" value="..." onclick="BPAShowSelector('id_post_site_x', 'string');">
	</td>
</tr>