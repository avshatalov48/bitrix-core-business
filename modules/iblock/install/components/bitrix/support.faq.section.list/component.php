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


if(!CModule::IncludeModule("iblock"))
	return false;

//prepare params
$arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);
if($arParams['IBLOCK_ID']<=0)
	return false;

$arParams["SECTION_ID"] = intval($arParams["SECTION_ID"]);
$arParams["SECTION"] = intval($arParams["SECTION"]);
$arParams["SECTION_URL"] = trim($arParams["SECTION_URL"]);
$arParams["EXPAND_LIST"] = $arParams["EXPAND_LIST"]=="Y";

if(isset($arParams["IBLOCK_TYPE"]) && $arParams["IBLOCK_TYPE"]!='')
	$arFilter['IBLOCK_TYPE'] = $arParams["IBLOCK_TYPE"];

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

//WHERE
$arFilter = Array(
	'IBLOCK_ID' => $arParams['IBLOCK_ID'],
	'GLOBAL_ACTIVE' => 'Y',
	'IBLOCK_ACTIVE' => 'Y',
	'ELEMENT_SUBSECTIONS' => 'N',
	'CNT_ACTIVE' => 'Y',
);
//join filter
if($arParams['SECTION'] > 0)
	$arFilter['ID'] = $arParams['SECTION'];
elseif (!$arParams["EXPAND_LIST"])
	$arFilter["SECTION_ID"] = 0;

$arAddCacheParams = array(
	"MODE" => $_REQUEST['bitrix_show_mode']?$_REQUEST['bitrix_show_mode']:'view',
	"SESS_MODE" => $_SESSION['SESS_PUBLIC_SHOW_MODE']?$_SESSION['SESS_PUBLIC_SHOW_MODE']:'view',
);

//**work body**//
if($this->StartResultCache(false, array(($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups()), $arFilter, $arAddCacheParams)))
{
		//get info for sections
		$arSec = CIBlockSection::GetList(Array('left_margin'=>'asc'), $arFilter, true);
		while($arRes = $arSec->Fetch())
		{
			$arResult['SECTIONS'][$arRes['ID']] = $arRes;

			if($arParams['SECTION']==0) //Depth from root section
				$arResult['SECTIONS'][$arRes['ID']]['REAL_DEPTH'] = --$arRes['DEPTH_LEVEL'];
			else //Depth for subsections
			{
				$arResult['SECTIONS'][$arRes['ID']]['REAL_DEPTH'] = 0;
				$tmpParentDepth = $arRes['DEPTH_LEVEL'];
			}

			//get info from sections and subsections
			if($arParams['EXPAND_LIST'] && $arParams['SECTION']>0)
			{
				//correct filter
				unset($arFilter['ID']);
				$arFilter["LEFT_MARGIN"] = $arRes["LEFT_MARGIN"] + 1;
				$arFilter["RIGHT_MARGIN"] = $arRes["RIGHT_MARGIN"];

				$arSecInc = CIBlockSection::GetList(Array('left_margin'=>'asc'), $arFilter, true);
				while($arResInc = $arSecInc->Fetch())
				{
					if($arResInc['ID'] == $arParams['SECTION'] && !isset($arResult['CURRENT_SECTION']))
						$arResult['CURRENT_SECTION'] = $arResInc;

					$arResult['SECTIONS'][$arResInc['ID']] = $arResInc;
					$arResult['SECTIONS'][$arResInc['ID']]['REAL_DEPTH'] = $arResInc['DEPTH_LEVEL']-$tmpParentDepth;

					//detail url
					$arResult['SECTIONS'][$arResInc['ID']]['SECTION_PAGE_URL'] = htmlspecialcharsbx(str_replace(
						array("#SERVER_NAME#", "#SITE_DIR#", "#IBLOCK_ID#", "#SECTION_ID#", "#ELEMENT_ID#"),
						array(SITE_SERVER_NAME, SITE_DIR, $arParams["IBLOCK_ID"], $arResInc["ID"], ""),
						($arParams["SECTION_URL"] <> ''?$arParams["SECTION_URL"]:$arResInc["SECTION_PAGE_URL"])
					));

				}
			}
			//detail url
			$arResult['SECTIONS'][$arRes['ID']]['SECTION_PAGE_URL'] = htmlspecialcharsbx(str_replace(
				array("#SERVER_NAME#", "#SITE_DIR#", "#IBLOCK_ID#", "#SECTION_ID#", "#ELEMENT_ID#"),
				array(SITE_SERVER_NAME, SITE_DIR, $arParams["IBLOCK_ID"], $arRes["ID"], ""),
				($arParams["SECTION_URL"] <> ''?$arParams["SECTION_URL"]:$arRes["SECTION_PAGE_URL"])
			));
		}

		//no sections to display
		if(count($arResult['SECTIONS'])<=0)
		{
			$this->AbortResultCache();
			@define("ERROR_404", "Y");
			return false;
		}

	$this->SetResultCacheKeys(array(
	));
	//include template
	$this->IncludeComponentTemplate();
}

//add buttons common
if($USER->IsAuthorized())
{
	$arButtons = CIBlock::GetPanelButtons($arParams['IBLOCK_ID'], 0, $arParams['SECTION_ID']);
	if($APPLICATION->GetShowIncludeAreas())
		$this->AddIncludeAreaIcons(CIBlock::GetComponentMenu("configure", $arButtons));
	CIBlock::AddPanelButtons($APPLICATION->GetPublicShowMode(), $this->GetName(), array("intranet"=>$arButtons["intranet"]));
}
?>