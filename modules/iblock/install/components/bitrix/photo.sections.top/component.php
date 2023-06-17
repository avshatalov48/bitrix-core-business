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


/*************************************************************************
	Processing of received parameters
*************************************************************************/
if (!isset($arParams["CACHE_TIME"]))
{
	$arParams["CACHE_TIME"] = 36000000;
}

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"] ?? '');
$arParams["IBLOCK_ID"] = (int)($arParams["IBLOCK_ID"] ?? 0);

$arParams["SECTION_SORT_FIELD"] = trim($arParams["SECTION_SORT_FIELD"] ?? '');
$arParams["SECTION_SORT_ORDER"] = mb_strtolower($arParams["SECTION_SORT_ORDER"] ?? '');
if ($arParams["SECTION_SORT_ORDER"] !== "desc")
{
	$arParams["SECTION_SORT_ORDER"] = "asc";
}
$arParams["SECTION_COUNT"] = (int)($arParams["SECTION_COUNT"] ?? 0);
if ($arParams["SECTION_COUNT"] <= 0)
{
	$arParams["SECTION_COUNT"] = 20;
}

$arParams["ELEMENT_COUNT"] = (int)($arParams["ELEMENT_COUNT"] ?? 0);
if ($arParams["ELEMENT_COUNT"] <= 0)
{
	$arParams["ELEMENT_COUNT"] = 9;
}
$arParams["LINE_ELEMENT_COUNT"] = (int)($arParams["LINE_ELEMENT_COUNT"] ?? 0);
if ($arParams["LINE_ELEMENT_COUNT"] <= 0)
{
	$arParams["LINE_ELEMENT_COUNT"] = 3;
}
$arParams["ELEMENT_SORT_FIELD"] = trim($arParams["ELEMENT_SORT_FIELD"] ?? '');
if (
	!isset($arParams["ELEMENT_SORT_ORDER"])
	|| !preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["ELEMENT_SORT_ORDER"])
)
{
	$arParams["ELEMENT_SORT_ORDER"] = "asc";
}

