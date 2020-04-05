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
	return;

//prepare params
$arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);
if($arParams['IBLOCK_ID']<=0)
	return;

$arParams["SECTION_ID"] = intval($arParams["SECTION_ID"]);
if($arParams["SECTION_ID"]<=0)
	return;

$arParams["DETAIL_URL"] = trim($arParams["DETAIL_URL"]);

if(isset($arParams["IBLOCK_TYPE"]) && $arParams["IBLOCK_TYPE"]!='')
	$arFilter['IBLOCK_TYPE'] = $arParams["IBLOCK_TYPE"];

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

//SELECT
$arSelect = Array(
	"ID",
	"NAME",
	"IBLOCK_ID",
	"IBLOCK_SECTION_ID",
	"PREVIEW_TEXT_TYPE",
	"PREVIEW_TEXT",
	"DETAIL_TEXT_TYPE",
	"DETAIL_TEXT",
	"CREATED_BY",
);
//WHERE
$arFilter = Array(
	'IBLOCK_ID' => $arParams["IBLOCK_ID"],
	'ACTIVE' => 'Y',
	'IBLOCK_ACTIVE' => 'Y',
	'SECTION_ID' => $arParams["SECTION_ID"],
);
//ORDER BY
$arOrder = Array(
	'SORT' => 'ASC',
	'ID' => 'DESC',
);

$arAddCacheParams = array(
	"MODE" => $_REQUEST['bitrix_show_mode']?$_REQUEST['bitrix_show_mode']:'view',
	"SESS_MODE" => $_SESSION['SESS_PUBLIC_SHOW_MODE']?$_SESSION['SESS_PUBLIC_SHOW_MODE']:'view',
);

//**work body**//
if($this->StartResultCache(false, array(($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups()), $arFilter, $arAddCacheParams)))
{
	$arResult['ITEMS'] = Array();
	$arItems = CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelect);
	while($arResItems = $arItems->Fetch())
	{

		$arButtons = CIBlock::GetPanelButtons(
			$arResItems["IBLOCK_ID"],
			$arResItems["ID"],
			$arParams["SECTION_ID"],
			array("SECTION_BUTTONS"=>false, "SESSID"=>false)
		);
		$arResItems["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
		$arResItems["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];

		$arResult['ITEMS'][] = $arResItems;
		$arResult['ITEMS_ID'][] = $arResItems["ID"];
	}

	if(count($arResult['ITEMS'])<=0)
	{
		$this->AbortResultCache();
		@define("ERROR_404", "Y");
		return;
	}
	$this->EndResultCache();
}

// activation rating
CRatingsComponentsMain::GetShowRating($arParams);
if($arParams['SHOW_RATING'] == 'Y' && !empty($arResult['ITEMS_ID']))
	$arResult['RATING'] = CRatings::GetRatingVoteResult('IBLOCK_ELEMENT', $arResult['ITEMS_ID']);

//include template
$this->IncludeComponentTemplate();

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
			$arButtons = CIBlock::GetPanelButtons($arParams["IBLOCK_ID"], 0, $arParams["SECTION_ID"]);

			foreach ($arButtons as $key => $arButton)
			{
				unset($arButtons[$key]['add_section']);
				unset($arButtons[$key]['edit_section']);
				unset($arButtons[$key]['delete_section']);
			}

			if($APPLICATION->GetShowIncludeAreas())
				$this->AddIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $arButtons));
		}
	}
}
?>