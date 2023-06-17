<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
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

if (!isset($arParams["CACHE_TIME"]))
{
	$arParams["CACHE_TIME"] = 3600;
}

if (
	isset($arParams["SENDER_CHAIN_ID"])
	&& isset($arParams["PREVENT_SEND_IF_NO_NEWS"])
	&& $arParams["PREVENT_SEND_IF_NO_NEWS"] === "Y"
)
{
	$arParams["SENDER_CHAIN_ID"] = (int)($arParams["SENDER_CHAIN_ID"] ?? 0);
}
else
{
	$arParams["SENDER_CHAIN_ID"] = 0;
}

$arParams["NEWS_COUNT"] = (int)($arParams["NEWS_COUNT"] ?? 0);
if ($arParams["NEWS_COUNT"] <= 0)
{
	$arParams["NEWS_COUNT"] = 20;
}
$arNavParams = array(
	"nTopCount" => $arParams["NEWS_COUNT"],
	"bDescPageNumbering" => $arParams["PAGER_DESC_NUMBERING"] ?? 'N',
);
$arNavigation = false;

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"] ?? '');
if (empty($arParams["IBLOCK_TYPE"]))
{
	$arParams["IBLOCK_TYPE"] = "news";
}

/** if IBLOCK_ID is string it'll be used as iblock code for selection. Look for details below into cache block*/
$arParams["IBLOCK_ID"] = trim($arParams["IBLOCK_ID"] ?? '');
$arParams["PARENT_SECTION"] = (int)($arParams["PARENT_SECTION"] ?? 0);
$arParams["PARENT_SECTION_CODE"] ??= '';
$arParams["INCLUDE_SUBSECTIONS"] = ($arParams["INCLUDE_SUBSECTIONS"] ?? '') !== "N";

$arParams["SORT_BY1"] = trim($arParams["SORT_BY1"] ?? '');
if (empty($arParams["SORT_BY1"]))
{
	$arParams["SORT_BY1"] = "ACTIVE_FROM";
}
if (
	!isset($arParams["SORT_ORDER1"])
	|| !preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["SORT_ORDER1"])
)
{
	$arParams["SORT_ORDER1"] = "DESC";
}

if (empty($arParams["SORT_BY2"]))
{
	$arParams["SORT_BY2"] = "SORT";
}
if (
	!isset($arParams["SORT_ORDER2"])
	|| !preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["SORT_ORDER2"])
)
{
	$arParams["SORT_ORDER2"] = "ASC";
}

$arrFilter = [];
if (
	!empty($arParams["FILTER_NAME"])
	&& preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"])
)
{
	$arrFilter = $GLOBALS[$arParams["FILTER_NAME"]] ?? [];
	if (!is_array($arrFilter))
	{
		$arrFilter = [];
	}
}

$arParams["CHECK_DATES"] = ($arParams["CHECK_DATES"] ?? '') !== "N";

if (empty($arParams["FIELD_CODE"]) || !is_array($arParams["FIELD_CODE"]))
{
	$arParams["FIELD_CODE"] = [];
}
foreach ($arParams["FIELD_CODE"] as $key => $value)
{
	if (!$value)
	{
		unset($arParams["FIELD_CODE"][$key]);
	}
}

if (empty($arParams["PROPERTY_CODE"]) || !is_array($arParams["PROPERTY_CODE"]))
{
	$arParams["PROPERTY_CODE"] = [];
}

foreach ($arParams["PROPERTY_CODE"] as $key=>$val)
{
	if ($val === "")
	{
		unset($arParams["PROPERTY_CODE"][$key]);
	}
}

$arParams["DETAIL_URL"] = trim($arParams["DETAIL_URL"] ?? '');
$arParams["SECTION_URL"] = trim($arParams["SECTION_URL"] ?? '');
$arParams["IBLOCK_URL"] = trim($arParams["IBLOCK_URL"] ?? '');

$arParams["PAGER_TITLE"] ??= '';
$arParams["PAGER_TEMPLATE"] ??= '';
$arParams["PAGER_SHOW_ALWAYS"] ??= false;

$arParams["NEWS_COUNT"] = (int)($arParams["NEWS_COUNT"] ?? 0);
if ($arParams["NEWS_COUNT"] <= 0)
{
	$arParams["NEWS_COUNT"] = 20;
}

