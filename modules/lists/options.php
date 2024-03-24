<?php

/** @global CUser $USER */
/** @global CMain $APPLICATION */

if($USER->IsAdmin() && CModule::IncludeModule('iblock') && CModule::IncludeModule('lists')):

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

$aTabs = array(
	array(
		"DIV" => "permissions",
		"TAB" => GetMessage("LISTS_OPTIONS_TAB_PERMISSIONS"),
		"TITLE" => GetMessage("LISTS_OPTIONS_TAB_TITLE_PERMISSIONS"),
		"OPTIONS" => array(),
	),
);
if(IsModuleInstalled('socialnetwork'))
{
	$aTabs[] = array(
		"DIV" => "socnet",
		"TAB" => GetMessage("LISTS_OPTIONS_TAB_SOCNET"),
		"TITLE" => GetMessage("LISTS_OPTIONS_TAB_TITLE_SOCNET"),
		"OPTIONS" => array(),
	);
}
$aTabs[] = array(
	"DIV" => "livefeed",
	"TAB" => GetMessage("LISTS_OPTIONS_TAB_LIVE_FEED"),
	"TITLE" => GetMessage("LISTS_OPTIONS_TAB_TITLE_LIVE_FEED"),
	"OPTIONS" => array(),
);

$arGroups = array("REFERENCE"=>array(), "REFERENCE_ID"=>array());
$rsGroups = CGroup::GetDropDownList();
while($ar = $rsGroups->Fetch())
{
	$arGroups["REFERENCE"][] = $ar["REFERENCE"];
	$arGroups["REFERENCE_ID"][] = $ar["REFERENCE_ID"];
}

$arIBTypes = array("REFERENCE"=>array(), "REFERENCE_ID"=>array());
$rsIBTypes = CIBlockType::GetList();
while($arIBType = $rsIBTypes->GetNext())
{
	$arIBTypes["REFERENCE"][] = $arIBType["~ID"];
	$arIBTypes["REFERENCE_ID"][] = $arIBType["~ID"];
}

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$Update ??= '';
$Apply ??= '';
$RestoreDefaults ??= '';

if($_SERVER['REQUEST_METHOD'] === "POST" && $Update.$Apply.$RestoreDefaults <> '' && check_bitrix_sessid())
{
	if($RestoreDefaults <> '')
	{
		COption::RemoveOption("lists");
	}
	else
	{
		$arRights = array();
		if(
			isset($_POST["type_right"]) && is_array($_POST["type_right"])
			&& isset($_POST["group_right"]) && is_array($_POST["group_right"])
		)
		{
			$keys = array_keys($_POST["type_right"]);
			foreach($keys as $i)
			{
				if(
					array_key_exists($i, $_POST["type_right"])
					&& array_key_exists($i, $_POST["group_right"])
				)
				{
					$arRights[$_POST["type_right"][$i]][] = $_POST["group_right"][$i];
				}
			}
		}

		foreach($arRights as $type_id => $groups)
			CLists::SetPermission($type_id, $groups);

		if(IsModuleInstalled('socialnetwork'))
		{
			COption::SetOptionString("lists", "socnet_iblock_type_id", $_POST["socnet_iblock_type_id"]);
			CLists::EnableSocnet($_POST["socnet_enable"] === "Y");
		}

		if(isset($_POST["livefeed_iblock_type_id"]) && isset($_POST["livefeed_url"]))
		{
			COption::SetOptionString("lists", "livefeed_iblock_type_id", $_POST["livefeed_iblock_type_id"]);
			COption::SetOptionString("lists", "livefeed_url", $_POST["livefeed_url"]);
		}
	}

	if($Update <> '' && $_REQUEST["back_url_settings"] <> '')
	{
		LocalRedirect($_REQUEST["back_url_settings"]);
	}
	else
	{
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid ?? 'lists')."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
	}
}

$tabControl->Begin();
?>
<script>
function addNewTableRow(tableID, regexp, rindex)
{
	var tbl = document.getElementById(tableID);
	var cnt = tbl.rows.length-1;
	var oRow = tbl.insertRow(cnt);
	var col_count = tbl.rows[cnt-1].cells.length;

	for(var i=0;i<col_count;i++)
	{
		var oCell = oRow.insertCell(i);
		var html = tbl.rows[cnt-1].cells[i].innerHTML;
		oCell.innerHTML = html.replace(regexp,
			function(html)
			{
				return html.replace('[n'+arguments[rindex]+']', '[n'+(1+parseInt(arguments[rindex]))+']');
			}
		);
	}
}
</script>
<form method="post" action="<?= $APPLICATION->GetCurPage() ?>?mid=<?=urlencode($mid ?? 'lists')?>&amp;lang=<?=LANGUAGE_ID?>">
<?php

