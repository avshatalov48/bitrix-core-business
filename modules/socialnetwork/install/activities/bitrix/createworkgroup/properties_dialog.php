<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var array $arCurrentValues */
/** @var array $arDocumentFields */
/** @var string $formName */
/** @var string $currentSiteId */
global $DB, $USER, $APPLICATION;
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
?>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPCWG_GROUP_NAME") ?>:</span></td>
	<td width="60%">
		<input type="text" name="group_name" id="id_group_name" value="<?= htmlspecialcharsbx($arCurrentValues["group_name"]) ?>" size="50">
		<input type="button" value="..." onclick="BPAShowSelector('id_group_name', 'string');">
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPCWG_OWNER") ?>:</span></td>
	<td width="60%">
		<input type="text" name="owner_id" id="id_owner_id" value="<?= htmlspecialcharsbx($arCurrentValues["owner_id"]) ?>" size="50">
		<input type="button" value="..." onclick="BPAShowSelector('id_owner_id', 'user');">
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPCWG_USERS") ?>:</span></td>
	<td width="60%">
		<input type="text" name="users" id="id_users" value="<?= htmlspecialcharsbx($arCurrentValues["users"]) ?>" size="50">
		<input type="button" value="..." onclick="BPAShowSelector('id_users', 'user');">
	</td>
</tr>
<?
foreach ($arDocumentFields as $fieldKey => $fieldValue)
{
	?>
	<tr>
		<td align="right" width="40%" valign="top"><?= ($fieldValue["Required"]) ? "<span class=\"adm-required-field\">".htmlspecialcharsbx($fieldValue["Name"])."</span>:" : htmlspecialcharsbx($fieldValue["Name"]) .":" ?></td>
		<td width="60%" id="td_<?= htmlspecialcharsbx($fieldKey) ?>" valign="top">
			<?
			if ($fieldValue["UserField"])
			{
				if ($arCurrentValues[$fieldKey])
				{
					if ($fieldValue["UserField"]["USER_TYPE_ID"] == "boolean")
					{
						$fieldValue["UserField"]["VALUE"] = ($arCurrentValues[$fieldKey] == "Y" ? 1 : 0);
					}
					else
					{
						$fieldValue["UserField"]["VALUE"] = $arCurrentValues[$fieldKey];
					}
					$fieldValue["UserField"]["ENTITY_VALUE_ID"] = 1; //hack to not empty value
				}
				$APPLICATION->IncludeComponent(
					"bitrix:system.field.edit",
					$fieldValue["UserField"]["USER_TYPE"]["USER_TYPE_ID"],
					array(
						"bVarsFromForm" => false,
						"arUserField" => $fieldValue["UserField"],
						"form_name" => $formName,
						'SITE_ID' => $currentSiteId,
					), null, array("HIDE_ICONS" => "Y")
				);
			}
			?>
		</td>
	</tr>
	<?
}
?>
<tr>
	<td align="right" width="40%" valign="top"><?= GetMessage("BPCWG_SITE") ?>:</td>
	<td width="60%">
		<select name="group_site">
			<option value="">(<?= GetMessage("BPCWG_SITE_OTHER") ?>)</option>
			<?
			$b = $o = "";
			$expression = CBPDocument::IsExpression($arCurrentValues["group_site"]) ? htmlspecialcharsbx($arCurrentValues["group_site"]) : '';
			$dbSites = CSite::GetList($b, $o, Array("ACTIVE" => "Y"));
			while ($site = $dbSites->GetNext())
			{
				?><option value="<?= $site["LID"] ?>"<?= ($site["LID"] == $arCurrentValues["group_site"]) ? " selected" : ""?>>[<?= $site["LID"] ?>] <?= $site["NAME"] ?></option><?
			}
			?>
		</select><br>
		<input type="text" name="group_site_x" id="id_group_site_x" size="30" value="<?= $expression ?>" />
		<input type="button" value="..." onclick="BPAShowSelector('id_group_site_x', 'string');">
	</td>
</tr>
<? echo $APPLICATION->GetCSS();?>
