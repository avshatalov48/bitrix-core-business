<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$pageId = "group_group_lists";
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork_group/templates/.default/util_group_menu.php");
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork_group/templates/.default/util_group_profile.php");
?>
<?$APPLICATION->IncludeComponent("bitrix:lists.element.navchain", ".default", array(
	"IBLOCK_TYPE_ID" => COption::GetOptionString("lists", "socnet_iblock_type_id"),
	"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"],
	"ADD_NAVCHAIN_GROUP" => "Y",
	"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],

	"IBLOCK_ID" => $arResult["VARIABLES"]["list_id"],
	"ADD_NAVCHAIN_LIST" => "Y",
	"LISTS_URL" => $arResult["PATH_TO_GROUP_LISTS"],

	"SECTION_ID" => $arResult["VARIABLES"]["section_id"],
	"ADD_NAVCHAIN_SECTIONS" => "Y",
	"LIST_URL" => $arResult["PATH_TO_GROUP_LIST_VIEW"],

	"ELEMENT_ID" => $arResult["VARIABLES"]["element_id"],
	"ADD_NAVCHAIN_ELEMENT" => "Y",
	"LIST_ELEMENT_URL" => $arResult["PATH_TO_GROUP_LIST_ELEMENT_EDIT"],
	),
	$component
);?>
<?$APPLICATION->IncludeComponent("bitrix:bizproc.log", ".default", array(
	"MODULE_ID" => "lists",
	"ENTITY" => 'Bitrix\Lists\BizprocDocumentLists',
	"COMPONENT_VERSION" => 2,
	"ID" => $arResult["VARIABLES"]["document_state_id"],
	),
	$component
);?>