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
<?$APPLICATION->IncludeComponent("bitrix:bizproc.task", ".default", array(
	"DOCUMENT_URL" => str_replace(
		array("#list_id#", "#section_id#", "#element_id#", "#group_id#"),
		array($arResult["VARIABLES"]["list_id"], intval($arResult["VARIABLES"]["section_id"]), $arResult["VARIABLES"]["element_id"], $arResult["VARIABLES"]["group_id"]),
		$arResult["PATH_TO_GROUP_LIST_ELEMENT_EDIT"]
	),
	"TASK_ID" => $arResult["VARIABLES"]["task_id"],
	),
	$component
);?>