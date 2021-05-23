<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

if(!is_array($arParams["TABS"]))
	$arParams["TABS"] = array();

if($arParams["CAN_EXPAND_TABS"] !== 'N' && $arParams["CAN_EXPAND_TABS"] !== false)
	$arParams["CAN_EXPAND_TABS"] = true;
else
	$arParams["CAN_EXPAND_TABS"] = false;

if($arParams["SHOW_FORM_TAG"] !== 'N' && $arParams["SHOW_FORM_TAG"] !== false)
	$arParams["SHOW_FORM_TAG"] = true;
else
	$arParams["SHOW_FORM_TAG"] = false;

if($arParams["SHOW_SETTINGS"] !== 'N' && $arParams["SHOW_SETTINGS"] !== false)
	$arParams["SHOW_SETTINGS"] = true;
else
	$arParams["SHOW_SETTINGS"] = false;

if($arParams["USE_THEMES"] !== 'N' && $arParams["USE_THEMES"] !== false && CPageOption::GetOptionString("main.interface", "use_themes", "Y") !== "N")
	$arParams["USE_THEMES"] = true;
else
	$arParams["USE_THEMES"] = false;

if($arParams["MAX_FILE_SIZE"] == '')
	$arParams["MAX_FILE_SIZE"] = 102400;

$arParams["FORM_ID"] = preg_replace("/[^a-z0-9_]/i", "", $arParams["FORM_ID"]);

//*********************
//get saved options
//*********************
$aOptions = CUserOptions::GetOption("main.interface.form", $arParams["FORM_ID"], array());

if(!is_array($aOptions["tabs"]))
	$aOptions["tabs"] = array();

if($arParams["USE_THEMES"] && $arParams["THEME_GRID_ID"] <> '')
{
	$aGridOptions = CUserOptions::GetOption("main.interface.grid", $arParams["THEME_GRID_ID"], array());
	if($aGridOptions["theme"] <> '')
		$aOptions["theme"] = $aGridOptions["theme"];
}

$arResult["OPTIONS"] = $aOptions;

$arResult["GLOBAL_OPTIONS"] = CUserOptions::GetOption("main.interface", "global", array(), 0);

if($arParams["USE_THEMES"])
{
	if($arResult["GLOBAL_OPTIONS"]["theme_template"][SITE_TEMPLATE_ID] <> '')
		$arResult["GLOBAL_OPTIONS"]["theme"] = $arResult["GLOBAL_OPTIONS"]["theme_template"][SITE_TEMPLATE_ID];

	if($arResult["OPTIONS"]["theme"] == '')
		$arResult["OPTIONS"]["theme"] = $arResult["GLOBAL_OPTIONS"]["theme"];

	$arResult["OPTIONS"]["theme"] = preg_replace("/[^a-z0-9_.-]/i", "", $arResult["OPTIONS"]["theme"]);
}
else
{
	$arResult["OPTIONS"]["theme"] = '';
}

//Admin can change common settings
$arResult["IS_ADMIN"] = $USER->CanDoOperation('edit_other_settings');

//*********************
// Tabs manipulating
//*********************

$arAllFields = array();
$arPersistentFields = array();
$arResult["TABS"] = array();
foreach($arParams["TABS"] as $tab)
{
	$tabId = $tab["id"];
	$arResult["TABS"][$tabId] = $tab;
	$aFields = array();
	if(is_array($tab["fields"]))
	{
		foreach($tab["fields"] as $field)
		{
			$id = $field["id"];
			$arAllFields[$id] = $aFields[$id] = $field;

			if((isset($field["required"]) && $field["required"])
				|| (isset($field["persistent"]) && $field["persistent"]))
			{
				$arPersistentFields[$id] = array("tabId" => $tabId, "field" => $field);
			}
		}
	}
	$arResult["TABS"][$tab["id"]]["fields"] = $aFields;
}

$arResult["TABS_META"] = array();
$arResult["AVAILABLE_FIELDS"] = array();

if($arParams["SHOW_SETTINGS"])
{
	if(!empty($aOptions["tabs"]))
	{
		$aTabs = array();
		$aUsedFields = array();
		foreach($aOptions["tabs"] as $tab)
		{
			$aTabs[$tab["id"]] = $tab;
			$aTabs[$tab["id"]]["icon"] = $arResult["TABS"][$tab["id"]]["icon"];
			$aFields = array();
			if(is_array($tab["fields"]))
			{
				foreach($tab["fields"] as $field)
				{
					$id = $field["id"];
					$fieldType = isset($field["type"]) ? $field["type"] : "";
					if(isset($arAllFields[$id]))
					{
						$aFields[$id] = $arAllFields[$id];
						$aFields[$id]["name"] = $field["name"];
						$aUsedFields[$id] = true;

						unset($arPersistentFields[$id]);

					}
					elseif($fieldType === "section" || $fieldType === "")
					{
						$aFields[$id] = $field;
						$aFields[$id]["type"] = "section";

						unset($arPersistentFields[$id]);
					}
				}
			}
			$aTabs[$tab["id"]]["fields"] = $aFields;
		}

		reset($aTabs);
		$firstTabId = key($aTabs);
		if($firstTabId !== null)
		{
			foreach($arPersistentFields as $id => $fieldInfo)
			{
				$tabId = $fieldInfo["tabId"];
				$field = $fieldInfo["field"];

				if(!isset($aTabs[$tabId]))
				{
					$tabId = $firstTabId;
				}

				$fields = $aTabs[$tabId]["fields"];
				$firstField = reset($fields);
				if(!is_array($firstField))
				{
					$aTabs[$tabId]["fields"] = array($id => $field);
				}
				elseif(isset($firstField["type"]) && $firstField["type"] === "section")
				{
					//Insert in to beginning of first section of tab
					$aTabs[$tabId]["fields"] = array_slice($aTabs[$tabId]["fields"], 0, 1, true)
						+ array($id => $field)
						+ array_slice($aTabs[$tabId]["fields"], 1, null, true);
				}
				else
				{
					//Insert in to beginning of tab
					$aTabs[$tabId]["fields"] = array_merge(array($id => $field), $aTabs[$tabId]["fields"]);
				}

			}
		}
		$arMeta = $aTabs;

		foreach($arAllFields as $id => $field)
			if(!array_key_exists($id, $aUsedFields))
				$arResult["AVAILABLE_FIELDS"][$id] = array("id"=>$id, "name"=>$field["name"], "type"=>$field["type"]);
	
		if($arResult["OPTIONS"]["settings_disabled"] <> "Y")
		{
			$arResult["TABS"] = $aTabs;
		}
	}
	else
	{
		$arMeta = $arResult["TABS"];
	}
	
	//tabs info for settings
	foreach($arMeta as $id=>$tab)
	{
		$arResult["TABS_META"][$id] = array('id'=>$id, 'name'=>$tab['name'], 'title'=>$tab['title']);
		foreach($tab['fields'] as $field)
			$arResult["TABS_META"][$id]['fields'][$field['id']] = array("id"=>$field["id"], "name"=>$field["name"], "type"=>$field["type"]);
	}
}


$hidden = $arParams["FORM_ID"]."_active_tab";
if(isset($_REQUEST[$hidden]) && array_key_exists($_REQUEST[$hidden], $arResult["TABS"]))
{
	$arResult["SELECTED_TAB"] = $_REQUEST[$hidden];
}
else
{
	foreach($arResult["TABS"] as $tab)
	{
		$arResult["SELECTED_TAB"] = $tab["id"];
		break;
	}
}

//*********************
// Self-explaining
//*********************

$this->IncludeComponentTemplate();
