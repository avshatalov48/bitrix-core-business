<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.section.edit.icon",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
 		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
 		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
 		"USER_ALIAS" => "",
 		"PERMISSION_EXTERNAL" => "",
 		"BEHAVIOUR" => "",
 		
 		"ELEMENT_SORT_FIELD" => "ID", 
 		"ELEMENT_SORT_ORDER" => "ASC", 
 		
		"SECTIONS_TOP_URL" => $arResult["URL_TEMPLATES"]["section_top"],
		"GALLERY_URL" => "",
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		
		"ALBUM_PHOTO_THUMBS_WIDTH"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],
		"ALBUM_PHOTO_WIDTH"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		
		"SET_TITLE" => $arParams["SET_TITLE"], 
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"], 
		
		"THUMBS_SIZE"	=>	80 
	),
	$component
);
?>