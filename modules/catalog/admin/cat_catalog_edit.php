<?php

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Iblock\PropertyTable;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @global CMain $APPLICATION */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");
IncludeModuleLangFile(__FILE__);

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

if(!CModule::IncludeModule("catalog"))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage("CAT_CEDIT_CATALOG_MODULE_IS_MISSING"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}

$selfFolderUrl = $adminPage->getSelfFolderUrl();

$strWarning = "";
$bVarsFromForm = false;
$IBLOCK_ID = intval($_REQUEST["IBLOCK_ID"]);

$arIBlock = CIBlock::GetArrayByID($IBLOCK_ID);
if($arIBlock)
{
	$bBadBlock = !(
		CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_edit")
		|| AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_SETTINGS_ACCESS)
	);
}
else
{
	$bBadBlock = true;
}

if($bBadBlock)
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage("CAT_CEDIT_BAD_IBLOCK"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("CAT_CEDIT_MAIN_TAB"),
		"ICON" => "iblock_section",
		"TITLE" => GetMessage("CAT_CEDIT_MAIN_TAB_TITLE"),
	),
	array(
		"DIV" => "edit3",
		"TAB" => GetMessage("CAT_CEDIT_PROPERTY_TAB"),
		"ICON" => "iblock_section",
		"TITLE" => GetMessage("CAT_CEDIT_PROPERTY_TAB_TITLE"),
	),
);

$tabControl = new CAdminForm("form_catalog_edit_".$IBLOCK_ID, $aTabs);

