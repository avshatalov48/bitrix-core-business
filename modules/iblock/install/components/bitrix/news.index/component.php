<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Loader,
	Bitrix\Main,
	Bitrix\Iblock;

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if(!is_array($arParams["IBLOCKS"]))
	$arParams["IBLOCKS"] = array($arParams["IBLOCKS"]);
foreach($arParams["IBLOCKS"] as $key=>$val)
	if(!$val)
		unset($arParams["IBLOCKS"][$key]);

$arParams["IBLOCK_SORT_BY"] = trim($arParams["IBLOCK_SORT_BY"]);
if(!in_array($arParams["IBLOCK_SORT_BY"], array("SORT","NAME","ID")))
	$arParams["SORT_BY1"] = "SORT";
$arParams["IBLOCK_SORT_ORDER"] = strtoupper($arParams["IBLOCK_SORT_ORDER"]);
if($arParams["IBLOCK_SORT_ORDER"]!="DESC")
	$arParams["IBLOCK_SORT_ORDER"]="ASC";

$arParams["SORT_BY1"] = trim($arParams["SORT_BY1"]);
if(strlen($arParams["SORT_BY1"])<=0)
	$arParams["SORT_BY1"] = "ACTIVE_FROM";
if(!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["SORT_ORDER1"]))
	$arParams["SORT_ORDER1"]="DESC";

if(strlen($arParams["SORT_BY2"])<=0)
	$arParams["SORT_BY2"] = "SORT";
if(!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["SORT_ORDER2"]))
	$arParams["SORT_ORDER2"]="ASC";

if(!is_array($arParams["FIELD_CODE"]))
	$arParams["FIELD_CODE"] = array();
foreach($arParams["FIELD_CODE"] as $key=>$val)
	if($val==="")
		unset($arParams["FIELD_CODE"][$key]);
if(!is_array($arParams["PROPERTY_CODE"]))
	$arParams["PROPERTY_CODE"] = array();
foreach($arParams["PROPERTY_CODE"] as $k=>$v)
	if($v==="")
		unset($arParams["PROPERTY_CODE"][$k]);

