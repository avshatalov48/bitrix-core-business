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
	"LISTS_URL" => $arResult["PATH_TO_GROUP_LISTS"],
	"ADD_NAVCHAIN_LIST" => "N",
	"ADD_NAVCHAIN_SECTIONS" => "N",
	"ADD_NAVCHAIN_ELEMENT" => "N",
	),
	$component
);?>
<?$APPLICATION->IncludeComponent("bitrix:lists.list", ".default", array(
	"IBLOCK_TYPE_ID" => COption::GetOptionString("lists", "socnet_iblock_type_id"),
	"IBLOCK_ID" => $arResult["VARIABLES"]["list_id"],
	"SECTION_ID" => $arResult["VARIABLES"]["section_id"],
	"LISTS_URL" => $arResult["PATH_TO_GROUP_LISTS"],
	"LIST_EDIT_URL" => $arResult["PATH_TO_GROUP_LIST_EDIT"],
	"LIST_FIELDS_URL" => $arResult["PATH_TO_GROUP_LIST_FIELDS"],
	"LIST_URL" => $arResult["PATH_TO_GROUP_LIST_VIEW"],
	"LIST_SECTIONS_URL" => $arResult["PATH_TO_GROUP_LIST_SECTIONS"],
	"LIST_ELEMENT_URL" => $arResult["PATH_TO_GROUP_LIST_ELEMENT_EDIT"],
	"LIST_FILE_URL" => $arResult["PATH_TO_GROUP_LIST_FILE"],
	"EXPORT_EXCEL_URL" => $arResult["PATH_TO_GROUP_LIST_EXPORT_EXCEL"],
	"BIZPROC_LOG_URL" => $arResult["PATH_TO_GROUP_BIZPROC_LOG"],
	"BIZPROC_WORKFLOW_START_URL" => $arResult["PATH_TO_GROUP_BIZPROC_WORKFLOW_START"],
	"BIZPROC_WORKFLOW_ADMIN_URL" => $arResult["PATH_TO_GROUP_BIZPROC_WORKFLOW_ADMIN"],
	"BIZPROC_TASK_URL" => $arResult["PATH_TO_GROUP_BIZPROC_TASK"],
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"],
	),
	$component
);?>