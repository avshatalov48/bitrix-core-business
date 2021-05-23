<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!is_array($arResult["MENU_VARIABLES"])):
	return false;
endif;
?><?$APPLICATION->IncludeComponent("bitrix:bizproc.task", "", Array(
	"MODULE_ID" => $arResult["VARIABLES"]["MODULE_ID"], 
	"ENTITY" => $arResult["VARIABLES"]["ENTITY"], 
	"DOCUMENT_TYPE" => $arResult["VARIABLES"]["DOCUMENT_TYPE"], 
	"DOCUMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
	"TASK_ID" => $arResult["VARIABLES"]["ID"], 
	"DOCUMENT_URL" => str_replace(
		array("#ELEMENT_ID#", "#ACTION#"), 
		array("#DOCUMENT_ID#", "EDIT"), $arResult["~PATH_TO_USER_FILES_ELEMENT_EDIT"]),
	"DISPLAY_PANEL"	=>	$arParams["DISPLAY_PANEL"]),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>