if(strlen($arParams["FILTER_NAME"])<=0 || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"]))
{
	$arrFilter = array();
}
else
{
	global ${$arParams["FILTER_NAME"]};
	$arrFilter = ${$arParams["FILTER_NAME"]};
	if(!is_array($arrFilter))
		$arrFilter = array();
}

$arParams["NEWS_COUNT"] = intval($arParams["NEWS_COUNT"]);
if($arParams["NEWS_COUNT"]<=0)
	$arParams["NEWS_COUNT"] = 5;

$arParams["IBLOCK_URL"]=trim($arParams["IBLOCK_URL"]);
$arParams["DETAIL_URL"]=trim($arParams["DETAIL_URL"]);

$arParams["ACTIVE_DATE_FORMAT"] = trim($arParams["ACTIVE_DATE_FORMAT"]);
if(strlen($arParams["ACTIVE_DATE_FORMAT"])<=0)
	$arParams["ACTIVE_DATE_FORMAT"] = $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT"));

$arResult["IBLOCKS"]=array();

if($this->startResultCache(false, ($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups())))
{
	if(!Loader::includeModule("iblock"))
	{
		$this->abortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}
	//SELECT
	$arSelect = array_merge($arParams["FIELD_CODE"], array(
		"ID",
		"IBLOCK_ID",
		"ACTIVE_FROM",
		"NAME",
		"DETAIL_TEXT_TYPE",
		"DETAIL_PAGE_URL",
		"PREVIEW_TEXT_TYPE",
	));
	$bGetProperty = count($arParams["PROPERTY_CODE"])>0;
	if($bGetProperty)
		$arSelect[]="PROPERTY_*";
	//WHERE
	//$arrFilter["IBLOCK_TYPE"] = $arParams["IBLOCK_TYPE"];
	$arrFilter["ACTIVE"] = "Y";
	$arrFilter["ACTIVE_DATE"] = "Y";
	$arrFilter["CHECK_PERMISSIONS"] = "Y";
	//ORDER BY
	$arOrder = array(
		$arParams["SORT_BY1"]=>$arParams["SORT_ORDER1"],
		$arParams["SORT_BY2"]=>$arParams["SORT_ORDER2"],
	);
	if(!array_key_exists("ID", $arOrder))
		$arOrder["ID"] = "DESC";
	$arIBlockOrder = array(
		$arParams["IBLOCK_SORT_BY"]=>$arParams["IBLOCK_SORT_ORDER"],
	);
	if(!array_key_exists("ID", $arIBlockOrder))
		$arIBlockOrder["ID"] = "DESC";
	$rsIBlocks = CIBlock::GetList($arIBlockOrder, array("LID"=>SITE_ID, "ACTIVE"=>"Y", "ID"=>$arParams["IBLOCKS"]));
	while($arIBlock = $rsIBlocks->GetNext())
	{
		$arButtons = CIBlock::GetPanelButtons(
			$arIBlock["ID"],
			0,
			0,
			array("SECTION_BUTTONS"=>false, "SESSID"=>false)
		);
		$arIBlock["ADD_ELEMENT_LINK"] = $arButtons["edit"]["add_element"]["ACTION_URL"];

		$arIBlock["~LIST_PAGE_URL"] = str_replace(
			array("#SERVER_NAME#", "#SITE_DIR#", "#IBLOCK_TYPE_ID#", "#IBLOCK_ID#", "#IBLOCK_CODE#", "#IBLOCK_EXTERNAL_ID#", "#CODE#"),
			array(SITE_SERVER_NAME, SITE_DIR, $arIBlock["IBLOCK_TYPE_ID"], $arIBlock["ID"], $arIBlock["CODE"], $arIBlock["EXTERNAL_ID"], $arIBlock["CODE"]),
			strlen($arParams["IBLOCK_URL"])? trim($arParams["~IBLOCK_URL"]): $arIBlock["~LIST_PAGE_URL"]
		);
		$arIBlock["~LIST_PAGE_URL"] = preg_replace("'/+'s", "/", $arIBlock["~LIST_PAGE_URL"]);
		$arIBlock["LIST_PAGE_URL"] = htmlspecialcharsbx($arIBlock["~LIST_PAGE_URL"]);

		$arIBlock["ITEMS"]=array();
		$arrFilter["IBLOCK_ID"] = $arIBlock["ID"];
		$rsItem = CIBlockElement::GetList($arOrder, $arrFilter, false, array("nTopCount"=>$arParams["NEWS_COUNT"]), $arSelect);
		$rsItem->SetUrlTemplates($arParams["DETAIL_URL"]);
		while($obItem = $rsItem->GetNextElement())
		{
			$arItem = $obItem->GetFields();

			$arButtons = CIBlock::GetPanelButtons(
				$arItem["IBLOCK_ID"],
				$arItem["ID"],
				0,
				array("SECTION_BUTTONS"=>false, "SESSID"=>false)
			);
			$arItem["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
			$arItem["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];

			if(strlen($arItem["ACTIVE_FROM"])>0)
				$arItem["DISPLAY_ACTIVE_FROM"] = CIBlockFormatProperties::DateFormat($arParams["ACTIVE_DATE_FORMAT"], MakeTimeStamp($arItem["ACTIVE_FROM"], CSite::GetDateFormat()));
			else
				$arItem["DISPLAY_ACTIVE_FROM"] = "";

			Iblock\InheritedProperty\ElementValues::queue($arItem["IBLOCK_ID"], $arItem["ID"]);

			$arItem["FIELDS"] = array();

			if($bGetProperty)
				$arItem["PROPERTIES"] = $obItem->GetProperties();
			$arItem["DISPLAY_PROPERTIES"]=array();
			foreach($arParams["PROPERTY_CODE"] as $pid)
			{
				$prop = &$arItem["PROPERTIES"][$pid];
				if(
					(is_array($prop["VALUE"]) && count($prop["VALUE"])>0)
					|| (!is_array($prop["VALUE"]) && strlen($prop["VALUE"])>0)
				)
				{
					$arItem["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arItem, $prop, "news_out");
				}
			}

			$arIBlock["ITEMS"][] = $arItem;
		}
		$arResult["IBLOCKS"][]=$arIBlock;
	}

	foreach ($arResult["IBLOCKS"] as &$arIBlock)
	{
		foreach ($arIBlock["ITEMS"] as &$arItem)
		{
			$ipropValues = new Iblock\InheritedProperty\ElementValues($arItem["IBLOCK_ID"], $arItem["ID"]);
			$arItem["IPROPERTY_VALUES"] = $ipropValues->getValues();

			Iblock\Component\Tools::getFieldImageData(
				$arItem,
				array('PREVIEW_PICTURE', 'DETAIL_PICTURE'),
				Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT,
				'IPROPERTY_VALUES'
			);
			foreach($arParams["FIELD_CODE"] as $code)
				if(array_key_exists($code, $arItem))
					$arItem["FIELDS"][$code] = $arItem[$code];
		}
		unset($arItem);
	}
	unset($arIBlock);

	$this->setResultCacheKeys(array(
	));
	$this->includeComponentTemplate();
}
