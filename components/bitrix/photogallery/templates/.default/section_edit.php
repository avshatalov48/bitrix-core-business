<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="photo-page-section-edit">
<?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.section.edit",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
 		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
 		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"BEHAVIOUR" => "SIMPLE",
		"USER_ALIAS" => "",
		"PERMISSION" => "",

		"ACTION" => $arResult["VARIABLES"]["ACTION"],

		"GALLERY_URL" => "",
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"SECTION_EDIT_URL" => $arResult["URL_TEMPLATES"]["section_edit"],
		"SECTION_EDIT_ICON_URL" => $arResult["URL_TEMPLATES"]["section_edit_icon"],
		"INDEX_URL" => $arResult["URL_TEMPLATES"]["index"],
		"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],

 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_SECTION"],
 		"SHOW_PHOTO_USER" => "N",
 		"GALLERY_AVATAR_SIZE" => "",
 		"SET_STATUS_404" => $arParams["SET_STATUS_404"],

		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"ADD_CHAIN_ITEM" => $arParams["ADD_CHAIN_ITEM"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],

		"ALBUM_PHOTO_THUMBS_WIDTH" => $arParams["ALBUM_PHOTO_THUMBS_WIDTH"],
		"THUMBNAIL_SIZE" => $arParams["THUMBNAIL_SIZE"],
		"PAGE_ELEMENTS" => $arParams["ELEMENTS_PAGE_ELEMENTS"],
		"SHOW_TAGS" => $arParams["SHOW_TAGS"],
		"USE_PHOTO_TITLE" => $arParams["USE_PHOTO_TITLE"]
	),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>
</div>