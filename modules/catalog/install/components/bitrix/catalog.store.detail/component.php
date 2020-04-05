<?
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CCacheManager $CACHE_MANAGER */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arParams['STORE'] = (isset($arParams['STORE']) ? (int)$arParams['STORE'] : 0);
if ($arParams['STORE'] <= 0)
{
	ShowError(GetMessage("STORE_NOT_EXIST"));
	return;
}

$arParams['MAP_TYPE'] = (int)(isset($arParams['MAP_TYPE']) ? $arParams['MAP_TYPE'] : 0);

$arParams['SET_TITLE'] = (isset($arParams['SET_TITLE']) && $arParams['SET_TITLE'] == 'Y' ? 'Y' : 'N');

if (!isset($arParams['CACHE_TIME']))
	$arParams['CACHE_TIME'] = 3600;

if ($this->startResultCache())
{
	if (!\Bitrix\Main\Loader::includeModule("catalog"))
	{
		$this->abortResultCache();
		ShowError(GetMessage("CATALOG_MODULE_NOT_INSTALL"));
		return;
	}
	$arResult['STORE'] = $arParams['STORE'];
	$arSelect = array(
		"ID",
		"TITLE",
		"ADDRESS",
		"DESCRIPTION",
		"GPS_N",
		"GPS_S",
		"IMAGE_ID",
		"PHONE",
		"SCHEDULE",
		"SITE_ID"
	);
	$storeIterator = CCatalogStore::GetList(array('ID' => 'ASC'),array('ID' => $arResult['STORE'], 'ACTIVE' => 'Y'),false,false,$arSelect);
	$arResult = $storeIterator->GetNext();
	unset($storeIterator);
	if (!$arResult)
	{
		$this->abortResultCache();
		ShowError(GetMessage("STORE_NOT_EXIST"));
		return;
	}
	$storeSite = (string)$arResult['SITE_ID'];
	if ($storeSite != '' && $storeSite != SITE_ID)
	{
		$this->abortResultCache();
		ShowError(GetMessage("STORE_NOT_EXIST"));
		return;
	}
	unset($storeSite);
	if($arResult["GPS_N"] != '' && $arResult["GPS_S"] != '')
		$this->abortResultCache();
	$arResult["MAP"] = $arParams["MAP_TYPE"];
	if(isset($arParams["PATH_TO_LISTSTORES"]))
		$arResult["LIST_URL"] = CComponentEngine::makePathFromTemplate($arParams["PATH_TO_LISTSTORES"]);
	$this->includeComponentTemplate();
}

if ($arParams["SET_TITLE"] == "Y")
{
	$title = (isset($arResult["TITLE"]) && $arResult["TITLE"] != '' ? $arResult["TITLE"]." (".$arResult["ADDRESS"].")" : $arResult["ADDRESS"]);
	$APPLICATION->SetTitle($title);
}