<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.section.edit",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
 		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
 		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"USER_ALIAS" => "",
		"PERMISSION" => "",
		"BEHAVIOUR" => "",
		"ACTION" => $arResult["VARIABLES"]["ACTION"],
		
		"SECTIONS_TOP_URL" => $arResult["URL_TEMPLATES"]["sections_top"],
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"GALLERY_URL" => "",
		
 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_SECTION"],
 		
		"CACHE_TIME" => $arParams["CACHE_TIME"], 
		"CACHE_TYPE" => $arParams["CACHE_TYPE"], 
		"SET_TITLE" => $arParams["SET_TITLE"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"]
	),
	$component
);
?>