$arParams["CACHE_FILTER"] = ($arParams["CACHE_FILTER"] ?? '') === "Y";
if (!$arParams["CACHE_FILTER"] && !empty($arrFilter))
{
	$arParams["CACHE_TIME"] = 0;
}

$arParams["ACTIVE_DATE_FORMAT"] = trim($arParams["ACTIVE_DATE_FORMAT"] ?? '');
if (empty($arParams["ACTIVE_DATE_FORMAT"]))
{
	$arParams["ACTIVE_DATE_FORMAT"] = $DB->DateFormatToPHP(\CSite::GetDateFormat("SHORT"));
}
$arParams["PREVIEW_TRUNCATE_LEN"] = (int)($arParams["PREVIEW_TRUNCATE_LEN"] ?? 0);
$arParams["HIDE_LINK_WHEN_NO_DETAIL"] = ($arParams["HIDE_LINK_WHEN_NO_DETAIL"] ?? '') === "Y";


if($this->startResultCache(false, array($arParams)))
{
	if(!Loader::includeModule("iblock"))
	{
		$this->abortResultCache();
		return;
	}
	if(is_numeric($arParams["IBLOCK_ID"]))
	{
		$rsIBlock = CIBlock::GetList(array(), array(
			"ACTIVE" => "Y",
			"ID" => $arParams["IBLOCK_ID"],
		));
	}
	else
	{
		$rsIBlock = CIBlock::GetList(array(), array(
			"ACTIVE" => "Y",
			"CODE" => $arParams["IBLOCK_ID"],
			"SITE_ID" => $this->getSiteId(),
		));
	}
	if($arResult = $rsIBlock->GetNext())
	{
		$arResult["USER_HAVE_ACCESS"] = false;
		//SELECT
		$arSelect = array_merge($arParams["FIELD_CODE"], array(
			"ID",
			"IBLOCK_ID",
			"IBLOCK_SECTION_ID",
			"NAME",
			"ACTIVE_FROM",
			"DETAIL_PAGE_URL",
			"DETAIL_TEXT",
			"DETAIL_TEXT_TYPE",
			"PREVIEW_TEXT",
			"PREVIEW_TEXT_TYPE",
			"PREVIEW_PICTURE",
		));
		$bGetProperty = count($arParams["PROPERTY_CODE"])>0;
		if($bGetProperty)
			$arSelect[]="PROPERTY_*";
		//WHERE
		$arFilter = array (
			"IBLOCK_ID" => $arResult["ID"],
			"IBLOCK_LID" => $this->getSiteId(),
			"ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => "N",
		);

		if($arParams["CHECK_DATES"])
			$arFilter["ACTIVE_DATE"] = "Y";

		if($arParams["SENDER_CHAIN_ID"] > 0 && Loader::includeModule('sender'))
		{
			$postingDb = \Bitrix\Sender\PostingTable::getList(array(
				'select' => array('DATE_SENT'),
				'filter' => array(
					'=MAILING_CHAIN_ID' => $arParams["SENDER_CHAIN_ID"],
					'=STATUS' => array(
						\Bitrix\Sender\PostingTable::STATUS_SENT,
						\Bitrix\Sender\PostingTable::STATUS_SENT_WITH_ERRORS
					)
				),
				'order' => array('DATE_SENT' => 'DESC'),
				'limit' => 1
			));
			if($posting = $postingDb->fetch())
			{
				if($arParams["CHECK_DATES"])
					$arFilter[">ACTIVE_FROM"] = $posting['DATE_SENT'];
				else
					$arFilter[">DATE_CREATE"] = $posting['DATE_SENT'];
			}
		}

		$arParams["PARENT_SECTION"] = CIBlockFindTools::GetSectionID(
			$arParams["PARENT_SECTION"],
			$arParams["PARENT_SECTION_CODE"],
			array(
				"GLOBAL_ACTIVE" => "Y",
				"IBLOCK_ID" => $arResult["ID"],
			)
		);

		if($arParams["PARENT_SECTION"]>0)
		{
			$arFilter["SECTION_ID"] = $arParams["PARENT_SECTION"];
			if($arParams["INCLUDE_SUBSECTIONS"])
				$arFilter["INCLUDE_SUBSECTIONS"] = "Y";

			$arResult["SECTION"]= array("PATH" => array());

			$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($arResult["ID"], $arParams["PARENT_SECTION"]);
			$arResult["IPROPERTY_VALUES"] = $ipropValues->getValues();
		}
		else
		{
			$arResult["SECTION"]= false;
		}
		//ORDER BY
		$arSort = array(
			$arParams["SORT_BY1"]=>$arParams["SORT_ORDER1"],
			$arParams["SORT_BY2"]=>$arParams["SORT_ORDER2"],
		);
		if(!array_key_exists("ID", $arSort))
			$arSort["ID"] = "DESC";

		$obParser = new CTextParser;
		$arResult["ITEMS"] = array();
		$arResult["ELEMENTS"] = array();
		$rsElement = CIBlockElement::GetList($arSort, array_merge($arFilter, $arrFilter), false, $arNavParams, $arSelect);
		if($arParams["SENDER_CHAIN_ID"] && $rsElement->SelectedRowsCount() < $arParams["NEWS_COUNT"])
		{
			if(class_exists('\Bitrix\Main\Mail\StopException'))
			{
				\Bitrix\Main\Mail\EventMessageThemeCompiler::stop();
			}
		}

		$rsElement->SetUrlTemplates($arParams["DETAIL_URL"], "", $arParams["IBLOCK_URL"]);
		$i = 0;
		while($obElement = $rsElement->GetNextElement())
		{
			if($i>=$arParams["NEWS_COUNT"]) break;
			$i++;

			$arItem = $obElement->GetFields();

			if($arParams["PREVIEW_TRUNCATE_LEN"] > 0)
				$arItem["PREVIEW_TEXT"] = $obParser->html_cut($arItem["PREVIEW_TEXT"], $arParams["PREVIEW_TRUNCATE_LEN"]);

			if($arItem["ACTIVE_FROM"] <> '')
				$arItem["DISPLAY_ACTIVE_FROM"] = CIBlockFormatProperties::DateFormat($arParams["ACTIVE_DATE_FORMAT"], MakeTimeStamp($arItem["ACTIVE_FROM"], CSite::GetDateFormat()));
			else
				$arItem["DISPLAY_ACTIVE_FROM"] = "";

			$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($arItem["IBLOCK_ID"], $arItem["ID"]);
			$arItem["IPROPERTY_VALUES"] = $ipropValues->getValues();

			Iblock\Component\Tools::getFieldImageData(
				$arItem,
				array('PREVIEW_PICTURE', 'DETAIL_PICTURE'),
				Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT,
				'IPROPERTY_VALUES'
			);

			$arItem["FIELDS"] = array();
			foreach($arParams["FIELD_CODE"] as $code)
				if(array_key_exists($code, $arItem))
					$arItem["FIELDS"][$code] = $arItem[$code];

			if($bGetProperty)
				$arItem["PROPERTIES"] = $obElement->GetProperties();
			$arItem["DISPLAY_PROPERTIES"]=array();
			foreach($arParams["PROPERTY_CODE"] as $pid)
			{
				$prop = &$arItem["PROPERTIES"][$pid];
				if(
					(is_array($prop["VALUE"]) && count($prop["VALUE"])>0)
					|| (!is_array($prop["VALUE"]) && $prop["VALUE"] <> '')
				)
				{
					$arItem["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arItem, $prop);
				}
			}

			$arResult["ITEMS"][] = $arItem;
			$arResult["ELEMENTS"][] = $arItem["ID"];
		}
		if ($bGetProperty)
		{
			\CIBlockFormatProperties::clearCache();
		}
		$arResult["NAV_STRING"] = $rsElement->GetPageNavStringEx($navComponentObject, $arParams["PAGER_TITLE"], $arParams["PAGER_TEMPLATE"], $arParams["PAGER_SHOW_ALWAYS"]);
		/** @var CBitrixComponent $navComponentObject */
		$arResult["NAV_CACHED_DATA"] = $navComponentObject->getTemplateCachedData();
		$arResult["NAV_RESULT"] = $rsElement;
		$this->setResultCacheKeys(array(
			"ID",
			"IBLOCK_TYPE_ID",
			"LIST_PAGE_URL",
			"NAV_CACHED_DATA",
			"NAME",
			"SECTION",
			"ELEMENTS",
			"IPROPERTY_VALUES",
		));

		$this->includeComponentTemplate();
	}
	else
	{
		$this->abortResultCache();
	}
}

if(isset($arResult["ID"]))
{
	$this->setTemplateCachedData($arResult["NAV_CACHED_DATA"]);

	return $arResult["ELEMENTS"];
}
