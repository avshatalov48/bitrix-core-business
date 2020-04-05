<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/*************************************************************************
Processing of received parameters
 *************************************************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

//$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);

$arParams["SECTION_URL"]=trim($arParams["SECTION_URL"]);

$arResult["SECTIONS"]=array();

/*************************************************************************
Work with cache
 *************************************************************************/
if($this->StartResultCache(false, ($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups())))
{
	if(!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
		return;
	}
	$arFilter = array(
		"ACTIVE" => "Y",
		"GLOBAL_ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"CNT_ACTIVE" => "Y",
	);

	$arSelect = array();

	if(!empty($arSelect))
	{
		$arSelect[] = "ID";
		$arSelect[] = "NAME";
		$arSelect[] = "LEFT_MARGIN";
		$arSelect[] = "RIGHT_MARGIN";
		$arSelect[] = "DEPTH_LEVEL";
		$arSelect[] = "IBLOCK_ID";
		$arSelect[] = "IBLOCK_SECTION_ID";
		$arSelect[] = "LIST_PAGE_URL";
		$arSelect[] = "SECTION_PAGE_URL";
	}

	$arFilter["<="."DEPTH_LEVEL"] = 1;

	//ORDER BY
	$arSort = array(
		"left_margin"=>"asc",
	);
	//EXECUTE
	$rsSections = CIBlockSection::GetList($arSort, $arFilter, false, $arSelect);
	$rsSections->SetUrlTemplates("", $arParams["SECTION_URL"]);
	while($arSection = $rsSections->GetNext())
	{
		if(isset($arSection["PICTURE"]))
		{
			$picture = CFile::GetFileArray($arSection["PICTURE"]);

			$arFileTmp = CFile::ResizeImageGet(
				$picture,
				array("width" => 130, "height" => 130),
				BX_RESIZE_IMAGE_PROPORTIONAL,
				true
			);

			$arSection["PICTURE"] = $arFileTmp["src"];
		}
		else
			$arSection["PICTURE"] = "/bitrix/components/bitrix/eshopapp.sections/images/no_image.png";
		$arResult["SECTIONS"][]=$arSection;
	}

	//$arResult["SECTIONS_COUNT"] = count($arResult["SECTIONS"]);

	$this->SetResultCacheKeys(array(
		"SECTIONS_COUNT",
		"SECTION",
	));

	//$this->IncludeComponentTemplate();
}

$data = array();
foreach($arResult["SECTIONS"] as $arSection)
{
	$data["data"][] = array(
		"ID" => $arSection["ID"],
		"NAME" => $arSection["~NAME"],
		"URL" => $arSection["SECTION_PAGE_URL"],
		"IMAGE" => $arSection["PICTURE"]
	);
}
if (SITE_CHARSET != "utf-8")
	$data = $APPLICATION->ConvertCharsetArray($data, SITE_CHARSET, "utf-8");
echo json_encode($data);
?>