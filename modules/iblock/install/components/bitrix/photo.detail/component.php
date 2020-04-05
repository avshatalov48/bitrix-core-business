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

/*************************************************************************
	Processing of received parameters
*************************************************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
$arParams["SECTION_ID"] = intval($arParams["SECTION_ID"]);
$arParams["SECTION_CODE"] = trim($arParams["SECTION_CODE"]);

$arParams["ELEMENT_ID"] = intval($arParams["~ELEMENT_ID"]);
if($arParams["ELEMENT_ID"] > 0 && $arParams["ELEMENT_ID"]."" != $arParams["~ELEMENT_ID"])
{
	if (CModule::IncludeModule("iblock"))
	{
		\Bitrix\Iblock\Component\Tools::process404(
			trim($arParams["MESSAGE_404"]) ?: GetMessage("PHOTO_ELEMENT_NOT_FOUND")
			,true
			,$arParams["SET_STATUS_404"] === "Y"
			,$arParams["SHOW_404"] === "Y"
			,$arParams["FILE_404"]
		);
	}
	return;
}

$arParams["ELEMENT_CODE"] = trim($arParams["ELEMENT_CODE"]);
$arParams["ELEMENT_SORT_FIELD"] = trim($arParams["ELEMENT_SORT_FIELD"]);
if(!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["ELEMENT_SORT_ORDER"]))
	$arParams["ELEMENT_SORT_ORDER"]="asc";

if(!is_array($arParams["FIELD_CODE"]))
	$arParams["FIELD_CODE"] = array();
foreach($arParams["FIELD_CODE"] as $key=>$val)
	if($val==="")
		unset($arParams["FIELD_CODE"][$key]);

if(!is_array($arParams["PROPERTY_CODE"]))
	$arParams["PROPERTY_CODE"] = array();
foreach($arParams["PROPERTY_CODE"] as $key=>$val)
	if($val==="")
		unset($arParams["PROPERTY_CODE"][$key]);

$arParams["SECTION_URL"]=trim($arParams["SECTION_URL"]);
$arParams["DETAIL_URL"]=trim($arParams["DETAIL_URL"]);

$arParams["META_KEYWORDS"]=trim($arParams["META_KEYWORDS"]);
if(strlen($arParams["META_KEYWORDS"])<=0)
	$arParams["META_KEYWORDS"] = "-";
$arParams["META_DESCRIPTION"]=trim($arParams["META_DESCRIPTION"]);
if(strlen($arParams["META_DESCRIPTION"])<=0)
	$arParams["META_DESCRIPTION"] = "-";
$arParams["BROWSER_TITLE"]=trim($arParams["BROWSER_TITLE"]);
if(strlen($arParams["BROWSER_TITLE"])<=0)
	$arParams["BROWSER_TITLE"] = "-";

$arParams["SET_TITLE"] = $arParams["SET_TITLE"]!="N"; //Turn on by default
$arParams["SET_LAST_MODIFIED"] = $arParams["SET_LAST_MODIFIED"]==="Y";

$arParams["SHOW_WORKFLOW"] = $_REQUEST["show_workflow"]=="Y";

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
if(!$bUSER_HAVE_ACCESS)
{
	ShowError(GetMessage("T_DETAIL_PERM_DEN"));
	return 0;
}

/*************************************************************************
			Start caching
*************************************************************************/

