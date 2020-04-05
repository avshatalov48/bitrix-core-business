<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $APPLICATION;

$aMenuLinksAdd = $APPLICATION->IncludeComponent(
	"bitrix:menu.sections",
	"",
	Array(
		"ID" => $_REQUEST["news"],
		"IBLOCK_TYPE" => "news",
		"IBLOCK_ID" => GetMessage("IBLOCK_INSTALL_PUBLIC_IBLOCK_ID"),
		"SECTION_URL" => "index.php?SECTION_ID=#ID#",
		"CACHE_TIME" => "3600"
	)
);

$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksAdd);
?>