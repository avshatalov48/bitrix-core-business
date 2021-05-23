<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}

$USER_ID = intval($arParams["USER_ID"]);
if($USER_ID <= 0)
{
	return;
}

$headersData = $this->getCustomColumns(); // custom product table columns
$basketData = $this->getBasketItems();

$arResult = array_merge($arResult, $basketData);
$arResult["GRID"]["HEADERS"] = $headersData;


$this->IncludeComponentTemplate();
?>