<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="photo-page-detail-edit">
<?$ElementID = $APPLICATION->IncludeComponent(
	"bitrix:photogallery.detail.edit",
	"",
	Array(
 		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
 		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"USER_ALIAS" => $arResult["MENU_VARIABLES"]["USER_ALIAS"],
		"PERMISSION" => $arResult["MENU_VARIABLES"]["PERMISSION"],
 		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
 		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
 		"ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
 		"ELEMENT_CODE" => $arResult["VARIABLES"]["ELEMENT_CODE"],
		"BEHAVIOUR" => "USER",
 		
		"ACTION" => $arResult["VARIABLES"]["ACTION"],
		
		"GALLERY_URL" => $arResult["URL_TEMPLATES"]["gallery"],
		"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		
 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
		"ALBUM_PHOTO_THUMBS_WIDTH"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],
		"ALBUM_PHOTO_WIDTH"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		"THUMBNAIL_SIZE"	=>	$arParams["THUMBNAIL_SIZE"],
		"PREVIEW_SIZE"	=>	$arParams["PREVIEW_SIZE"],
		"SHOW_TAGS"	=>	$arParams["SHOW_TAGS"],
		"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],
		"SET_STATUS_404" => $arParams["SET_STATUS_404"], 
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
 		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
 		"SET_TITLE" => $arParams["SET_TITLE"],
		"ADD_CHAIN_ITEM" => $arParams["ADD_CHAIN_ITEM"],
		
		"SHOW_PUBLIC" => $arParams["SHOW_ONLY_PUBLIC"], 
		"SHOW_APPROVE" => $arParams["MODERATE"]
	),
	$component, 
	array("HIDE_ICONS" => "Y")
);
?>
</div>