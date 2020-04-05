<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);
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
);?><?$APPLICATION->IncludeComponent(
		"bitrix:main.interface.toolbar",
		"",
		array(
			"BUTTONS"=>array(
				array(
					"TEXT" => GetMessage("CT_BL_STATE_BIZPROC"),
					"TITLE" => GetMessage("CT_BL_STATE_BIZPROC_TITLE"),
					"LINK" => CHTTP::urlAddParams(str_replace(
							array("#list_id#", "#group_id#", "#ID#", "#id#"),
							array($arResult["VARIABLES"]["list_id"], $arResult["VARIABLES"]["group_id"], 0, 0),
							$arResult["PATH_TO_GROUP_BIZPROC_WORKFLOW_EDIT"]
					), array("init" => "statemachine")),
					"ICON" => "btn-new",
				),
				array(
					"TEXT" => GetMessage("CT_BL_SEQ_BIZPROC"),
					"TITLE" => GetMessage("CT_BL_SEQ_BIZPROC_TITLE"),
					"LINK" => str_replace(
							array("#list_id#", "#group_id#", "#ID#", "#id#"),
							array($arResult["VARIABLES"]["list_id"], $arResult["VARIABLES"]["group_id"], 0, 0),
							$arResult["PATH_TO_GROUP_BIZPROC_WORKFLOW_EDIT"]
					),
					"ICON" => "btn-new",
				),
				array(
					"SEPARATOR" => "Y",
				),
				array(
					"TEXT" => htmlspecialcharsbx(CIBlock::GetArrayByID($arResult["VARIABLES"]["list_id"], "ELEMENTS_NAME")),
					"TITLE" => GetMessage("CT_BL_ELEMENTS_TITLE"),
					"LINK" => str_replace(
							array("#list_id#", "#group_id#", "#section_id#"),
							array($arResult["VARIABLES"]["list_id"], $arResult["VARIABLES"]["group_id"], 0),
							$arResult["PATH_TO_GROUP_LIST_VIEW"]),
				),
			),
		),
		$component, array("HIDE_ICONS" => "Y")
);?><?$APPLICATION->IncludeComponent("bitrix:bizproc.workflow.list", ".default", Array(
	"MODULE_ID" => "lists",
	"ENTITY" => 'Bitrix\Lists\BizprocDocumentLists',
	"DOCUMENT_ID" => "iblock_".$arResult["VARIABLES"]["list_id"],
	"EDIT_URL" => str_replace(
				array("#list_id#", "#group_id#"),
				array($arResult["VARIABLES"]["list_id"], $arResult["VARIABLES"]["group_id"]),
				$arResult["PATH_TO_GROUP_BIZPROC_WORKFLOW_EDIT"]
			),
	"SET_TITLE" => "Y",
	),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>