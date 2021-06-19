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
		<?=CBPDocument::ShowParameterField("user", 'users_to', $arCurrentValues['users_to'], Array('rows'=> 2))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class=""><?= GetMessage("SNBPA_PD_POST_TITLE") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'post_title', $arCurrentValues['post_title'], Array('size'=> 50))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("SNBPA_PD_POST_MESSAGE") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("text", 'post_message', $arCurrentValues['post_message'], ['rows'=> 7, 'cols' => 40])?>
	</td>
</tr>
<tr>
	<td align="right" width="40%" valign="top"><?= GetMessage("SNBPA_PD_POST_SITE") ?>:</td>
	<td width="60%">
		<select name="post_site">
			<option value="">(<?= GetMessage("SNBPA_PD_POST_SITE_OTHER") ?>)</option>
			<?
			$expression = CBPDocument::IsExpression($arCurrentValues["post_site"]) ? htmlspecialcharsbx($arCurrentValues["post_site"]) : '';
			$dbSites = CSite::GetList('', '', Array("ACTIVE" => "Y"));
			while ($site = $dbSites->GetNext())
			{
				?><option value="<?= $site["LID"] ?>"<?= ($site["LID"] == $arCurrentValues["post_site"]) ? " selected" : ""?>>[<?= $site["LID"] ?>] <?= $site["NAME"] ?></option><?
			}
			?>
		</select><br>
		<?=CBPDocument::ShowParameterField("string", 'post_site_x', $expression, Array('size'=> 30))?>
	</td>
</tr>