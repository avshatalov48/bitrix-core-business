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

if(!is_array($arParams["HEADERS"]))
	$arParams["HEADERS"] = array();

if(!is_array($arParams["FOOTER"]))
	$arParams["FOOTER"] = array();

if(!is_array($arParams["FILTER"]))
	$arParams["FILTER"] = array();

if(!is_array($arParams["SORT"]))
	$arParams["SORT"] = array();

if(!is_array($arParams["SORT_VARS"]))
	$arParams["SORT_VARS"] = array();
if(!isset($arParams["SORT_VARS"]["by"]))
	$arParams["SORT_VARS"]["by"] = "by";
if(!isset($arParams["SORT_VARS"]["order"]))
	$arParams["SORT_VARS"]["order"] = "order";

if($arParams["SHOW_FORM_TAG"] !== 'N' && $arParams["SHOW_FORM_TAG"] !== false)
	$arParams["SHOW_FORM_TAG"] = true;
else
	$arParams["SHOW_FORM_TAG"] = false;	
	
if($arParams["ACTION_ALL_ROWS"] === "Y" || $arParams["ACTION_ALL_ROWS"] === true)
	$arParams["ACTION_ALL_ROWS"] = true;
else
	$arParams["ACTION_ALL_ROWS"] = false;

if($arParams["EDITABLE"] === "N" || $arParams["EDITABLE"] === false)
	$arParams["EDITABLE"] = false;
else
	$arParams["EDITABLE"] = true;

if($arParams["USE_THEMES"] !== 'N' && $arParams["USE_THEMES"] !== false && CPageOption::GetOptionString("main.interface", "use_themes", "Y") !== "N")
	$arParams["USE_THEMES"] = true;
else
	$arParams["USE_THEMES"] = false;

$arParams["GRID_ID"] = preg_replace("/[^a-z0-9_]/i", "", $arParams["GRID_ID"]);

if(!is_array($arParams["~NAV_PARAMS"]))
{
	$arParams["~NAV_PARAMS"] = array();
}

$arResult["HEADERS"] = array();

//*********************
//get saved columns and sorting from user settings
//*********************
$grid_options = new CGridOptions($arParams["GRID_ID"]);

$aOptions = $grid_options->GetOptions();

if(!isset($aOptions["views"]["default"]["name"]))
	$aOptions["views"]["default"]["name"] = GetMessage("interface_grid_default_view");

$func = create_function('$a, $b', 'return strcmp($a["name"], $b["name"]);');
uasort($aOptions["views"], $func);

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
// Filter
//*********************

if(is_array($arParams["FILTER_PRESETS"]) && !empty($arParams["FILTER_PRESETS"]))
	$arResult["OPTIONS"]["filters"] = array_merge($arParams["FILTER_PRESETS"], $aOptions["filters"]);

$arResult["FILTER"] = $grid_options->GetFilter($arParams["FILTER"]);

$aVisRows = array();
$aFilterTmp = explode(",", $aOptions["filter_rows"]);
foreach($aFilterTmp as $field)
{
	if(($f = trim($field)) <> "")
		$aVisRows[$f] = $f;
}

$arResult["FILTER_ROWS"] = array();
foreach($arParams["FILTER"] as $field)
{
	if($field["filtered"] == true)
	{
		$arResult["FILTER_ROWS"][$field["id"]] = true;
		continue;
	}

	if(isset($arResult["FILTER"][$field["id"]."_from"]))
		$flt = $arResult["FILTER"][$field["id"]."_from"];
	elseif(isset($arResult["FILTER"][$field["id"]."_to"]))
		$flt = $arResult["FILTER"][$field["id"]."_to"];
	else
		$flt = $arResult["FILTER"][$field["id"]];
	
	if(is_array($flt) && !empty($flt) || !is_array($flt) && $flt <> '')
		$arResult["FILTER_ROWS"][$field["id"]] = true;
	elseif(array_key_exists($field["id"], $aVisRows))
		$arResult["FILTER_ROWS"][$field["id"]] = true;
	elseif(!isset($aOptions["filter_rows"]))
		$arResult["FILTER_ROWS"][$field["id"]] = ($field["default"] == true);
	else
		$arResult["FILTER_ROWS"][$field["id"]] = false;
}
if(!in_array(true, $arResult["FILTER_ROWS"]))
{
	foreach($arParams["FILTER"] as $field)
	{
		$arResult["FILTER_ROWS"][$field["id"]] = true;
		break;
	}
}

//*********************
// Columns names
//*********************

$aCurView = $aOptions["views"][$aOptions["current_view"]];
$arResult["COLS_NAMES"] = array();
foreach($arParams["HEADERS"] as $i => $header)
{
	$arResult["COLS_NAMES"][$header["id"]] = $header["name"];
	if(isset($aCurView["custom_names"][$header["id"]]))
	{
		$arParams["HEADERS"][$i]["original_name"] = $header["name"];
		$arParams["HEADERS"][$i]["name"] = htmlspecialcharsbx($aCurView["custom_names"][$header["id"]]);
	}
}

//*********************
// Columns
//*********************

$aColsTmp = explode(",", $aCurView["columns"]);
$aCols = array();
foreach($aColsTmp as $col)
{
	if(trim($col)<>"")
		$aCols[] = trim($col);
}

$bEmptyCols = empty($aCols);
foreach($arParams["HEADERS"] as $param)
{
	if(($bEmptyCols && $param["default"]==true) || in_array($param["id"], $aCols))
		$arResult["HEADERS"][$param["id"]] = $param;
}

