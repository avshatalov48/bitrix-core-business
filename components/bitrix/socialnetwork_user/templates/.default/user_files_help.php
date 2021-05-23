<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent("bitrix:webdav.help", ".default", Array(
	"IBLOCK_TYPE"	=>	$arParams["FILES_USER_IBLOCK_TYPE"],
	"IBLOCK_ID"	=>	$arParams["FILES_USER_IBLOCK_ID"],
	"ROOT_SECTION_ID"	=>	$arResult["VARIABLES"]["ROOT_SECTION_ID"],
	"SECTION_ID"	=>	$arResult["VARIABLES"]["SECTION_ID"],
	"ELEMENT_ID"	=>	$arResult["VARIABLES"]["ELEMENT_ID"],
	"PERMISSION"	=>	$arResult["VARIABLES"]["PERMISSION"],
	"ACTION"	=>	$arResult["VARIABLES"]["ACTION"],
	
	"BASE_URL"	=>	$arResult["VARIABLES"]["BASE_URL"],
	
	"SET_TITLE"	=>	$arParams["SET_TITLE"],
    "SET_NAV_CHAIN" => "Y",
	"STR_TITLE" => $arResult["VARIABLES"]["STR_TITLE"],
	"DISPLAY_PANEL"	=>	$arParams["DISPLAY_PANEL"]),
	$component
);
?>
