<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arResult["~filter_name"] = trim($_REQUEST["filter_name"]);
$arResult["filter_name"] = htmlspecialcharsbx($arResult["~filter_name"]);

if (array_key_exists("filter_my", $_REQUEST) && $_REQUEST["filter_my"] == "Y")
	$arResult["filter_my"] = $_REQUEST["filter_my"];

if (array_key_exists("filter_archive", $_REQUEST) && $_REQUEST["filter_archive"] == "Y")
	$arResult["filter_archive"] = $_REQUEST["filter_archive"];

if (array_key_exists("filter_extranet", $_REQUEST) && $_REQUEST["filter_extranet"] == "Y")
	$arResult["filter_extranet"] = $_REQUEST["filter_extranet"];

$this->IncludeComponentTemplate();
?>