<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="photo-page-galleries">
<?
	$arCompParams = Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"USER_ID" => $arResult["VARIABLES"]["USER_ID"],
		"PERMISSION" => $arResult["MENU_VARIABLES"]["PERMISSION"],
		"SORT_BY" => $arParams["SECTION_SORT_BY"],
		"SORT_ORD" => $arParams["SECTION_SORT_ORD"],
		"INDEX_URL" => $arResult["URL_TEMPLATES"]["index"],
		"GALLERY_URL" => $arResult["URL_TEMPLATES"]["gallery"],
		"GALLERIES_URL" => $arResult["URL_TEMPLATES"]["galleries"],
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"GALLERY_EDIT_URL" => $arResult["URL_TEMPLATES"]["gallery_edit"],
		"UPLOAD_URL" => $arResult["URL_TEMPLATES"]["upload"],
		"ONLY_ONE_GALLERY" => $arParams["ONLY_ONE_GALLERY"],
		"GALLERY_GROUPS" => $arParams["GALLERY_GROUPS"],
		"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],
		"PAGE_ELEMENTS" => $arParams["SECTION_PAGE_ELEMENTS"],
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
		"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
		"SECTION_SELECT_FIELDS" => ($arResult["VARIABLES"]["USER_ID"] > 0 ? array() : array("ALBUMS")),
		"SECTION_FILTER" => ($arResult["VARIABLES"]["USER_ID"] > 0 ? array() : array(">ELEMENTS_CNT" => 0)),
		"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
		"SET_STATUS_404" => $arParams["SET_STATUS_404"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"ADD_CHAIN_ITEM" => $arParams["ADD_CHAIN_ITEM"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"SHOW_PAGE_NAVIGATION" => "bottom"
	);

	if (intVal($arResult["VARIABLES"]["USER_ID"]) > 0):?>
	<?$APPLICATION->IncludeComponent(
		"bitrix:photogallery.gallery.list",
		"",
		$arCompParams,
		$component
	);?>
<?else:?>
	<?$APPLICATION->IncludeComponent(
		"bitrix:photogallery.gallery.list",
		"modern",
		$arCompParams,
		$component
	);?>
<? endif;?>
</div>