if(
	$_SERVER["REQUEST_METHOD"] == "POST"
	&& (
		(isset($_REQUEST["save"]) && $_REQUEST["save"] <> '')
		|| (isset($_REQUEST["apply"]) && $_REQUEST["apply"] <> '')
	)
	&& check_bitrix_sessid()
)
{
	if($_POST["NAME"] != $arIBlock["NAME"])
	{
		$ib = new CIBlock;
		$res = $ib->Update($IBLOCK_ID, array(
			"NAME" => $_POST["NAME"],
		));
		if(!$res)
		{
			$strWarning .= $ib->LAST_ERROR."<br>";
			$bVarsFromForm = true;
		}
	}

	if($arIBlock["SECTION_PROPERTY"] != "Y")
	{
		$ib = new CIBlock;
		$res = $ib->Update($IBLOCK_ID, array(
			"SECTION_PROPERTY" => "Y",
		));
		if(!$res)
		{
			$strWarning .= $ib->LAST_ERROR."<br>";
			$bVarsFromForm = true;
		}
	}

	$arCatalog = CCatalogSku::GetInfoByProductIBlock($IBLOCK_ID);

	if(is_array($arCatalog) && CIBlock::GetArrayByID($arCatalog["IBLOCK_ID"], "SECTION_PROPERTY") != "Y")
	{
		$ib = new CIBlock;
		$res = $ib->Update($arCatalog["IBLOCK_ID"], array(
			"SECTION_PROPERTY" => "Y",
		));
		if(!$res)
		{
			$strWarning .= $ib->LAST_ERROR."<br>";
			$bVarsFromForm = true;
		}
	}

	if($strWarning === "")
	{
		$TextParser = new CBXSanitizer();
		$TextParser->SetLevel(CBXSanitizer::SECURE_LEVEL_LOW);
		$TextParser->ApplyDoubleEncode(false);
		$props = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $IBLOCK_ID, "CHECK_PERMISSIONS" => "N"));
		while($p = $props->Fetch())
		{
			if(
				isset($_POST["SECTION_PROPERTY"])
				&& is_array($_POST["SECTION_PROPERTY"])
				&& array_key_exists($p["ID"], $_POST["SECTION_PROPERTY"])
				&& $_POST["SECTION_PROPERTY"][$p["ID"]]["SHOW"] === "Y"
			)
			{
				$filterHint = trim((string)($_POST['SECTION_PROPERTY'][$p['ID']]['FILTER_HINT'] ?? null));
				if ($filterHint)
				{
					$filterHint = $TextParser->SanitizeHtml($filterHint);
				}

				CIBlockSectionPropertyLink::Set(
					0,
					$p['ID'],
					[
						'SMART_FILTER' => $_POST['SECTION_PROPERTY'][$p['ID']]['SMART_FILTER'] ?? null,
						'DISPLAY_TYPE' => $_POST['SECTION_PROPERTY'][$p['ID']]['DISPLAY_TYPE'] ?? null,
						'DISPLAY_EXPANDED' => $_POST['SECTION_PROPERTY'][$p['ID']]['DISPLAY_EXPANDED'] ?? null,
						'FILTER_HINT' => $filterHint,
					]
				);
			}
			else
			{
				CIBlockSectionPropertyLink::Delete(0, $p["ID"]);
			}
		}

		if (is_array($arCatalog))
		{
			$props = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $arCatalog["IBLOCK_ID"], "CHECK_PERMISSIONS" => "N"));
			while($p = $props->Fetch())
			{
				if(
					isset($_POST["SECTION_PROPERTY"])
					&& is_array($_POST["SECTION_PROPERTY"])
					&& array_key_exists($p["ID"], $_POST["SECTION_PROPERTY"])
					&& $_POST["SECTION_PROPERTY"][$p["ID"]]["SHOW"] === "Y"
				)
				{
					$filterHint = trim((string)($_POST['SECTION_PROPERTY'][$p['ID']]['FILTER_HINT'] ?? null));
					if ($filterHint)
					{
						$filterHint = $TextParser->SanitizeHtml($filterHint);
					}

					CIBlockSectionPropertyLink::Set(
						0,
						$p['ID'],
						[
							'SMART_FILTER' => $_POST['SECTION_PROPERTY'][$p['ID']]['SMART_FILTER'] ?? null,
							'DISPLAY_TYPE' => $_POST['SECTION_PROPERTY'][$p['ID']]['DISPLAY_TYPE'] ?? null,
							'DISPLAY_EXPANDED' => $_POST['SECTION_PROPERTY'][$p['ID']]['DISPLAY_EXPANDED'] ?? null,
							'IBLOCK_ID' => $IBLOCK_ID,
							'FILTER_HINT' => $filterHint,
						]
					);
				}
				else
				{
					CIBlockSectionPropertyLink::Delete(0, $p["ID"]);
				}
			}
		}

		$redirectUrl = $selfFolderUrl."cat_catalog_edit.php?lang=".LANGUAGE_ID."&IBLOCK_ID=".$IBLOCK_ID."&".$tabControl->ActiveTabParam();
		$adminSidePanelHelper->reloadPage($redirectUrl, (!empty($_REQUEST['apply']) ? 'apply' : 'save'));
		$redirectUrl = $adminSidePanelHelper->setDefaultQueryParams($redirectUrl);
		LocalRedirect($redirectUrl);
	}
}

ClearVars("str_");
if($bVarsFromForm)
	$str_NAME = $_POST["NAME"];
else
	$str_NAME = $arIBlock["NAME"];

$APPLICATION->SetTitle(GetMessage("CAT_CEDIT_EDIT_TITLE", array("#IBLOCK_NAME#"=>$arIBlock["NAME"])));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if($strWarning)
	CAdminMessage::ShowOldStyleError($strWarning."<br>");

$tabControl->BeginEpilogContent();
echo bitrix_sessid_post();
$tabControl->EndEpilogContent();

$actionUrl = $selfFolderUrl."cat_catalog_edit.php?lang=".LANGUAGE_ID."&IBLOCK_ID=".$IBLOCK_ID;
$actionUrl = $adminSidePanelHelper->setDefaultQueryParams($actionUrl);
$tabControl->Begin(array("FORM_ACTION" => $actionUrl));

