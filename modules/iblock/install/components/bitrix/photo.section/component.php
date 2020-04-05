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

use Bitrix\Main\Context;
use Bitrix\Main\Type\DateTime;

CPageOption::SetOptionString("main", "nav_page_in_session", "N");

/*************************************************************************
	Processing of received parameters
*************************************************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);

$arParams["SECTION_ID"] = intval($arParams["~SECTION_ID"]);
if($arParams["SECTION_ID"] > 0 && $arParams["SECTION_ID"]."" != $arParams["~SECTION_ID"])
{
	if (CModule::IncludeModule("iblock"))
	{
		\Bitrix\Iblock\Component\Tools::process404(
			trim($arParams["MESSAGE_404"]) ?: GetMessage("PHOTO_SECTION_NOT_FOUND")
			,true
			,$arParams["SET_STATUS_404"] === "Y"
			,$arParams["SHOW_404"] === "Y"
			,$arParams["FILE_404"]
		);
	}
	return;
}

$arParams["SECTION_CODE"] = trim($arParams["SECTION_CODE"]);

$arParams["ELEMENT_SORT_FIELD"] = trim($arParams["ELEMENT_SORT_FIELD"]);
if(!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["ELEMENT_SORT_ORDER"]))
	$arParams["ELEMENT_SORT_ORDER"]="asc";

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

if(!is_array($arParams["FIELD_CODE"]))
	$arParams["FIELD_CODE"] = array();
foreach($arParams["FIELD_CODE"] as $key=>$val)
	if(!$val)
		unset($arParams["FIELD_CODE"][$key]);
if(!is_array($arParams["PROPERTY_CODE"]))
	$arParams["PROPERTY_CODE"] = array();
foreach($arParams["PROPERTY_CODE"] as $key=>$val)
	if($val==="")
		unset($arParams["PROPERTY_CODE"][$key]);

$arParams["SECTION_URL"]=trim($arParams["SECTION_URL"]);
$arParams["DETAIL_URL"]=trim($arParams["DETAIL_URL"]);

$arParams["PAGE_ELEMENT_COUNT"] = intval($arParams["PAGE_ELEMENT_COUNT"]);
if($arParams["PAGE_ELEMENT_COUNT"]<=0)
	$arParams["PAGE_ELEMENT_COUNT"]=20;
$arParams["LINE_ELEMENT_COUNT"] = intval($arParams["LINE_ELEMENT_COUNT"]);
if($arParams["LINE_ELEMENT_COUNT"]<=0)
	$arParams["LINE_ELEMENT_COUNT"]=3;

$arParams["ADD_SECTIONS_CHAIN"] = $arParams["ADD_SECTIONS_CHAIN"]!="N"; //Turn on by default
$arParams["SET_TITLE"] = $arParams["SET_TITLE"]!="N"; //Turn on by default
$arParams["SET_LAST_MODIFIED"] = $arParams["SET_LAST_MODIFIED"]==="Y";
$arParams["CACHE_FILTER"]=$arParams["CACHE_FILTER"]=="Y";
if(!$arParams["CACHE_FILTER"] && count($arrFilter)>0)
	$arParams["CACHE_TIME"] = 0;

$arParams["DISPLAY_TOP_PAGER"] = $arParams["DISPLAY_TOP_PAGER"]=="Y";
$arParams["DISPLAY_BOTTOM_PAGER"] = $arParams["DISPLAY_BOTTOM_PAGER"]!="N";
$arParams["PAGER_TITLE"] = trim($arParams["PAGER_TITLE"]);
$arParams["PAGER_SHOW_ALWAYS"] = $arParams["PAGER_SHOW_ALWAYS"]!="N";
$arParams["PAGER_TEMPLATE"] = trim($arParams["PAGER_TEMPLATE"]);
$arParams["PAGER_DESC_NUMBERING"] = $arParams["PAGER_DESC_NUMBERING"]=="Y";
$arParams["PAGER_DESC_NUMBERING_CACHE_TIME"] = intval($arParams["PAGER_DESC_NUMBERING_CACHE_TIME"]);
$arParams["PAGER_SHOW_ALL"] = $arParams["PAGER_SHOW_ALL"]!=="N";

$arNavParams = array(
	"nPageSize" => $arParams["PAGE_ELEMENT_COUNT"],
	"bDescPageNumbering" => $arParams["PAGER_DESC_NUMBERING"],
	"bShowAll" => $arParams["PAGER_SHOW_ALL"],
);
$arNavigation = CDBResult::GetNavParams($arNavParams);
if($arNavigation["PAGEN"]==0 && $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"]>0)
	$arParams["CACHE_TIME"] = $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"];

if (empty($arParams["PAGER_PARAMS_NAME"]) || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["PAGER_PARAMS_NAME"]))
{
	$pagerParameters = array();
}
else
{
	$pagerParameters = $GLOBALS[$arParams["PAGER_PARAMS_NAME"]];
	if (!is_array($pagerParameters))
		$pagerParameters = array();
}

$arParams["USE_PERMISSIONS"] = $arParams["USE_PERMISSIONS"]=="Y";
if(!is_array($arParams["GROUP_PERMISSIONS"]))
	$arParams["GROUP_PERMISSIONS"] = array(1);

$bUSER_HAVE_ACCESS = !$arParams["USE_PERMISSIONS"];
if($arParams["USE_PERMISSIONS"] && isset($GLOBALS["USER"]) && is_object($GLOBALS["USER"]))
{
	$arUserGroupArray = $USER->GetUserGroupArray();
	foreach($arParams["GROUP_PERMISSIONS"] as $PERM)
	{
		if(in_array($PERM, $arUserGroupArray))
		{
			$bUSER_HAVE_ACCESS = true;
			break;
		}
	}
}

if($this->StartResultCache(false, array($arrFilter, ($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups()), $arNavigation, $bUSER_HAVE_ACCESS, $pagerParameters)))
{
	if(!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}

	$arSelect = array();
	if(isset($arParams["SECTION_USER_FIELDS"]) && is_array($arParams["SECTION_USER_FIELDS"]))
	{
		foreach($arParams["SECTION_USER_FIELDS"] as $field)
			if(is_string($field) && preg_match("/^UF_/", $field))
				$arSelect[] = $field;
	}
	if(preg_match("/^UF_/", $arParams["META_KEYWORDS"]))
		$arSelect[] = $arParams["META_KEYWORDS"];
	if(preg_match("/^UF_/", $arParams["META_DESCRIPTION"]))
		$arSelect[] = $arParams["META_DESCRIPTION"];
	if(preg_match("/^UF_/", $arParams["BROWSER_TITLE"]))
		$arSelect[] = $arParams["BROWSER_TITLE"];

	$arFilter = array(
		"ACTIVE" => "Y",
		"GLOBAL_ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE" => "Y",
	);

	if(strlen($arParams["SECTION_CODE"]) > 0)
		$arFilter["=CODE"]=$arParams["SECTION_CODE"];
	else
		$arFilter["ID"]=$arParams["SECTION_ID"];

	$rsSection = CIBlockSection::GetList(Array(), $arFilter, false, $arSelect);
	$rsSection->SetUrlTemplates("", $arParams["SECTION_URL"]);
	$arResult = $rsSection->GetNext();

	//Check if have to show root elements
	if(!$arResult && (strlen($arParams["SECTION_CODE"]) < 1) && !$arParams["SECTION_ID"])
	{
		$arResult = array(
			"ID" => $arParams["SECTION_ID"],
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		);
	}

	if($arResult)
	{
		$arResult["PATH"] = array();
		if($arParams["ADD_SECTIONS_CHAIN"])
		{
			$rsPath = CIBlockSection::GetNavChain($arResult["IBLOCK_ID"], $arResult["ID"]);
			$rsPath->SetUrlTemplates("", $arParams["SECTION_URL"]);
			while($arPath=$rsPath->GetNext())
			{
				$arResult["PATH"][] = $arPath;
			}
		}

		$arResult["USER_HAVE_ACCESS"] = $bUSER_HAVE_ACCESS;

		$arResult["PICTURE"] = CFile::GetFileArray($arResult["PICTURE"]);
		$arResult["DETAIL_PICTURE"] = CFile::GetFileArray($arResult["DETAIL_PICTURE"]);

		//SELECT
		$arSelect = array_merge($arParams["FIELD_CODE"], array(
			"ID",
			"CODE",
			"IBLOCK_ID",
			"NAME",
			"TIMESTAMP_X",
			"PREVIEW_PICTURE",
			"DETAIL_PICTURE",
			"DETAIL_PAGE_URL",
			"PREVIEW_TEXT_TYPE",
			"DETAIL_TEXT_TYPE",
		));
		$bGetProperty = count($arParams["PROPERTY_CODE"])>0;
		if($bGetProperty)
			$arSelect[]="PROPERTY_*";
		//WHERE
		$arrFilter["SECTION_ID"] = $arResult["ID"];
		$arrFilter["INCLUDE_SUBSECTIONS"] = "Y";
		$arrFilter["ACTIVE"] = "Y";
		$arrFilter["ACTIVE_DATE"] = "Y";
		$arrFilter["CHECK_PERMISSIONS"] = "Y";
		$arrFilter["IBLOCK_ID"] = $arResult["IBLOCK_ID"];
		//ORDER BY
		$arSort = array(
			$arParams["ELEMENT_SORT_FIELD"] => $arParams["ELEMENT_SORT_ORDER"],
			"ID" => "ASC",
		);
		//EXECUTE
		$rsElements = CIBlockElement::GetList($arSort, $arrFilter, false, $arNavParams, $arSelect);
		$rsElements->SetUrlTemplates($arParams["DETAIL_URL"], $arParams["SECTION_URL"]);
		$rsElements->SetSectionContext($arResult);
		$arResult["ITEMS"] = array();
		while($obElement = $rsElements->GetNextElement())
		{
			$arItem = $obElement->GetFields();

			$arButtons = CIBlock::GetPanelButtons(
				$arItem["IBLOCK_ID"],
				$arItem["ID"],
				$arResult["ID"],
				array("SECTION_BUTTONS"=>false, "SESSID"=>false)
			);
			$arItem["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
			$arItem["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];

			if($bGetProperty)
				$arItem["PROPERTIES"] = $obElement->GetProperties();
			$arItem["DISPLAY_PROPERTIES"]=array();
			foreach($arParams["PROPERTY_CODE"] as $pid)
			{
				$prop = &$arItem["PROPERTIES"][$pid];
				if(
					(is_array($prop["VALUE"]) && count($prop["VALUE"])>0)
					|| (!is_array($prop["VALUE"]) && strlen($prop["VALUE"])>0)
				)
				{
					$arItem["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arItem, $prop, "photo_out");
				}
			}

			$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($arItem["IBLOCK_ID"], $arItem["ID"]);
			$arItem["IPROPERTY_VALUES"] = $ipropValues->getValues();

			$arItem["PREVIEW_PICTURE"] = (0 < $arItem["PREVIEW_PICTURE"] ? CFile::GetFileArray($arItem["PREVIEW_PICTURE"]) : false);
			if ($arItem["PREVIEW_PICTURE"])
			{
				$arItem["PREVIEW_PICTURE"]["ALT"] = $arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"];
				if ($arItem["PREVIEW_PICTURE"]["ALT"] == "")
					$arItem["PREVIEW_PICTURE"]["ALT"] = $arItem["NAME"];
				$arItem["PREVIEW_PICTURE"]["TITLE"] = $arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"];
				if ($arItem["PREVIEW_PICTURE"]["TITLE"] == "")
					$arItem["PREVIEW_PICTURE"]["TITLE"] = $arItem["NAME"];
			}

			$arItem["DETAIL_PICTURE"] = (0 < $arItem["DETAIL_PICTURE"] ? CFile::GetFileArray($arItem["DETAIL_PICTURE"]) : false);
			if ($arItem["DETAIL_PICTURE"])
			{
				$arItem["DETAIL_PICTURE"]["ALT"] = $arItem["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_ALT"];
				if ($arItem["DETAIL_PICTURE"]["ALT"] == "")
					$arItem["DETAIL_PICTURE"]["ALT"] = $arItem["NAME"];
				$arItem["DETAIL_PICTURE"]["TITLE"] = $arItem["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_TITLE"];
				if ($arItem["DETAIL_PICTURE"]["TITLE"] == "")
					$arItem["DETAIL_PICTURE"]["TITLE"] = $arItem["NAME"];
			}

			if(is_array($arItem["PREVIEW_PICTURE"]))
				$arItem["PICTURE"] = $arItem["PREVIEW_PICTURE"];
			elseif(is_array($arItem["DETAIL_PICTURE"]))
				$arItem["PICTURE"] = $arItem["DETAIL_PICTURE"];

			if ($arParams["SET_LAST_MODIFIED"])
			{
				$time = DateTime::createFromUserTime($arItem["TIMESTAMP_X"]);
				if (
					!isset($arResult["ITEMS_TIMESTAMP_X"])
					|| $time->getTimestamp() > $arResult["ITEMS_TIMESTAMP_X"]->getTimestamp()
				)
					$arResult["ITEMS_TIMESTAMP_X"] = $time;
			}

			$arResult["ITEMS"][]=$arItem;
		}

		$navComponentParameters = array();
		if ($arParams["PAGER_BASE_LINK_ENABLE"] === "Y")
		{
			$pagerBaseLink = trim($arParams["PAGER_BASE_LINK"]);
			if ($pagerBaseLink === "")
				$pagerBaseLink = $arResult["~SECTION_PAGE_URL"];

			if ($pagerParameters && isset($pagerParameters["BASE_LINK"]))
			{
				$pagerBaseLink = $pagerParameters["BASE_LINK"];
				unset($pagerParameters["BASE_LINK"]);
			}

			$navComponentParameters["BASE_LINK"] = CHTTP::urlAddParams($pagerBaseLink, $pagerParameters, array("encode"=>true));
		}

		$arResult["NAV_STRING"] = $rsElements->GetPageNavStringEx(
			$navComponentObject,
			$arParams["PAGER_TITLE"],
			$arParams["PAGER_TEMPLATE"],
			$arParams["PAGER_SHOW_ALWAYS"],
			$this,
			$navComponentParameters
		);
		$arResult["NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();
		$arResult["NAV_RESULT"] = $rsElements;

		$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($arResult["IBLOCK_ID"], $arResult["ID"]);
		$arResult["IPROPERTY_VALUES"] = $ipropValues->getValues();

		$this->SetResultCacheKeys(array(
			"ID",
			"IBLOCK_ID",
			"NAV_CACHED_DATA",
			$arParams["META_KEYWORDS"],
			$arParams["META_DESCRIPTION"],
			$arParams["BROWSER_TITLE"],
			"NAME",
			"PATH",
			"IPROPERTY_VALUES",
			"ITEMS_TIMESTAMP_X",
		));
		$this->IncludeComponentTemplate();
	}
	else
	{
		$this->AbortResultCache();
		\Bitrix\Iblock\Component\Tools::process404(
			trim($arParams["MESSAGE_404"]) ?: GetMessage("PHOTO_SECTION_NOT_FOUND")
			,true
			,$arParams["SET_STATUS_404"] === "Y"
			,$arParams["SHOW_404"] === "Y"
			,$arParams["FILE_404"]
		);
	}
}

if(isset($arResult["ID"]))
{
	$arTitleOptions = null;
	if($USER->IsAuthorized())
	{
		if(
			$APPLICATION->GetShowIncludeAreas()
			|| $arParams["SET_TITLE"]
			|| isset($arResult[$arParams["BROWSER_TITLE"]])
		)
		{
			if(CModule::IncludeModule("iblock"))
			{
				$url_template = CIBlock::GetArrayByID($arResult["IBLOCK_ID"], "LIST_PAGE_URL");
				$arIBlock = CIBlock::GetArrayByID($arResult["IBLOCK_ID"]);
				$arIBlock["IBLOCK_CODE"] = $arIBlock["CODE"];
				$UrlDeleteSectionButton = CIBlock::ReplaceDetailURL($url_template, $arIBlock, true, false);

				$arButtons = CIBlock::GetPanelButtons(
					$arResult["IBLOCK_ID"],
					0,
					$arResult["ID"],
					array("RETURN_URL" => array(
						"delete_section" => $UrlDeleteSectionButton,
					))
				);
				foreach($arButtons as $mode => $ar)
					unset($arButtons[$mode]["add_section"]);

				if($APPLICATION->GetShowIncludeAreas())
					$this->AddIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $arButtons));

				if($arParams["SET_TITLE"] || isset($arResult[$arParams["BROWSER_TITLE"]]))
				{
					$arTitleOptions = array(
						'ADMIN_EDIT_LINK' => $arButtons["submenu"]["edit_section"]["ACTION"],
						'PUBLIC_EDIT_LINK' => $arButtons["edit"]["edit_section"]["ACTION"],
						'COMPONENT_NAME' => $this->GetName(),
					);
				}
			}
		}
	}

	$this->SetTemplateCachedData($arResult["NAV_CACHED_DATA"]);

	if($arParams["SET_TITLE"])
	{
		if ($arResult["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"] != "")
			$APPLICATION->SetTitle($arResult["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"], $arTitleOptions);
		elseif(isset($arResult["NAME"]))
			$APPLICATION->SetTitle($arResult["NAME"], $arTitleOptions);
	}

	$browserTitle = \Bitrix\Main\Type\Collection::firstNotEmpty(
		$arResult["PROPERTIES"], array($arParams["BROWSER_TITLE"], "VALUE")
		,$arResult["IPROPERTY_VALUES"], "SECTION_META_TITLE"
	);
	if (is_array($browserTitle))
		$APPLICATION->SetPageProperty("title", implode(" ", $browserTitle), $arTitleOptions);
	elseif ($browserTitle != "")
		$APPLICATION->SetPageProperty("title", $browserTitle, $arTitleOptions);

	$metaKeywords = \Bitrix\Main\Type\Collection::firstNotEmpty(
		$arResult["PROPERTIES"], array($arParams["META_KEYWORDS"], "VALUE")
		,$arResult["IPROPERTY_VALUES"], "SECTION_META_KEYWORDS"
	);
	if (is_array($metaKeywords))
		$APPLICATION->SetPageProperty("keywords", implode(" ", $metaKeywords), $arTitleOptions);
	elseif ($metaKeywords != "")
		$APPLICATION->SetPageProperty("keywords", $metaKeywords, $arTitleOptions);

	$metaDescription = \Bitrix\Main\Type\Collection::firstNotEmpty(
		$arResult["PROPERTIES"], array($arParams["META_DESCRIPTION"], "VALUE")
		,$arResult["IPROPERTY_VALUES"], "SECTION_META_DESCRIPTION"
	);
	if (is_array($metaDescription))
		$APPLICATION->SetPageProperty("description", implode(" ", $metaDescription), $arTitleOptions);
	elseif ($metaDescription != "")
		$APPLICATION->SetPageProperty("description", $metaDescription, $arTitleOptions);

	if($arParams["ADD_SECTIONS_CHAIN"])
	{
		foreach($arResult["PATH"] as $arPath)
		{
			$APPLICATION->AddChainItem($arPath["NAME"], $arPath["~SECTION_PAGE_URL"]);
		}
	}

	if ($arParams["SET_LAST_MODIFIED"] && $arResult["ITEMS_TIMESTAMP_X"])
	{
		Context::getCurrent()->getResponse()->setLastModified($arResult["ITEMS_TIMESTAMP_X"]);
	}
}

?>
