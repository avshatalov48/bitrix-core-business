<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="photo-page-section-edit-icon">

<?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.section.edit.icon",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
 		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
 		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"BEHAVIOUR" => "SIMPLE",
		"USER_ALIAS" => "",
		"PERMISSION" => "",
 		
 		"ELEMENT_SORT_FIELD" => "ID", 
 		"ELEMENT_SORT_ORDER" => "ASC", 
 		
		"INDEX_URL" => $arResult["URL_TEMPLATES"]["section_top"],
		"GALLERY_URL" => $arResult["URL_TEMPLATES"]["gallery"],
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"SECTION_EDIT_ICON_URL" => $arResult["URL_TEMPLATES"]["section_edit_icon"], 
		
		"ALBUM_PHOTO_THUMBS_WIDTH"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],
		"ALBUM_PHOTO_WIDTH"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		"SET_STATUS_404"	=>	$arParams["SET_STATUS_404"],
		
		"SET_TITLE" => $arParams["SET_TITLE"], 
		"ADD_CHAIN_ITEM" => $arParams["ADD_CHAIN_ITEM"], 
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"], 
		
		"THUMBNAIL_SIZE"	=>	80
	),
	$component, 
	array("HIDE_ICONS" => "Y")
);
?>
</div>