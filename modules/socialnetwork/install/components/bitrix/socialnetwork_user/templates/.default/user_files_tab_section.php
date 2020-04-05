<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent("bitrix:webdav.section.edit", ".default", Array(
	"OBJECT"	=>	$arParams["OBJECT"], 
	"SECTION_ID"	=>	$arResult["VARIABLES"]["SECTION_ID"],
	"PERMISSION"	=>	$arResult["VARIABLES"]["PERMISSION"],
	"ACTION"	=>	$arResult["VARIABLES"]["ACTION"],
	
	"SECTIONS_URL" => $arResult["~PATH_TO_USER_FILES"],
	"SECTION_EDIT_URL" => $arResult["~PATH_TO_USER_FILES_SECTION_EDIT"],
	"USER_VIEW_URL" => $arResult["~PATH_TO_USER"],

    "FORM_ID" => $arParams["FORM_ID"],
	"TAB_ID" => "tab_section",
    "NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],

	"SET_TITLE"	=>	$arParams["SET_TITLE"],
	"STR_TITLE" => $arResult["VARIABLES"]["STR_TITLE"],
	"CACHE_TYPE"	=>	$arParams["CACHE_TYPE"],
	"CACHE_TIME"	=>	$arParams["CACHE_TIME"],
	"DISPLAY_PANEL"	=>	$arParams["DISPLAY_PANEL"]),
	$component
);?>
