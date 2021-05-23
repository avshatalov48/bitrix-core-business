<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent("bitrix:webdav.connector", ".default", Array(
	"IBLOCK_TYPE"	=>	$arParams["FILES_USER_IBLOCK_TYPE"],
	"IBLOCK_ID"	=>	$arParams["FILES_USER_IBLOCK_ID"],
	"ROOT_SECTION_ID"	=>	$arResult["VARIABLES"]["ROOT_SECTION_ID"],
	"SECTION_ID"	=>	$arResult["VARIABLES"]["SECTION_ID"],
	"ELEMENT_ID"	=>	$arResult["VARIABLES"]["ELEMENT_ID"],
	"PERMISSION"	=>	$arResult["VARIABLES"]["PERMISSION"],
	"ACTION"	=>	$arResult["VARIABLES"]["ACTION"],
	"OBJECT"	=>	$arParams["OBJECT"],
	
	"HELP_URL"	=>	$arResult["~PATH_TO_USER_FILES_HELP"],
	
	"SET_TITLE"	=>	$arParams["SET_TITLE"],
	"STR_TITLE" => $arResult["VARIABLES"]["STR_TITLE"],
	"DISPLAY_PANEL"	=>	$arParams["DISPLAY_PANEL"]),
	$component
);
?>
