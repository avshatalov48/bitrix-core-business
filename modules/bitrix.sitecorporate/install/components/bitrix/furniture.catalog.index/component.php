<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

$arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);
if (!$arParams['IBLOCK_ID'])
	return;

$arParams['IBLOCK_BINDING'] = $arParams['IBLOCK_BINDING'] == 'element' ? 'element' : 'section';

if ($this->StartResultCache(false, ($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups())))
{
	if(!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
		return;
	}
	
	$arResult["ITEMS"] = array();
	if ($arParams['IBLOCK_BINDING'] == 'element')
	{
		$arFilter = array('ACTIVE' => 'Y', 'IBLOCK_ID' => $arParams['IBLOCK_ID']);
		$dbRes = CIBlockElement::GetList(array('SORT' => 'ASC'), $arFilter);
		
		while ($arRes = $dbRes->GetNext())
		{
			$arResult['ITEMS'][] = array(
				'NAME' => $arRes['NAME'],
				'DESCRIPTION' => $arRes['DESCRIPTION'],
				'DETAIL_URL' => $arRes['DETAIL_PAGE_URL'],
				'PICTURE' => $arRes['DETAIL_PICTURE'] ? CFile::GetFileArray($arRes["DETAIL_PICTURE"]) : ($arRes['PREVIEW_PICTURE'] ? CFile::GetFileArray($arRes["PREVIEW_PICTURE"]) : array())
			);
		}
	}
	else
	{
		$arFilter = array('ACTIVE' => 'Y', 'IBLOCK_ID' => $arParams['IBLOCK_ID'], 'DEPTH_LEVEL' => 1);
		$dbRes = CIBlockSection::GetList(array('SORT' => 'ASC'), $arFilter);
		$dbRes->SetUrlTemplates();
		
		while ($arRes = $dbRes->GetNext())
		{
			$arResult['ITEMS'][] = array(
				'NAME' => $arRes['NAME'],
				'DESCRIPTION' => $arRes['DESCRIPTION'],
				'DETAIL_URL' => $arRes['SECTION_PAGE_URL'],
				'PICTURE' => CFile::GetFileArray($arRes["PICTURE"])
			);
		}
	}
	
	$this->IncludeComponentTemplate();
}

?>