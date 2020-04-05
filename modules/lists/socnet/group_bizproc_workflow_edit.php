<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?
$pageId = "group_group_lists";
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork_group/templates/.default/util_group_menu.php");
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork_group/templates/.default/util_group_profile.php");
?>
<?$APPLICATION->IncludeComponent("bitrix:lists.element.navchain", ".default", array(
	"IBLOCK_TYPE_ID" => COption::GetOptionString("lists", "socnet_iblock_type_id"),
	"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"],
	"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],

	"ADD_NAVCHAIN_GROUP" => "Y",
	"LISTS_URL" => $arResult["PATH_TO_GROUP_LISTS"],

	"IBLOCK_ID" => $arResult["VARIABLES"]["list_id"],
	"ADD_NAVCHAIN_LIST" => "Y",
	"LIST_URL" => $arResult["PATH_TO_GROUP_LIST_VIEW"],

	"ADD_NAVCHAIN_SECTIONS" => "N",
	"ADD_NAVCHAIN_ELEMENT" => "N",
	),
	$component
);?><?$APPLICATION->IncludeComponent("bitrix:bizproc.workflow.edit", ".default", array(
	"MODULE_ID" => "lists",
	"ENTITY" => 'Bitrix\Lists\BizprocDocumentLists',
	"DOCUMENT_TYPE" => "iblock_".$arResult["VARIABLES"]["list_id"],
	"ID" => $arResult['VARIABLES']['ID'],
	"EDIT_PAGE_TEMPLATE" => str_replace(
				array("#list_id#", "#group_id#"),
				array($arResult["VARIABLES"]["list_id"], $arResult["VARIABLES"]["group_id"]),
				$arResult["PATH_TO_GROUP_BIZPROC_WORKFLOW_EDIT"]
			),
	"LIST_PAGE_URL" => str_replace(
				array("#list_id#", "#group_id#"),
				array($arResult["VARIABLES"]["list_id"], $arResult["VARIABLES"]["group_id"]),
				$arResult["PATH_TO_GROUP_BIZPROC_WORKFLOW_ADMIN"]
			),
	"SHOW_TOOLBAR" => "Y",
	"SET_TITLE" => "Y",
	),
	$component,
	array("HIDE_ICONS" => "Y")
);?>