$tabControl->BeginNextTab();
	?>
	<tr>
		<td valign="top" colspan="2">
		<table border="0" cellspacing="0" cellpadding="0" class="internal" align="center" id="tblRIGHTS">
			<tr class="heading">
				<td><?echo GetMessage("LISTS_OPTIONS_USER_GROUPS")?></td>
				<td><?echo GetMessage("LISTS_OPTIONS_IBLOCK_TYPES")?></td>
			</tr>
	<?
	$i = 0;
	foreach(CLists::GetPermission() as $type_id => $groups):
		if (in_array($type_id, $arIBTypes["REFERENCE_ID"])):
			foreach($groups as $group):?>
				<tr>
					<td><?echo SelectBoxFromArray("group_right[n".$i."]", $arGroups, $group, GetMessage("LISTS_OPTIONS_CHOOSE_GROUP"))?></td>
					<td><?echo SelectBoxFromArray("type_right[n".$i."]", $arIBTypes, $type_id, GetMessage("LISTS_OPTIONS_CHOOSE_TYPE"))?></td>
				</tr>
			<? $i++; endforeach;
		endif;
	endforeach;
	if($i == 0)
	{
		?>
			<tr>
				<td><?echo SelectBoxFromArray("group_right[n".$i."]", $arGroups, $group, GetMessage("LISTS_OPTIONS_CHOOSE_GROUP"))?></td>
				<td><?echo SelectBoxFromArray("type_right[n".$i."]", $arIBTypes, $type_id, GetMessage("LISTS_OPTIONS_CHOOSE_TYPE"))?></td>
			</tr>
		<?
	}
	?>
		<tr>
			<td colspan="2" style="border:none">
			<input type="button" value="<?echo GetMessage("LISTS_OPTIONS_ADD_RIGHT")?>" onClick="addNewTableRow('tblRIGHTS', /right\[(n)([0-9]*)\]/g, 2)">
			</td>
		</tr>
		</table>
		</td>
	</tr>
	<?
if(IsModuleInstalled('socialnetwork'))
{
	$socnet_iblock_type_id = COption::GetOptionString("lists", "socnet_iblock_type_id");
	$socnet_enable = CLists::IsEnabledSocnet();
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td width="30%"><?echo GetMessage("LISTS_OPTIONS_SOCNET_ENABLE")?>:</td>
		<td width="70%"><input type="checkbox" name="socnet_enable" <?if($socnet_enable) echo "checked"?> value="Y"></td>
	</tr>
	<tr>
		<td width="30%"><?echo GetMessage("LISTS_OPTIONS_SOCNET_IBLOCK_TYPE")?>:</td>
		<td width="70%"><?echo SelectBoxFromArray("socnet_iblock_type_id", $arIBTypes, $socnet_iblock_type_id, GetMessage("MAIN_NO"))?></td>
	</tr>
	<?
}

	$livefeed_iblock_type_id = COption::GetOptionString("lists", "livefeed_iblock_type_id");
	$livefeed_url = COption::GetOptionString('lists', 'livefeed_url');
	$tabControl->BeginNextTab();
	?>
		<tr>
			<td width="30%"><?echo GetMessage("LISTS_OPTIONS_LIVE_FEED_IBLOCK_TYPE")?>:</td>
			<td width="70%"><?echo SelectBoxFromArray("livefeed_iblock_type_id", $arIBTypes, $livefeed_iblock_type_id, GetMessage("MAIN_NO"))?></td>
		</tr>
		<tr>
			<td width="30%"><?echo GetMessage("LISTS_OPTIONS_LIVE_FEED_SEF_FOLDER")?>:</td>
			<td width="70%">
				<input type="text" name="livefeed_url" id="livefeed_url" value="<?=htmlspecialcharsbx($livefeed_url); ?>">
			</td>
		</tr>
	<?

$tabControl->Buttons();?>
	<input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>" class="adm-btn-save">
	<input type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
	<?if($_REQUEST["back_url_settings"] <> ''):?>
		<input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
	<?endif?>
	<input type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="return confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
	<?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form>
<?endif;?>