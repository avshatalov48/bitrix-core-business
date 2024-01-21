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
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

$arParams["IBLOCK_TYPE"]=trim($arParams["IBLOCK_TYPE"]);
$arParams["IBLOCK_URL"]=trim($arParams["IBLOCK_URL"]);

/*************************************************************************
			Work with cache
*************************************************************************/
$arResult["ITEMS"] = [];

if($this->StartResultCache(false, ($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups())))
{
	if(!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}
	//WHERE
	$arFilter = [
		"TYPE" => $arParams["IBLOCK_TYPE"],
		"SITE_ID" => SITE_ID,
		"ACTIVE" => "Y",
	];
	//ORDER BY
	$arSort = [
		"SORT" => "ASC",
		"NAME" => "ASC",
	];

	$rsIBlocks = CIBlock::GetList($arSort, $arFilter);

	while($arIBlock = $rsIBlocks->GetNext())
	{
		$arIBlock["PICTURE"] = CFile::GetFileArray($arIBlock["PICTURE"]);

		$arIBlock["~LIST_PAGE_URL"] = str_replace(
			array("#SERVER_NAME#", "#SITE_DIR#", "#IBLOCK_TYPE_ID#", "#IBLOCK_ID#", "#IBLOCK_CODE#", "#IBLOCK_EXTERNAL_ID#", "#CODE#"),
			array(SITE_SERVER_NAME, SITE_DIR, $arIBlock["IBLOCK_TYPE_ID"], $arIBlock["ID"], $arIBlock["CODE"], $arIBlock["EXTERNAL_ID"], $arIBlock["CODE"]),
			$arParams["IBLOCK_URL"] <> ''? trim($arParams["~IBLOCK_URL"]) : $arIBlock["~LIST_PAGE_URL"]
		);
		$arIBlock["~LIST_PAGE_URL"] = preg_replace("'/+'s", "/", $arIBlock["~LIST_PAGE_URL"]);
		$arIBlock["LIST_PAGE_URL"] = htmlspecialcharsbx($arIBlock["~LIST_PAGE_URL"]);

		$arResult["ITEMS"][]=$arIBlock;
	}
	$this->IncludeComponentTemplate();
}

if (!empty($arResult["ITEMS"]) && $USER->IsAuthorized())
{
	if($APPLICATION->GetShowIncludeAreas() && CModule::IncludeModule("iblock"))
		$this->AddIncludeAreaIcons(CIBlock::ShowPanel(0, 0, 0, $arParams["IBLOCK_TYPE"], true));
}
