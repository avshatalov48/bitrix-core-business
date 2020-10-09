<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="photo-page-section">
<?$result = $APPLICATION->IncludeComponent(
	"bitrix:photogallery.section",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"USER_ALIAS" => "",
		"BEHAVIOUR" => "SIMPLE",
		"PERMISSION" => "",
		"GALLERY_URL" => "",
		"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["URL_TEMPLATES"]["detail_slide_show"],
		"INDEX_URL" => $arResult["URL_TEMPLATES"]["index"],
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"SECTION_EDIT_URL" => $arResult["URL_TEMPLATES"]["section_edit"],
		"SECTION_EDIT_ICON_URL" => $arResult["URL_TEMPLATES"]["section_edit_icon"],
		"UPLOAD_URL" => $arResult["URL_TEMPLATES"]["upload"],
		"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],
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
	$component,
	array("HIDE_ICONS" => "Y")
);?><?
if ($result && intval($result["ELEMENTS_CNT"]) > 0)
{
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
?>
<div class="photo-info-box photo-info-box-photo-list">
	<div class="photo-info-box-inner">
<?
$result2 = $APPLICATION->IncludeComponent(
	"bitrix:photogallery.detail.list.ex",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"BEHAVIOUR" => "SIMPLE",
		"USER_ALIAS" => "",
		"PERMISSION" => "",
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"INCLUDE_SUBSECTIONS" => "N", // Used to prevent displaying photos from subalbums in this section
		"CURRENT_ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
		"CURRENT_ELEMENT_CODE" => $arResult["VARIABLES"]["ELEMENT_CODE"],
		"DRAG_SORT" => $arParams["DRAG_SORT"],
		"MORE_PHOTO_NAV" => "Y",

		"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
		"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
		"ELEMENT_SORT_FIELD1" => "",
		"ELEMENT_SORT_ORDER1" => "",
		"ELEMENT_FILTER" => array(),
		"ELEMENT_SELECT_FIELDS" => array(),
		"PROPERTY_CODE" => $arParams["PROPERTY_CODE"],

		"GALLERY_URL" =>	"",
		"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"],
		"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["URL_TEMPLATES"]["detail_slide_show"],
		"SEARCH_URL"	=>	$arResult["URL_TEMPLATES"]["search"],
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
		"USE_DESC_PAGE" => $arParams["ELEMENTS_USE_DESC_PAGE"],
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"PAGE_ELEMENTS" => $arParams["ELEMENTS_PAGE_ELEMENTS"],
		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
		"ADDITIONAL_SIGHTS" => $arParams["~ADDITIONAL_SIGHTS"],
		"PICTURES_SIGHT"	=>	"real",
		"GALLERY_SIZE" => 0,
		"SHOW_PHOTO_USER" => "N",
		"GALLERY_AVATAR_SIZE" => "0",
		"RETURN_FORMAT" => "LIST",
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => "N",
		"CELL_COUNT" => $arParams["CELL_COUNT"],
		"THUMBNAIL_SIZE" => $arParams["THUMBNAIL_SIZE"],
		"SHOW_COMMENTS" => "Y",
		"SHOW_CONTROLS"	=>	"Y",
		"SHOW_RATING" => $arParams["USE_RATING"],
		"SHOW_SHOWS" => $arParams["SHOW_SHOWS"],
		"SHOW_TAGS" => $arParams["SHOW_TAGS"],
		"SHOW_DATE" => $arParams["SHOW_DATE"],
		"SHOW_DESRIPTION" => $arParams["SHOW_DESRIPTION"],
		"USE_RATING" => $arParams["USE_RATING"],
		"MAX_VOTE" => $arParams["MAX_VOTE"],
		"VOTE_NAMES" => $arParams["VOTE_NAMES"],
		"DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"],
		"RATING_MAIN_TYPE" => $arParams["RATING_MAIN_TYPE"],
		"USE_COMMENTS" => $arParams["USE_COMMENTS"],
		"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"],
		"COMMENTS_COUNT" => $arParams["COMMENTS_COUNT"],
		"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
		"FORUM_ID" => $arParams["FORUM_ID"],
		"USE_CAPTCHA" => $arParams["USE_CAPTCHA"],
		"URL_TEMPLATES_READ" => $arParams["URL_TEMPLATES_READ"],
		"URL_TEMPLATES_PROFILE_VIEW" => $arParams["URL_TEMPLATES_PROFILE_VIEW"],
		"POST_FIRST_MESSAGE" => $arParams["POST_FIRST_MESSAGE"],
		"PREORDER" => $arParams["PREORDER"],
		"SHOW_LINK_TO_FORUM" => $arParams["SHOW_LINK_TO_FORUM"] == "Y" ? "Y" : "N",
		"BLOG_URL" => $arParams["BLOG_URL"],
		"PATH_TO_BLOG" => $arParams["PATH_TO_BLOG"],
		"PATH_TO_USER" => $arParams["PATH_TO_USER"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"]
	),
	$component,
	array("HIDE_ICONS" => "Y")
);?>
	</div>
</div>

<?if(empty($result2)):?>
<style>
div.photo-page-section div.photo-info-box-photo-list {display: none;}
</style>
<? endif;}

// SECTIONS LIST
if (intval($result["SECTIONS_CNT"]) > 0)
{
?>
<div class="photo-info-box photo-info-box-section-list">
	<div class="photo-info-box-inner">
		<div class="photo-header-big">
			<div class="photo-header-inner">
				<?= GetMessage("P_ALBUMS")?>
			</div>
		</div>
<?$result2 = $APPLICATION->IncludeComponent(
	"bitrix:photogallery.section.list",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"BEHAVIOUR" => "SIMPLE",
		"USER_ALIAS" => "",
		"PERMISSION" => "",
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],

		"SORT_BY" => $arParams["SECTION_SORT_BY"],
		"SORT_ORD" => $arParams["SECTION_SORT_ORD"],

		"INDEX_URL" => $arResult["URL_TEMPLATES"]["index"],
		"GALLERY_URL" => "",
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"SECTION_EDIT_URL" => $arResult["URL_TEMPLATES"]["section_edit"],
		"SECTION_EDIT_ICON_URL" => $arResult["URL_TEMPLATES"]["section_edit_icon"],
		"UPLOAD_URL" => $arResult["URL_TEMPLATES"]["upload"],
		"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],

		"ALBUM_PHOTO_THUMBS_SIZE"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],
		"ALBUM_PHOTO_SIZE"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		"SECTION_LIST_THUMBNAIL_SIZE" => $arParams["SECTION_LIST_THUMBNAIL_SIZE"],

		"PAGE_ELEMENTS" => $arParams["SECTION_PAGE_ELEMENTS"],
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],

		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_SECTION"],
		"SET_STATUS_404" => $arParams["SET_STATUS_404"],

		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => "N",
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
		"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
	),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>
	</div>
</div>
<?
if (empty($result2["SECTIONS"]))
{
?>
<style>
div.photo-page-section div.photo-info-box-section-list{display: none;}
</style>
<?
}
}
?>
</div>