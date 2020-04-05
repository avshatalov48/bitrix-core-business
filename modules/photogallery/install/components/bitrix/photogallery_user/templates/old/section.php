<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$result = $APPLICATION->IncludeComponent(
	"bitrix:photogallery.section",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"USER_ALIAS" => $arResult["MENU_VARIABLES"]["USER_ALIAS"],
		"BEHAVIOUR" => "USER",
		"PERMISSION" => $arResult["MENU_VARIABLES"]["PERMISSION"],

		"SECTIONS_TOP_URL" => $arResult["URL_TEMPLATES"]["sections_top"],
		"GALLERY_URL" => $arResult["URL_TEMPLATES"]["gallery"],
		"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["URL_TEMPLATES"]["detail_slide_show"],
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"SECTION_EDIT_URL" => $arResult["URL_TEMPLATES"]["section_edit"],
		"SECTION_EDIT_ICON_URL" => $arResult["URL_TEMPLATES"]["section_edit_icon"],
		"UPLOAD_URL" => $arResult["URL_TEMPLATES"]["upload"],

 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_SECTION"],

		"ALBUM_PHOTO_SIZE"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		"ALBUM_PHOTO_THUMBS_SIZE"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],
		"GALLERY_SIZE"	=>	$arParams["GALLERY_SIZE"],
		"RETURN_SECTION_INFO" => "Y",
		"SET_STATUS_404" => $arParams["SET_STATUS_404"],

		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"]
	),
	$component
);?><?

// SECTIONS LIST
if (!$result || intVal($result["ELEMENTS_CNT"]) <= 0):
	return false;
elseif (intVal($result["SECTIONS_CNT"]) > 0):
?><?$APPLICATION->IncludeComponent(
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

		"SECTIONS_TOP_URL" => $arResult["URL_TEMPLATES"]["sections_top"],
		"GALLERY_URL" => $arResult["URL_TEMPLATES"]["gallery"],
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"SECTION_EDIT_URL" => $arResult["URL_TEMPLATES"]["section_edit"],
		"SECTION_EDIT_ICON_URL" => $arResult["URL_TEMPLATES"]["section_edit_icon"],
		"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],
		"UPLOAD_URL" => $arResult["URL_TEMPLATES"]["upload"],

		"ALBUM_PHOTO_SIZE"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		"ALBUM_PHOTO_THUMBS_SIZE"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],

		"PAGE_ELEMENTS" => $arParams["SECTION_PAGE_ELEMENTS"],
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],

 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_SECTION"],
		"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],
		"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
		"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
		"SET_STATUS_404" => $arParams["SET_STATUS_404"],

		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"]
	),
	$component
);
?><?
endif;
if ($arParams["USE_RATING"] == "Y"):
	$arParams["PROPERTY_CODE"][] = "PROPERTY_vote_count";
	$arParams["PROPERTY_CODE"][] = "PROPERTY_vote_sum";
	$arParams["PROPERTY_CODE"][] = "PROPERTY_RATING";
endif;
if ($arParams["USE_COMMENTS"] == "Y"):
	if ($arParams["COMMENTS_TYPE"] == "FORUM")
		$arParams["PROPERTY_CODE"][] = "PROPERTY_FORUM_MESSAGE_CNT";
	elseif ($arParams["COMMENTS_TYPE"] == "BLOG")
		$arParams["PROPERTY_CODE"][] = "PROPERTY_BLOG_COMMENTS_CNT";
endif;

// DETAIL LIST
?><?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.detail.list",
	$arParams["TEMPLATE_LIST"],
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"BEHAVIOUR" => "USER",
		"USER_ALIAS" => $arResult["MENU_VARIABLES"]["USER_ALIAS"],
		"PERMISSION" => $arResult["MENU_VARIABLES"]["PERMISSION"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],

		"ELEMENTS_LAST_COUNT" => "",
		"ELEMENT_LAST_TIME" => "",
		"ELEMENTS_LAST_TIME_FROM" => "",
		"ELEMENTS_LAST_TIME_TO" => "",
		"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
		"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
		"ELEMENT_SORT_FIELD1" => "",
		"ELEMENT_SORT_ORDER1" => "",
		"ELEMENT_FILTER" => array(),
		"ELEMENT_SELECT_FIELDS" => array(),
		"PROPERTY_CODE" => $arParams["PROPERTY_CODE"],

		"GALLERY_URL"	=>	$arResult["URL_TEMPLATES"]["gallery"],
		"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"],
		"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["URL_TEMPLATES"]["detail_slide_show"],
		"SEARCH_URL"	=>	$arResult["URL_TEMPLATES"]["search"],

		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],

		"USE_DESC_PAGE" => $arParams["ELEMENTS_USE_DESC_PAGE"],
		"PAGE_ELEMENTS" => $arParams["ELEMENTS_PAGE_ELEMENTS"],
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],

 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],

		"ADDITIONAL_SIGHTS" => $arParams["~ADDITIONAL_SIGHTS"],
		"PICTURES_SIGHT" => "",
		"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],

		"SHOW_PHOTO_USER" => "N",
		"GALLERY_AVATAR_SIZE" => "0",

		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => "N",

		"CELL_COUNT"	=>	$arParams["CELL_COUNT"],
		"THUMBS_SIZE"	=>	$arParams["THUMBS_SIZE"],
		"SHOW_PAGE_NAVIGATION"	=>	"bottom",

		"SHOW_CONTROLS"	=>	"Y",
		"SHOW_RATING" => $arParams["USE_RATING"],
		"SHOW_SHOWS" => $arParams["SHOW_SHOWS"],
		"SHOW_COMMENTS" => $arParams["SHOW_COMMENTS"],
		"SHOW_TAGS" => $arParams["SHOW_TAGS"],

		"MAX_VOTE" => $arParams["MAX_VOTE"],
		"VOTE_NAMES" => $arParams["VOTE_NAMES"],
		"DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"],

		"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"]
	),
	$component
);
?>