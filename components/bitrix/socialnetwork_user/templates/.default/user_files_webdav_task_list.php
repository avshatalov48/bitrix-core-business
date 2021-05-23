<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!is_array($arResult["MENU_VARIABLES"])):
	return false;
endif;

$GLOBALS["APPLICATION"]->AddChainItem(GetMessage("WD_TASK"));
?><?$APPLICATION->IncludeComponent("bitrix:bizproc.task.list", "", Array(
	"MODULE_ID" => $arResult["VARIABLES"]["MODULE_ID"], 
	"ENTITY" => $arResult["VARIABLES"]["ENTITY"], 
	"DOCUMENT_TYPE" => $arResult["VARIABLES"]["DOCUMENT_TYPE"], 
	"DOCUMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
	"TASK_EDIT_URL" => $arResult["~PATH_TO_USER_FILES_WEBDAV_TASK"],
	"SET_TITLE"	=>	$arParams["SET_TITLE"]),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>