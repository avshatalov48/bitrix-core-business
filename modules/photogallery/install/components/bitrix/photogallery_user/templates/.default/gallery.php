<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (empty($arResult["MENU_VARIABLES"]) || empty($arResult["MENU_VARIABLES"]["ALL"]) || empty($arResult["MENU_VARIABLES"]["ALL"]["GALLERY"]))
{
?>
<div class="photo-note-box photo-note-error">
	<div class="photo-note-box-text"><?=ShowError(GetMessage("P_ERROR5"))?></div>
</div>
<?
return false;
}
?>
<div class="photo-page-gallery">
<?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.section.list",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"BEHAVIOUR" => "USER",
		"USER_ALIAS" => $arResult["MENU_VARIABLES"]["USER_ALIAS"],
		"PERMISSION" => $arResult["MENU_VARIABLES"]["PERMISSION"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"SORT_BY" => $arParams["SECTION_SORT_BY"],
		"SORT_ORD" => $arParams["SECTION_SORT_ORD"],
		"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],
		"GALLERIES_URL" => $arResult["URL_TEMPLATES"]["galleries"],
		"GALLERY_URL" => $arResult["URL_TEMPLATES"]["gallery"],
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"SECTION_EDIT_URL" => $arResult["URL_TEMPLATES"]["section_edit"],
		"SECTION_EDIT_ICON_URL" => $arResult["URL_TEMPLATES"]["section_edit_icon"],
		"UPLOAD_URL" => $arResult["URL_TEMPLATES"]["upload"],
		"PAGE_ELEMENTS" => $arParams["SECTION_PAGE_ELEMENTS"],
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_SECTION"],
		"ALBUM_PHOTO_THUMBS_SIZE"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],
		"ALBUM_PHOTO_SIZE"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		"SECTION_LIST_THUMBNAIL_SIZE" => $arParams["SECTION_LIST_THUMBNAIL_SIZE"],
		"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],
		"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
		"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
		"SET_STATUS_404" => $arParams["SET_STATUS_404"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"ADD_CHAIN_ITEM" => $arParams["ADD_CHAIN_ITEM"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"SHOW_TAGS" => $arParams["SHOW_TAGS"],
		"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
		"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
		"USE_COMMENTS" => $arParams["USE_COMMENTS"],
	),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>
</div>