$arrFilter = [];
if (!empty($arParams["FILTER_NAME"]) && preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"]))
{
	global ${$arParams["FILTER_NAME"]};
	$arrFilter = ${$arParams["FILTER_NAME"]} ?? [];
	if (!is_array($arrFilter))
	{
		$arrFilter = [];
	}
}

if (empty($arParams["FIELD_CODE"]) || !is_array($arParams["FIELD_CODE"]))
{
	$arParams["FIELD_CODE"] = [];
}
foreach ($arParams["FIELD_CODE"] as $key => $val)
{
	if ($val === "")
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

$arParams["SECTION_URL"] = trim($arParams["SECTION_URL"] ?? '');
$arParams["DETAIL_URL"] = trim($arParams["DETAIL_URL"] ?? '');

$arParams["CACHE_FILTER"] = ($arParams["CACHE_FILTER"] ?? '') === "Y";
if (!$arParams["CACHE_FILTER"] && !empty($arrFilter))
{
	$arParams["CACHE_TIME"] = 0;
}
//"hidden" parameter
$arParams["USE_RATING"] = ($arParams["USE_RATING"] ?? '') === "Y";

$arResult["SECTIONS"] = [];

$arParams["USE_PERMISSIONS"] = ($arParams["USE_PERMISSIONS"] ?? '') === "Y";
if (empty($arParams["GROUP_PERMISSIONS"]) || !is_array($arParams["GROUP_PERMISSIONS"]))
{
	$adminGroupId = 1;
	$arParams["GROUP_PERMISSIONS"] = [$adminGroupId];
}

$bUSER_HAVE_ACCESS = !$arParams["USE_PERMISSIONS"];
if ($arParams["USE_PERMISSIONS"] && isset($USER) && is_object($USER))
{
	$arUserGroupArray = $USER->GetUserGroupArray();
	foreach ($arParams["GROUP_PERMISSIONS"] as $PERM)
	{
		if (in_array($PERM, $arUserGroupArray))
		{
			$bUSER_HAVE_ACCESS = true;

			break;
		}
	}
}
$arResult["USER_HAVE_ACCESS"] = $bUSER_HAVE_ACCESS;
$arParams["CACHE_GROUPS"] ??= '';
if ($this->StartResultCache(false, array($arrFilter, ($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups()), $bUSER_HAVE_ACCESS)))
{
	if (!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));

		return;
	}
	//WHERE
	$arFilter = array(
		"ACTIVE" => "Y",
		"GLOBAL_ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE" => "Y",
	);
	//ORDER BY
	$arSort = array(
		$arParams["SECTION_SORT_FIELD"] => $arParams["SECTION_SORT_ORDER"],
		"ID" => "ASC",
	);
	//SELECT
	$arSelect = array();
	if(isset($arParams["SECTION_FIELDS"]) && is_array($arParams["SECTION_FIELDS"]))
	{
		foreach($arParams["SECTION_FIELDS"] as $field)
			if(is_string($field) && !empty($field))
				$arSelect[] = $field;
	}

	if(!empty($arSelect))
	{
		$arSelect[] = "ID";
		$arSelect[] = "IBLOCK_ID";
		$arSelect[] = "NAME";
		$arSelect[] = "LIST_PAGE_URL";
		$arSelect[] = "SECTION_PAGE_URL";
	}

	if(isset($arParams["SECTION_USER_FIELDS"]) && is_array($arParams["SECTION_USER_FIELDS"]))
	{
		foreach($arParams["SECTION_USER_FIELDS"] as $field)
			if(is_string($field) && preg_match("/^UF_/", $field))
				$arSelect[] = $field;
	}
	//EXECUTE
	$rsSections = CIBlockSection::GetList($arSort, $arFilter, false, $arSelect);
	$rsSections->SetUrlTemplates("", $arParams["SECTION_URL"]);

	//SELECT
	$arSelect = array_merge($arParams["FIELD_CODE"], array(
		"ID",
		"CODE",
		"IBLOCK_ID",
		"NAME",
		"PREVIEW_PICTURE",
		"DETAIL_PAGE_URL",
		"PREVIEW_TEXT_TYPE",
		"DETAIL_TEXT_TYPE",
	));
	$bGetProperty = $arParams["USE_RATING"] || count($arParams["PROPERTY_CODE"])>0;
	if($bGetProperty)
		$arSelect[]="PROPERTY_*";
	//WHERE
	$arrFilter["ACTIVE"] = "Y";
	$arrFilter["IBLOCK_ID"] = $arParams["IBLOCK_ID"];
	$arrFilter["ACTIVE_DATE"] = "Y";
	$arrFilter["CHECK_PERMISSIONS"] = "Y";
	//ORDER BY
	$arSort = array(
		$arParams["ELEMENT_SORT_FIELD"] => $arParams["ELEMENT_SORT_ORDER"],
		"ID" => "ASC",
	);

	while($arSection = $rsSections->GetNext())
	{
		$arButtons = CIBlock::GetPanelButtons(
			$arSection["IBLOCK_ID"],
			0,
			$arSection["ID"],
			array("SESSID"=>false)
		);
		$arSection["EDIT_LINK"] = $arButtons["edit"]["edit_section"]["ACTION_URL"] ?? '';
		$arSection["DELETE_LINK"] = $arButtons["edit"]["delete_section"]["ACTION_URL"] ?? '';
		$arSection["ADD_ELEMENT_LINK"] = $arButtons["edit"]["add_element"]["ACTION_URL"] ?? '';

		$arSection["ITEMS"] = array();

		//WHERE
		$arrFilter["SECTION_ID"] = $arSection["ID"];
		//EXECUTE
		$rsElements = CIBlockElement::GetList($arSort, $arrFilter, false, array("nTopCount"=>$arParams["ELEMENT_COUNT"]), $arSelect);
		$rsElements->SetUrlTemplates($arParams["DETAIL_URL"]);
		$rsElements->SetSectionContext($arSection);
		while($obElement = $rsElements->GetNextElement())
		{
			$arItem = $obElement->GetFields();

			$arButtons = CIBlock::GetPanelButtons(
				$arItem["IBLOCK_ID"],
				$arItem["ID"],
				$arSection["ID"],
				array("SECTION_BUTTONS"=>false, "SESSID"=>false)
			);
			$arItem["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"] ?? '';
			$arItem["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"] ?? '';

			if($bGetProperty)
				$arItem["PROPERTIES"] = $obElement->GetProperties();
			$arItem["DISPLAY_PROPERTIES"]=array();
			foreach($arParams["PROPERTY_CODE"] as $pid)
			{
				$prop = &$arItem["PROPERTIES"][$pid];
				if((
					is_array($prop["VALUE"]) && count($prop["VALUE"])>0)
					|| (!is_array($prop["VALUE"]) && $prop["VALUE"] <> '')
				)
				{
					$arItem["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arItem, $prop);
				}
			}

			\Bitrix\Iblock\InheritedProperty\ElementValues::queue($arItem["IBLOCK_ID"], $arItem["ID"]);

			$arSection["ITEMS"][]=$arItem;
		}
		$arResult["SECTIONS"][]=$arSection;
		if(count($arResult["SECTIONS"])>=$arParams["SECTION_COUNT"])
			break;
	}
	if ($bGetProperty)
	{
		\CIBlockFormatProperties::clearCache();
	}

	foreach ($arResult["SECTIONS"] as &$arSection)
	{
		foreach ($arSection["ITEMS"] as &$arItem)
		{
			$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($arItem["IBLOCK_ID"], $arItem["ID"]);
			$arItem["IPROPERTY_VALUES"] = $ipropValues->getValues();

			\Bitrix\Iblock\Component\Tools::getFieldImageData(
				$arItem,
				array('PREVIEW_PICTURE', 'DETAIL_PICTURE'),
				\Bitrix\Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT,
				'IPROPERTY_VALUES'
			);

			if(is_array($arItem["PREVIEW_PICTURE"]))
				$arItem["PICTURE"] = $arItem["PREVIEW_PICTURE"];
			elseif(is_array($arItem["DETAIL_PICTURE"]))
				$arItem["PICTURE"] = $arItem["DETAIL_PICTURE"];
		}
		unset($arItem);
	}
	unset($arSection);

	$this->SetResultCacheKeys([]);
	$this->IncludeComponentTemplate();
}

if(
	$USER->IsAuthorized()
	&& $APPLICATION->GetShowIncludeAreas()
	&& CModule::IncludeModule("iblock")
)
{
	$arButtons = CIBlock::GetPanelButtons($arParams["IBLOCK_ID"], 0, 0);
	foreach($arButtons as $mode => $ar)
		unset($arButtons[$mode]["add_element"]);

	$this->AddIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $arButtons));
}
?>