if(!$bEmptyCols)
{
	foreach($aCols as $i=>$col)
		$arResult["HEADERS"][$col]["__sort"] = $i;
	uasort($arResult["HEADERS"], create_function('$a, $b', 'if($a["__sort"] == $b["__sort"]) return 0; return ($a["__sort"] < $b["__sort"])? -1 : 1;'));
}

//*********************
// Sorting and URL
//*********************

$uri = new \Bitrix\Main\Web\Uri($this->request->getRequestUri());
$uri->deleteParams(\Bitrix\Main\HttpRequest::getSystemParameters());
$uri->deleteParams(array("bxajaxid", "AJAX_CALL", $arParams["SORT_VARS"]["by"], $arParams["SORT_VARS"]["order"]));

if($arParams["FORM_ID"] <> '' && $arParams["TAB_ID"] <> '')
{
	$uri->addParams(array($arParams["FORM_ID"].'_active_tab' => $arParams["TAB_ID"]));
}

$arResult["CURRENT_URL"] = $uri->getUri();

$sep = (mb_strpos($arResult["CURRENT_URL"], "?") !== false? "&":"?");

$sortBy = key($arParams["SORT"]);
$sortOrder = current($arParams["SORT"]);

foreach($arResult["HEADERS"] as $id=>$header)
{
	if($header["sort"] <> '')
	{
		$arResult["HEADERS"][$id]["sort_state"] = "";
		if(mb_strtolower($header["sort"]) == mb_strtolower($sortBy))
		{
			if(mb_strtolower($sortOrder) == "desc")
				$arResult["HEADERS"][$id]["sort_state"] = "desc";
			else
				$arResult["HEADERS"][$id]["sort_state"] = "asc";
		}
		$arResult["HEADERS"][$id]["sort_url"] = htmlspecialcharsbx($arResult["CURRENT_URL"].$sep.$arParams["SORT_VARS"]["by"]."=".$header["sort"]."&".$arParams["SORT_VARS"]["order"]."=");
		$arResult["HEADERS"][$id]["order"] = ($header["order"] == 'desc'? 'desc':'asc');
	}
}


//*********************
// Editable columns detection
//*********************

$arResult["EDIT_DATE"] = false;
$arResult["ALLOW_EDIT"] = false;
$arResult["ALLOW_INLINE_EDIT"] = false;
$arResult["COLS_EDIT_META"] = array();
foreach($arResult["HEADERS"] as $header)
{
	if($arParams["EDITABLE"] && isset($header["editable"]) && $header["editable"] !== false)
	{
		$arResult["ALLOW_EDIT"] = true;
		$arResult["ALLOW_INLINE_EDIT"] = true;
		if($header["type"] == "date")
			$arResult["EDIT_DATE"] = true;
	}

	$arResult["COLS_EDIT_META"][$header["id"]] = array(
			"editable" => (isset($header["editable"]) && $header["editable"] !== false),
			"type"=>($header["type"] <> ''? $header["type"] : "text"),
	);
	if($arParams["EDITABLE"] && is_array($header["editable"]))
		foreach($header["editable"] as $attr => $val)
			$arResult["COLS_EDIT_META"][$header["id"]][$attr] = $val;
}

//*********************
// Editable Data
//*********************

$arResult["DATA_FOR_EDIT"] = array();
if($arResult["ALLOW_EDIT"])
{
	$arResult["ALLOW_EDIT"] = false;
	foreach($arParams["ROWS"] as $row)
	{
		if($row["editable"] !== false)
		{
			$arResult["ALLOW_EDIT"] = true;
			$id = ($row["id"] <> ''? $row["id"] : $row["data"]["ID"]);
			foreach($arResult["HEADERS"] as $header)
			{
				if(isset($header["editable"]) && $header["editable"] !== false)
				{
					if(isset($row["editable"][$header["id"]]) && $row["editable"][$header["id"]] === false)
						$arResult["DATA_FOR_EDIT"][$id][$header["id"]] = false;
					else
						$arResult["DATA_FOR_EDIT"][$id][$header["id"]] = $row["data"]['~'.$header["id"]];
				}
			}
		}
	}
}

if($arParams["EDITABLE"] && is_array($arParams["ACTIONS"]) && count($arParams["ACTIONS"]) > 0)
	$arResult["ALLOW_EDIT"] = true;

//*********************
// Navigation
//*********************

if($arParams["NAV_STRING"] <> '')
{
	$arResult["NAV_STRING"] = $arParams["~NAV_STRING"];
}
elseif(is_object($arParams["NAV_OBJECT"]))
{
	if(($nav = $arParams["NAV_OBJECT"]) instanceof \Bitrix\Main\UI\PageNavigation)
	{
		$params = array_merge(
			array(
				"NAV_OBJECT" => $nav,
				"PAGE_WINDOW" => 5,
				"SHOW_ALWAYS" => true,
			),
			$arParams["~NAV_PARAMS"]
		);

		ob_start();

		$APPLICATION->IncludeComponent(
			"bitrix:main.pagenavigation",
			"modern",
			$params,
			false,
			array(
				"HIDE_ICONS" => "Y",
			)
		);

		$arResult["NAV_STRING"] = ob_get_clean();
	}
	else
	{
		/** @var CDBResult $nav */
		$nav = $arParams["NAV_OBJECT"];
		$nav->nPageWindow = 5;
		//dirty hack
		if($arParams["FORM_ID"] <> '' && $arParams["TAB_ID"] <> '')
			$_GET[$arParams["FORM_ID"].'_active_tab'] = $arParams["TAB_ID"];
		$arResult["NAV_STRING"] = $nav->GetPageNavStringEx($dummy, "", "modern", true, null, $arParams["~NAV_PARAMS"]);
	}
}

//*********************
// Self-explaining
//*********************

$this->IncludeComponentTemplate();