$tabControl->BeginNextFormTab();
$tabControl->AddEditField("NAME", GetMessage("IBLOCK_FIELD_NAME").":", true, array("size" => 50, "maxlength" => 255), $str_NAME);


$tabControl->BeginNextFormTab();
$tabControl->BeginCustomField("SECTION_PROPERTY", GetMessage("CAT_CEDIT_SECTION_PROPERTY_FIELD"));
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/admin_tools.php");
	$editor = new CEditorPopupControl();
	?>
		<tr colspan="2"><td style="text-align: center;">
			<?= $editor->getEditorHtml(); ?>
			<table class="internal" id="table_SECTION_PROPERTY">
			<tr class="heading">
				<td><?= GetMessage("CAT_CEDIT_PROP_TABLE_NAME"); ?></td>
				<td><?= GetMessage("CAT_CEDIT_PROP_TABLE_TYPE"); ?></td>
				<td><?= GetMessage("CAT_CEDIT_PROP_TABLE_SMART_FILTER"); ?></td>
				<td><?= GetMessage("CAT_CEDIT_PROP_TABLE_DISPLAY_TYPE"); ?></td>
				<td><?= GetMessage("CAT_CEDIT_PROP_TABLE_DISPLAY_EXPANDED"); ?></td>
				<td><?= GetMessage("CAT_CEDIT_PROP_TABLE_FILTER_HINT"); ?></td>
				<td><?= GetMessage("CAT_CEDIT_PROP_TABLE_ACTION"); ?></td></tr>
			<?php
			if(CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_edit"))
				$arShadow = $arHidden = array(
					-1 => GetMessage("CAT_CEDIT_PROP_SELECT_CHOOSE"),
					0 => GetMessage("CAT_CEDIT_PROP_SELECT_CREATE"),
				);
			else
				$arShadow = $arHidden = array(
					-1 => GetMessage("CAT_CEDIT_PROP_SELECT_CHOOSE"),
				);

			$arPropLinks = CIBlockSectionPropertyLink::GetArray($arIBlock["ID"], 0);
			$rsProps =  CIBlockProperty::GetList(array("SORT"=>"ASC",'ID' => 'ASC'), array("IBLOCK_ID" => $arIBlock["ID"], "CHECK_PERMISSIONS" => "N", "ACTIVE"=>"Y"));
			$rows = 0;
			while ($arProp = $rsProps->Fetch()):
				if(isset($arPropLinks[$arProp["ID"]]))
				{
					$rows++;
					$arLink = $arPropLinks[$arProp["ID"]];
					if($arLink["INHERITED"] == "N")
						$arShadow[$arProp["ID"]] = $arProp["NAME"];
				}
				else
				{
					$arLink = false;
					$arHidden[$arProp["ID"]] = $arProp["NAME"];
					$arShadow[$arProp["ID"]] = $arProp["NAME"];
				}
				$linkExists = is_array($arLink);
				$fileProperty = $arProp['PROPERTY_TYPE'] == PropertyTable::TYPE_FILE;
			?>
			<tr id="tr_SECTION_PROPERTY_<?= $arProp['ID']?>"<?= (!$linkExists ? ' style="display:none;"' : ''); ?>>
				<td style="text-align: left;">
					<?php
					if(!$linkExists || $arLink["INHERITED"] == "N"):
					?>
					<input type="hidden" name="SECTION_PROPERTY[<?= $arProp['ID']; ?>][SHOW]" id="hidden_SECTION_PROPERTY_<?= $arProp['ID']; ?>" value="<?= ($linkExists ? 'Y': 'N'); ?>">
					<?php
					endif;
					echo htmlspecialcharsbx($arProp["NAME"]);
					?>
				</td>
				<td style="text-align: left;"><?php
					if($arProp['PROPERTY_TYPE'] == "S" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_S");
					elseif($arProp['PROPERTY_TYPE'] == "N" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_N");
					elseif($arProp['PROPERTY_TYPE'] == "L" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_L");
					elseif($arProp['PROPERTY_TYPE'] == "F" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_F");
					elseif($arProp['PROPERTY_TYPE'] == "G" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_G");
					elseif($arProp['PROPERTY_TYPE'] == "E" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_E");
					elseif($arProp['USER_TYPE'] && is_array($ar = CIBlockProperty::GetUserType($arProp['USER_TYPE'])))
						echo htmlspecialcharsbx($ar["DESCRIPTION"]);
					else
						echo GetMessage("IBLOCK_PROP_S");
				?></td>
				<td style="text-align:center"><?php
					echo '<input type="checkbox" value="Y" '.(($linkExists && $arLink["INHERITED"] == "Y") || $fileProperty ? 'disabled="disabled"': '').' name="SECTION_PROPERTY['.$arProp['ID'].'][SMART_FILTER]" '.($linkExists && $arLink["SMART_FILTER"] == "Y"? 'checked="checked"': '').'>';
				?></td>
				<td style="text-align: left;">
					<?php
					$displayTypes = CIBlockSectionPropertyLink::getDisplayTypes($arProp["PROPERTY_TYPE"], $arProp["USER_TYPE"]);
					if ($displayTypes)
					{
						echo SelectBoxFromArray('SECTION_PROPERTY['.$arProp['ID'].'][DISPLAY_TYPE]', array(
							"REFERENCE_ID" => array_keys($displayTypes),
							"REFERENCE" => array_values($displayTypes),
						), $linkExists ? $arLink["DISPLAY_TYPE"] : '', '', '');
					}
					else
					{
						echo '&nbsp;';
					}
					?>
				</td>
				<td style="text-align:center"><?php
					echo '<input type="checkbox" value="Y" '.(($linkExists && $arLink["INHERITED"] == "Y") || $fileProperty ? 'disabled="disabled"': '').' name="SECTION_PROPERTY['.$arProp['ID'].'][DISPLAY_EXPANDED]" '.($linkExists && $arLink["DISPLAY_EXPANDED"] == "Y"? 'checked="checked"': '').'>';
				?></td>
				<td>
				<?php
					if ($fileProperty)
					{
						echo '&nbsp;';
					}
					elseif (!$linkExists || $arLink["INHERITED"] == "N")
					{
						$filterHint = ($linkExists ? (string)$arLink['FILTER_HINT'] : '');
						echo $editor->getControlHtml('SECTION_PROPERTY['.$arProp['ID'].'][FILTER_HINT]', $filterHint, 255);
					}
					elseif ($linkExists && $arLink['FILTER_HINT'] <> '')
					{
						echo CTextParser::closeTags($arLink['FILTER_HINT']);
					}
					else
					{
						echo '&nbsp;';
					}
				?></td>
				<td style="text-align: left;"><?php
					if(!$linkExists || $arLink["INHERITED"] == "N")
						echo '<a class="bx-action-href" href="javascript:deleteSectionProperty('.$arProp['ID'].', \'select_SECTION_PROPERTY\', \'shadow_SECTION_PROPERTY\', \'table_SECTION_PROPERTY\')">'.GetMessage("CAT_CEDIT_PROP_TABLE_ACTION_HIDE").'</a>';
					else
						echo '&nbsp;';
				?></td>
			</tr>
			<?php
			endwhile;
			?>
			<tr<?= ($rows == 0 ? '': ' style="display:none;"'); ?>>
				<td style="text-align: center;" colspan="4">
					<?= GetMessage("CAT_CEDIT_PROP_TABLE_EMPTY"); ?>
				</td>
			</tr>
			</table>
			<br>
			<select id="shadow_SECTION_PROPERTY" style="display:none;">
			<?php
			foreach($arShadow as $key => $value)
			{
				?><option value="<?=htmlspecialcharsbx($key); ?>"><?=htmlspecialcharsbx($value);?></option><?php
			}
			?>
			</select>
			<select id="select_SECTION_PROPERTY">
			<?php
			foreach($arHidden as $key => $value)
			{
				?><option value="<?=htmlspecialcharsbx($key); ?>"><?=htmlspecialcharsbx($value); ?></option><?php
			}
			?>
			</select>
			<input type="button" value="<?= GetMessage("CAT_CEDIT_PROP_TABLE_ACTION_ADD"); ?>" onclick="addSectionProperty(<?= $arIBlock['ID']; ?>, 'select_SECTION_PROPERTY', 'shadow_SECTION_PROPERTY', 'table_SECTION_PROPERTY')">
			<script>
			<?= CIBlockSectionPropertyLink::getDisplayTypesJsFunction(); ?>
			var target_id = '';
			var target_select_id = '';
			var target_shadow_id = '';
			function addSectionProperty(iblock_id, select_id, shadow_id, table_id)
			{
				var select = BX(select_id);
				if(select && select.value > 0)
				{
					var hidden = BX('hidden_SECTION_PROPERTY_' + select.value);
					var tr = BX('tr_SECTION_PROPERTY_' + select.value);
					if(hidden && tr)
					{
						jsSelectUtils.deleteOption(select_id, select.value);
						hidden.value = 'Y';
						var tds = BX.findChildren(tr, {tag:'td'}, true);
						BX.fx.colorAnimate.addRule('animationRule',"#F8F9FC","#faeeb4", "background-color", 50, 1, true);
						tr.style.display = 'table-row';
						for(var i = 0; i < tds.length; i++)
							BX.fx.colorAnimate(tds[i], 'animationRule');
					}
					adjustEmptyTR(table_id);
				}

				if(select && select.value == 0)
				{
					target_id = table_id;
					target_select_id = select_id;
					target_shadow_id = shadow_id;
					(new BX.CAdminDialog({
						'content_url' : '<?=$selfFolderUrl?>iblock_edit_property.php?lang=<?= LANGUAGE_ID; ?>&IBLOCK_ID='+iblock_id+'&ID=n0&bxpublic=Y&from_module=iblock&return_url=section_edit',
						'width' : 700,
						'height' : 400,
						'buttons': [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
					})).Show();
				}
			}
			function deleteSectionProperty(id, select_id, shadow_id, table_id)
			{
				var hidden = BX('hidden_SECTION_PROPERTY_' + id);
				var tr = BX('tr_SECTION_PROPERTY_' + id);
				if(hidden && tr)
				{
					hidden.value = 'N';
					tr.style.display = 'none';
					var select = BX(select_id);
					var shadow = BX(shadow_id);
					if(select && shadow)
					{
						jsSelectUtils.deleteAllOptions(select);
						for(var i = 0; i < shadow.length; i++)
						{
							if(shadow[i].value <= 0)
								jsSelectUtils.addNewOption(select, shadow[i].value, shadow[i].text);
							else if (BX('hidden_SECTION_PROPERTY_' + shadow[i].value).value == 'N')
								jsSelectUtils.addNewOption(select, shadow[i].value, shadow[i].text);
						}
					}
					adjustEmptyTR(table_id);
				}
			}
			function createSectionProperty(id, name, type, sort, property_type, user_type)
			{
				var tbl = BX(target_id);
				if(tbl)
				{
					var cnt = tbl.rows.length;
					var row = tbl.insertRow(cnt-1);
					//row.vAlign = 'top';
					row.id = 'tr_SECTION_PROPERTY_' + id;
					row.insertCell(-1);
					row.insertCell(-1);
					row.insertCell(-1);
					row.insertCell(-1);
					row.insertCell(-1);
					row.insertCell(-1);
					row.insertCell(-1);
					row.cells[0].align = 'left';
					row.cells[0].innerHTML = '<input type="hidden" name="SECTION_PROPERTY['+id+'][SHOW]" id="hidden_SECTION_PROPERTY_'+id+'" value="Y">'+BX.util.htmlspecialchars(name);
					row.cells[1].align = 'left';
					row.cells[1].innerHTML = type;
					row.cells[2].style.textAlign = 'center';
					row.cells[2].align = 'center';
					row.cells[2].innerHTML = '<input type="checkbox" value="Y" name="SECTION_PROPERTY['+id+'][SMART_FILTER]">';
					var displayTypes = getDisplayTypes(property_type, user_type);
					if (!displayTypes)
					{
						row.cells[3].innerHTML = '&nbsp;';
					}
					else
					{
						var select = BX.create('select', {
							'props': {
								'name': 'SECTION_PROPERTY[' + id + '][DISPLAY_TYPE]'
							}
						});
						for (var x in displayTypes)
						{
							if (displayTypes.hasOwnProperty(x))
							{
								jsSelectUtils.addNewOption(select, x, displayTypes[x], false, false);
							}
						}
						row.cells[3].appendChild(select);
					}
					row.cells[4].style.textAlign = 'center';
					row.cells[4].align = 'center';
					row.cells[4].innerHTML = '<input type="checkbox" value="Y" name="SECTION_PROPERTY['+id+'][DISPLAY_EXPANDED]">';
					row.cells[5].innerHTML = '<?= CUtil::JSEscape($editor->getControlHtml('SECTION_PROPERTY[#ID#][FILTER_HINT]', '', 255)); ?>'.replace('#ID#', id);
					row.cells[6].align = 'left';
					row.cells[6].innerHTML = '<a href="javascript:deleteSectionProperty('+id+', \''+target_select_id+'\', \''+target_shadow_id+'\', \''+target_id+'\')"><?= GetMessageJS("CAT_CEDIT_PROP_TABLE_ACTION_HIDE"); ?></a>';
					var shadow = BX(target_shadow_id);
					if(shadow)
						jsSelectUtils.addNewOption(shadow, id, name);
					adjustEmptyTR(target_id);
					BX.adminFormTools.modifyFormElements(tbl);
				}
			}
			function adjustEmptyTR(table_id)
			{
				var tbl = BX(table_id);
				if(tbl)
				{
					var cnt = tbl.rows.length;
					var tr = tbl.rows[cnt-1];

					var display = 'table-row';
					for(var i = 1; i < cnt-1; i++)
					{
						if(tbl.rows[i].style.display != 'none')
							display = 'none';
					}
					tr.style.display = display;
				}
			}
			</script>
		</td></tr>
		<?php
		$arCatalog = false;
		if (CModule::IncludeModule("catalog"))
			$arCatalog = CCatalogSku::GetInfoByProductIBlock($IBLOCK_ID);

		if (is_array($arCatalog))
		{
			$arPropLinks = CIBlockSectionPropertyLink::GetArray($arCatalog["IBLOCK_ID"], 0);
		?>
		<tr colspan="2" class="heading">
			<td style="text-align: center;"><?= GetMessage("CAT_CEDIT_PROP_SKU_SECTION"); ?></td>
		</tr>
		<tr colspan="2"><td style="text-align: center;">
			<table class="internal" id="table_SKU_SECTION_PROPERTY">
			<tr class="heading">
				<td><?= GetMessage("CAT_CEDIT_PROP_TABLE_NAME"); ?></td>
				<td><?= GetMessage("CAT_CEDIT_PROP_TABLE_TYPE"); ?></td>
				<td><?= GetMessage("CAT_CEDIT_PROP_TABLE_SMART_FILTER"); ?></td>
				<td><?= GetMessage("CAT_CEDIT_PROP_TABLE_DISPLAY_TYPE"); ?></td>
				<td><?= GetMessage("CAT_CEDIT_PROP_TABLE_DISPLAY_EXPANDED"); ?></td>
				<td><?= GetMessage("CAT_CEDIT_PROP_TABLE_FILTER_HINT"); ?></td>
				<td><?= GetMessage("CAT_CEDIT_PROP_TABLE_ACTION"); ?></td></tr>
			<?php
			if(CIBlockRights::UserHasRightTo($arCatalog["IBLOCK_ID"], $arCatalog["IBLOCK_ID"], "iblock_edit"))
				$arShadow = $arHidden = array(
					-1 => GetMessage("CAT_CEDIT_PROP_SELECT_CHOOSE"),
					0 => GetMessage("CAT_CEDIT_PROP_SELECT_CREATE"),
				);
			else
				$arShadow = $arHidden = array(
					-1 => GetMessage("CAT_CEDIT_PROP_SELECT_CHOOSE"),
				);

			$rsProps =  CIBlockProperty::GetList(array(
					"SORT"=>"ASC",
					'ID' => 'ASC',
				), array(
					"IBLOCK_ID" => $arCatalog["IBLOCK_ID"],
					"CHECK_PERMISSIONS" => "N",
					"ACTIVE"=>"Y",
				));
			$rows = 0;
			while ($arProp = $rsProps->Fetch()):

				if($arProp["ID"] == $arCatalog["SKU_PROPERTY_ID"])
					continue;

				if(isset($arPropLinks[$arProp["ID"]]))
				{
					$rows++;
					$arLink = $arPropLinks[$arProp["ID"]];
					if($arLink["INHERITED"] == "N")
						$arShadow[$arProp["ID"]] = $arProp["NAME"];
				}
				else
				{
					$arLink = false;
					$arHidden[$arProp["ID"]] = $arProp["NAME"];
					$arShadow[$arProp["ID"]] = $arProp["NAME"];
				}
				$linkExists = is_array($arLink);
				$fileProperty = $arProp['PROPERTY_TYPE'] === PropertyTable::TYPE_FILE;
			?>
			<tr id="tr_SECTION_PROPERTY_<?= $arProp['ID']; ?>"<?= (!$linkExists ? ' style="display:none;"' : ''); ?>>
				<td style="text-align: left;">
					<?php
					if (!$linkExists || $arLink["INHERITED"] == "N"):
						?>
					<input type="hidden" name="SECTION_PROPERTY[<?= $arProp['ID']; ?>][SHOW]" id="hidden_SECTION_PROPERTY_<?= $arProp['ID']; ?>" value="<?= ($linkExists ? 'Y': 'N') ;?>">
						<?php
					endif;
					?>
					<?= htmlspecialcharsbx($arProp["NAME"]); ?>
				</td>
				<td style="text-align: left;"><?php
					if($arProp['PROPERTY_TYPE'] == "S" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_S");
					elseif($arProp['PROPERTY_TYPE'] == "N" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_N");
					elseif($arProp['PROPERTY_TYPE'] == "L" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_L");
					elseif($arProp['PROPERTY_TYPE'] == "F" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_F");
					elseif($arProp['PROPERTY_TYPE'] == "G" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_G");
					elseif($arProp['PROPERTY_TYPE'] == "E" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_E");
					elseif($arProp['USER_TYPE'] && is_array($ar = CIBlockProperty::GetUserType($arProp['USER_TYPE'])))
						echo htmlspecialcharsbx($ar["DESCRIPTION"]);
					else
						echo GetMessage("IBLOCK_PROP_S");
				?></td>
				<td style="text-align:center"><?php
					echo '<input type="checkbox" value="Y" '.(($linkExists && $arLink["INHERITED"] == "Y") || $fileProperty ? 'disabled="disabled"': '').' name="SECTION_PROPERTY['.$arProp['ID'].'][SMART_FILTER]" '.($linkExists && $arLink["SMART_FILTER"] == "Y"? 'checked="checked"': '').'>';
				?></td>
				<td style="text-align: left;">
					<?php
					$displayTypes = CIBlockSectionPropertyLink::getDisplayTypes($arProp["PROPERTY_TYPE"], $arProp["USER_TYPE"]);
					if ($displayTypes)
					{
						echo SelectBoxFromArray('SECTION_PROPERTY['.$arProp['ID'].'][DISPLAY_TYPE]', array(
							"REFERENCE_ID" => array_keys($displayTypes),
							"REFERENCE" => array_values($displayTypes),
						), $linkExists ? $arLink["DISPLAY_TYPE"] : '', '', '');
					}
					else
					{
						echo '&nbsp;';
					}
					?>
				</td>
				<td style="text-align:center"><?php
					echo '<input type="checkbox" value="Y" '.(($linkExists && $arLink["INHERITED"] == "Y") || $fileProperty ? 'disabled="disabled"': '').' name="SECTION_PROPERTY['.$arProp['ID'].'][DISPLAY_EXPANDED]" '.($linkExists && $arLink["DISPLAY_EXPANDED"] == "Y"? 'checked="checked"': '').'>';
				?></td>
				<td>
				<?php
					if ($fileProperty)
					{
						echo '&nbsp;';
					}
					elseif (!$linkExists || $arLink["INHERITED"] == "N")
					{
						$filterHint = ($linkExists ? (string)$arLink['FILTER_HINT'] : '');
						echo $editor->getControlHtml('SECTION_PROPERTY['.$arProp['ID'].'][FILTER_HINT]', $filterHint, 255);
					}
					elseif ($linkExists && $arLink['FILTER_HINT'] <> '')
					{
						echo CTextParser::closeTags($arLink['FILTER_HINT']);
					}
					else
					{
						echo '&nbsp;';
					}
				?></td>
				<td style="text-align: left;"><?php
					if(!$linkExists || $arLink["INHERITED"] == "N")
						echo '<a class="bx-action-href" href="javascript:deleteSectionProperty('.$arProp['ID'].', \'select_SKU_SECTION_PROPERTY\', \'shadow_SKU_SECTION_PROPERTY\', \'table_SKU_SECTION_PROPERTY\')">'.GetMessage("CAT_CEDIT_PROP_TABLE_ACTION_HIDE").'</a>';
					else
						echo '&nbsp;';
				?></td>
			</tr>
			<?php
			endwhile;
			?>
			<tr<?= ($rows == 0 ? '': ' style="display:none;"'); ?>>
				<td style="text-align: center;" colspan="4">
					<?= GetMessage("CAT_CEDIT_PROP_TABLE_EMPTY"); ?>
				</td>
			</tr>
			</table>
			<br>
			<select id="shadow_SKU_SECTION_PROPERTY" style="display:none;">
			<?php
			foreach($arShadow as $key => $value)
			{
				?><option value="<?= htmlspecialcharsbx($key); ?>"><?= htmlspecialcharsbx($value); ?></option><?php
			}
			?>
			</select>
			<select id="select_SKU_SECTION_PROPERTY">
			<?php
			foreach($arHidden as $key => $value)
			{
				?><option value="<?= htmlspecialcharsbx($key); ?>"><?= htmlspecialcharsbx($value); ?></option><?php
			}
			?>
			</select>
			<input type="button" value="<?= GetMessage("CAT_CEDIT_PROP_TABLE_ACTION_ADD")?>" onclick="addSectionProperty(<?= $arCatalog["IBLOCK_ID"];?>, 'select_SKU_SECTION_PROPERTY', 'shadow_SKU_SECTION_PROPERTY', 'table_SKU_SECTION_PROPERTY')">
		</td></tr>
			<?php
		}
		?>
	<?php
$tabControl->EndCustomField("SECTION_PROPERTY", '');
$tabControl->Buttons(array("ajaxMode" => false, "disabled" => false));
$tabControl->SetShowSettings(false);
$tabControl->Show();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