if($arParams["SHOW_WORKFLOW"] || $this->StartResultCache(false, ($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups())))
{
	if(!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}

	if($arParams["ELEMENT_ID"] > 0)
		$ELEMENT_ID = $arParams["ELEMENT_ID"];
	else//Handle case when ELEMENT_CODE used
		$ELEMENT_ID = CIBlockFindTools::GetElementID(
			$arParams["ELEMENT_ID"],
			$arParams["~ELEMENT_CODE"],
			false,
			false,
			array(
				"IBLOCK_ACTIVE" => "Y",
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"ACTIVE_DATE" => "Y",
				"ACTIVE" => "Y",
				"CHECK_PERMISSIONS" => "Y",
			)
		);

	if($ELEMENT_ID)
	{
		$WF_SHOW_HISTORY = "N";
		if ($arParams["SHOW_WORKFLOW"] && CModule::IncludeModule("workflow"))
		{
			$WF_ELEMENT_ID = CIBlockElement::WF_GetLast($ELEMENT_ID);

			$WF_STATUS_ID = CIBlockElement::WF_GetCurrentStatus($WF_ELEMENT_ID, $WF_STATUS_TITLE);
			$WF_STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($WF_STATUS_ID);

			if ($WF_STATUS_ID == 1 || $WF_STATUS_PERMISSION < 1)
				$WF_ELEMENT_ID = $ELEMENT_ID;
			else
				$WF_SHOW_HISTORY = "Y";

			$ELEMENT_ID = $WF_ELEMENT_ID;
		}
		//SELECT
		$arSelect = array_merge($arParams["FIELD_CODE"], array(
			"ID",
			"CODE",
			"IBLOCK_ID",
			"IBLOCK_SECTION_ID",
			"SECTION_PAGE_URL",
			"NAME",
			"TIMESTAMP_X",
			"DETAIL_PICTURE",
			"PREVIEW_PICTURE",
			"DETAIL_TEXT",
			"DETAIL_PAGE_URL",
			"PREVIEW_TEXT_TYPE",
			"DETAIL_TEXT_TYPE",
		));
		$bGetProperty = count($arParams["PROPERTY_CODE"]) > 0
				|| $arParams["BROWSER_TITLE"] != "-"
				|| $arParams["META_KEYWORDS"] != "-"
				|| $arParams["META_DESCRIPTION"] != "-";
		if($bGetProperty)
			$arSelect[]="PROPERTY_*";
		//WHERE
		$arFilter = array(
			"ID" => $ELEMENT_ID,
			"IBLOCK_ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"ACTIVE_DATE" => "Y",
			"ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => "Y",
			"SHOW_HISTORY" => $WF_SHOW_HISTORY,
		);
		if($arParams["SECTION_ID"])
			$arFilter["SECTION_ID"]=$arParams["SECTION_ID"];
		elseif($arParams["SECTION_CODE"])
			$arFilter["SECTION_CODE"]=$arParams["SECTION_CODE"];
		//ORDER BY
		$arSort = array(
		);
		//EXECUTE
		$rsElement = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
		$rsElement->SetUrlTemplates($arParams["DETAIL_URL"], $arParams["SECTION_URL"]);
		if($obElement = $rsElement->GetNextElement())
		{
			$arResult = $obElement->GetFields();

			if($bGetProperty)
				$arResult["PROPERTIES"] = $obElement->GetProperties();
			$arResult["DISPLAY_PROPERTIES"]=array();
			foreach($arParams["PROPERTY_CODE"] as $pid)
			{
				$prop = &$arResult["PROPERTIES"][$pid];
				if(
					(is_array($prop["VALUE"]) && count($prop["VALUE"])>0)
					|| (!is_array($prop["VALUE"]) && strlen($prop["VALUE"])>0)
				)
				{
					$arResult["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arResult, $prop, "photo_out");
				}
			}

			if($arParams["SECTION_ID"])
				$arResult["IBLOCK_SECTION_ID"]=$arParams["SECTION_ID"];
			elseif($arParams["SECTION_CODE"])
				$arResult["IBLOCK_SECTION_CODE"]=$arParams["SECTION_CODE"];

			$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($arResult["IBLOCK_ID"], $arResult["ID"]);
			$arResult["IPROPERTY_VALUES"] = $ipropValues->getValues();

			if(isset($arResult["PREVIEW_PICTURE"]))
			{
				$arResult["PREVIEW_PICTURE"] = (0 < $arResult["PREVIEW_PICTURE"] ? CFile::GetFileArray($arResult["PREVIEW_PICTURE"]) : false);
				if ($arResult["PREVIEW_PICTURE"])
				{
					$arResult["PREVIEW_PICTURE"]["ALT"] = $arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"];
					if ($arResult["PREVIEW_PICTURE"]["ALT"] == "")
						$arResult["PREVIEW_PICTURE"]["ALT"] = $arResult["NAME"];
					$arResult["PREVIEW_PICTURE"]["TITLE"] = $arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"];
					if ($arResult["PREVIEW_PICTURE"]["TITLE"] == "")
						$arResult["PREVIEW_PICTURE"]["TITLE"] = $arResult["NAME"];
				}
			}
			if(isset($arResult["DETAIL_PICTURE"]))
			{
				$arResult["DETAIL_PICTURE"] = (0 < $arResult["DETAIL_PICTURE"] ? CFile::GetFileArray($arResult["DETAIL_PICTURE"]) : false);
				if ($arResult["DETAIL_PICTURE"])
				{
					$arResult["DETAIL_PICTURE"]["ALT"] = $arResult["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_ALT"];
					if ($arResult["DETAIL_PICTURE"]["ALT"] == "")
						$arResult["DETAIL_PICTURE"]["ALT"] = $arResult["NAME"];
					$arResult["DETAIL_PICTURE"]["TITLE"] = $arResult["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_TITLE"];
					if ($arResult["DETAIL_PICTURE"]["TITLE"] == "")
						$arResult["DETAIL_PICTURE"]["TITLE"] = $arResult["NAME"];
				}
			}

			if(is_array($arResult["DETAIL_PICTURE"]))
				$arResult["PICTURE"] = $arResult["DETAIL_PICTURE"];
			elseif(is_array($arResult["PREVIEW_PICTURE"]))
				$arResult["PICTURE"] = $arResult["PREVIEW_PICTURE"];

			$arSectionFilter = array(
				"IBLOCK_ID"=>$arResult["IBLOCK_ID"],
				"ACTIVE" => "Y",
			);
			if($arParams["SECTION_ID"])
				$arSectionFilter["ID"]=$arParams["SECTION_ID"];
			elseif($arParams["SECTION_CODE"])
				$arSectionFilter["=CODE"]=$arParams["SECTION_CODE"];

			$rsSection = CIBlockSection::GetList(Array(),$arSectionFilter);
			$rsSection->SetUrlTemplates("", $arParams["SECTION_URL"]);
			if($arResult["SECTION"] = $rsSection->GetNext())
			{
				$arResult["SECTION"]["PATH"] = array();
				$rsPath = CIBlockSection::GetNavChain($arResult["SECTION"]["IBLOCK_ID"], $arResult["SECTION"]["ID"]);
				$rsPath->SetUrlTemplates("", $arParams["SECTION_URL"]);
				while($arPath=$rsPath->GetNext())
				{
					$arResult["SECTION"]["PATH"][] = $arPath;
				}
			}
		}
	}
	// get the values for the Next and Previous links
	if(isset($arResult["ID"]))
	{
		//SELECT
		$arSelect = array(
			"ID",
			"IBLOCK_ID",
			"IBLOCK_SECTION_ID",
			"DETAIL_PAGE_URL",
			"LIST_PAGE_URL",
			"NAME",
			"PREVIEW_PICTURE",
		);
		//WHERE
		$arFilter = array(
			"IBLOCK_ID" => $arResult["IBLOCK_ID"],
			"SECTION_ID" => $arResult["SECTION"]["ID"],
			"ACTIVE_DATE" => "Y",
			"ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => "Y",
		);
		//ORDER BY
		$arSort = array(
			$arParams["ELEMENT_SORT_FIELD"] => $arParams["ELEMENT_SORT_ORDER"],
			"ID" => "ASC",
		);
		//EXECUTE
		$arResult["NEXT"] = array();
		$arResult["PREV"] = array();
		$rsElement = CIBlockElement::GetList($arSort, $arFilter, false, array("nElementID" => $arResult["ID"], "nPageSize" => 2), $arSelect);
		$rsElement->SetUrlTemplates($arParams["DETAIL_URL"], $arParams["SECTION_URL"]);
		$rsElement->SetSectionContext($arResult["SECTION"]);
		$end = false;

		while($arElement = $rsElement->GetNext())
		{
			if($arElement["ID"]==$arResult["ID"])
			{
				$end = true;
				$arResult["CURRENT"]["NO"] = $arElement["RANK"];
			}
			elseif($end)
			{
				$arResult["NEXT"][] = $arElement;
			}
			else
			{
				array_unshift($arResult["PREV"], $arElement);
			}
		}

		$arResult["CURRENT"]["COUNT"] = CIBlockElement::GetList(array(), $arFilter, array());

		foreach (array("NEXT", "PREV") as $type)
		{
			foreach($arResult[$type] as $key=>$value)
			{
				$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($value["IBLOCK_ID"], $value["ID"]);
				$IPROPERTY_VALUES = $ipropValues->getValues();

				$arResult[$type][$key]["PICTURE"] = CFile::GetFileArray($value["PREVIEW_PICTURE"]);
				if ($arResult[$type][$key]["PICTURE"])
				{
					$arResult[$type][$key]["PICTURE"]["ALT"] = $IPROPERTY_VALUES["ELEMENT_PREVIEW_PICTURE_FILE_ALT"];
					if ($arResult[$type][$key]["PICTURE"]["ALT"] == "")
						$arResult[$type][$key]["PICTURE"]["ALT"] = $value["NAME"];
					$arResult[$type][$key]["PICTURE"]["TITLE"] = $IPROPERTY_VALUES["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"];
					if ($arResult[$type][$key]["PICTURE"]["TITLE"] == "")
						$arResult[$type][$key]["PICTURE"]["TITLE"] = $value["NAME"];
				}
			}
		}

		$this->SetResultCacheKeys(array(
			"ID",
			"IBLOCK_ID",
			"IBLOCK_SECTION_ID",
			"LIST_PAGE_URL",
			"PROPERTIES",
			"NAME",
			"SECTION",
			"IPROPERTY_VALUES",
			"TIMESTAMP_X",
		));
		$this->IncludeComponentTemplate();
	}
	else
	{
		$this->AbortResultCache();
		\Bitrix\Iblock\Component\Tools::process404(
			trim($arParams["MESSAGE_404"]) ?: GetMessage("PHOTO_ELEMENT_NOT_FOUND")
			,true
			,$arParams["SET_STATUS_404"] === "Y"
			,$arParams["SHOW_404"] === "Y"
			,$arParams["FILE_404"]
		);
	}
}

/*************************************************************************
	Any actions without cache (if there was some to display)
*************************************************************************/
if(isset($arResult["ID"]))
{
	$arTitleOptions = null;
	if(CModule::IncludeModule("iblock"))
	{
		CIBlockElement::CounterInc($arResult["ID"]);

		if($USER->IsAuthorized())
		{
			if($APPLICATION->GetShowIncludeAreas()
				|| $arParams["SET_TITLE"]
				|| isset($arResult[$arParams["BROWSER_TITLE"]])
			)
			{
				$arReturnUrl = array(
					"add_element" => CIBlock::GetArrayByID($arResult["IBLOCK_ID"], "DETAIL_PAGE_URL"),
					"delete_element" => (
						isset($arResult["SECTION"])?
						$arResult["SECTION"]["SECTION_PAGE_URL"]:
						$arResult["LIST_PAGE_URL"]
					),
				);
				$arButtons = CIBlock::GetPanelButtons(
					$arResult["IBLOCK_ID"],
					$arResult["ID"],
					$arResult["IBLOCK_SECTION_ID"],
					Array("RETURN_URL" =>  $arReturnUrl)
				);

				if($APPLICATION->GetShowIncludeAreas())
					$this->AddIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $arButtons));

				if($arParams["SET_TITLE"] || isset($arResult[$arParams["BROWSER_TITLE"]]))
				{
					$arTitleOptions = array(
						'ADMIN_EDIT_LINK' => $arButtons["submenu"]["edit_element"]["ACTION"],
						'PUBLIC_EDIT_LINK' => $arButtons["edit"]["edit_element"]["ACTION"],
						'COMPONENT_NAME' => $this->GetName(),
					);
				}
			}
		}
	}

	if($arParams["SET_TITLE"])
	{
		if ($arResult["IPROPERTY_VALUES"]["ELEMENT_PAGE_TITLE"] != "")
			$APPLICATION->SetTitle($arResult["IPROPERTY_VALUES"]["ELEMENT_PAGE_TITLE"], $arTitleOptions);
		else
			$APPLICATION->SetTitle($arResult["NAME"], $arTitleOptions);
	}

	$browserTitle = \Bitrix\Main\Type\Collection::firstNotEmpty(
		$arResult["PROPERTIES"], array($arParams["BROWSER_TITLE"], "VALUE")
		,$arResult, $arParams["BROWSER_TITLE"]
		,$arResult["IPROPERTY_VALUES"], "ELEMENT_META_TITLE"
	);
	if (is_array($browserTitle))
		$APPLICATION->SetPageProperty("title", implode(" ", $browserTitle), $arTitleOptions);
	elseif ($browserTitle != "")
		$APPLICATION->SetPageProperty("title", $browserTitle, $arTitleOptions);

	$metaKeywords = \Bitrix\Main\Type\Collection::firstNotEmpty(
		$arResult["PROPERTIES"], array($arParams["META_KEYWORDS"], "VALUE")
		,$arResult["IPROPERTY_VALUES"], "ELEMENT_META_KEYWORDS"
	);
	if (is_array($metaKeywords))
		$APPLICATION->SetPageProperty("keywords", implode(" ", $metaKeywords), $arTitleOptions);
	elseif ($metaKeywords != "")
		$APPLICATION->SetPageProperty("keywords", $metaKeywords, $arTitleOptions);

	$metaDescription = \Bitrix\Main\Type\Collection::firstNotEmpty(
		$arResult["PROPERTIES"], array($arParams["META_DESCRIPTION"], "VALUE")
		,$arResult["IPROPERTY_VALUES"], "ELEMENT_META_DESCRIPTION"
	);
	if (is_array($metaDescription))
		$APPLICATION->SetPageProperty("description", implode(" ", $metaDescription), $arTitleOptions);
	elseif ($metaDescription != "")
		$APPLICATION->SetPageProperty("description", $metaDescription, $arTitleOptions);

	if(is_array($arResult["SECTION"]))
	{
		foreach($arResult["SECTION"]["PATH"] as $arPath)
		{
			$APPLICATION->AddChainItem($arPath["NAME"], $arPath["~SECTION_PAGE_URL"]);
		}
	}

	if ($arParams["SET_LAST_MODIFIED"] && $arResult["TIMESTAMP_X"])
	{
		Context::getCurrent()->getResponse()->setLastModified(DateTime::createFromUserTime($arResult["TIMESTAMP_X"]));
	}

	return $arResult["ID"];
}
?>
