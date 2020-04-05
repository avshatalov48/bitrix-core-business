<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$pageId = "user_photo";
include("util_menu.php");
include("util_profile.php");

if ($arParams["FATAL_ERROR"] == "Y"):
	if (!empty($arParams["ERROR_MESSAGE"])):
		ShowError($arParams["ERROR_MESSAGE"]);
	else:
		ShowNote($arParams["NOTE_MESSAGE"], "notetext-simple");
	endif;
	return false;
endif;

?>
<?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.user",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["PHOTO_USER_IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["PHOTO_USER_IBLOCK_ID"],
		"PAGE_NAME" => "INDEX",
		"USER_ALIAS" => $arResult["VARIABLES"]["GALLERY"]["CODE"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"PERMISSION" => $arResult["VARIABLES"]["PERMISSION"],

		"SORT_BY" => $arParams["PHOTO"]["ALL"]["SECTION_SORT_BY"],
		"SORT_ORD" => $arParams["PHOTO"]["ALL"]["SECTION_SORT_ORD"],

		"INDEX_URL" => $arResult["~PATH_TO_USER_PHOTO"],
		"GALLERY_URL" => $arResult["~PATH_TO_USER_PHOTO"],
		"GALLERIES_URL" => $arResult["~PATH_TO_USER_PHOTO_GALLERIES"],
		"GALLERY_EDIT_URL" => $arResult["~PATH_TO_USER_PHOTO_GALLERY_EDIT"],
		"SECTION_EDIT_URL" => $arResult["~PATH_TO_USER_PHOTO_SECTION_EDIT"],
		"SECTION_EDIT_ICON_URL" => $arResult["~PATH_TO_USER_PHOTO_SECTION_EDIT_ICON"],
		"UPLOAD_URL" => $arResult["~PATH_TO_USER_PHOTO_ELEMENT_UPLOAD"],

		"ONLY_ONE_GALLERY" => $arParams["PHOTO"]["ALL"]["ONLY_ONE_GALLERY"],
		"GALLERY_GROUPS" => $arParams["PHOTO"]["ALL"]["GALLERY_GROUPS"],
		"GALLERY_SIZE" => $arParams["PHOTO"]["ALL"]["GALLERY_SIZE"],

		"SET_NAV_CHAIN" => "N",
		"DISPLAY_ALBUM_NAME" => "N",

		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],

		"GALLERY_AVATAR_SIZE"	=>	$arParams["GALLERY_AVATAR_SIZE"],
	),
	$component,
	array("HIDE_ICONS" => "Y")
);
?><div class="social-photo-section-br">&nbsp;</div><?

$result = $APPLICATION->IncludeComponent(
	"bitrix:photogallery.section",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["PHOTO_USER_IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["PHOTO_USER_IBLOCK_ID"],
		"BEHAVIOUR" => "USER",
		"USER_ALIAS" => $arResult["VARIABLES"]["GALLERY"]["CODE"],
		"PERMISSION" => $arResult["VARIABLES"]["PERMISSION"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],

		"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["~PATH_TO_USER_PHOTO_ELEMENT_SLIDE_SHOW"],
		"GALLERY_URL" => $arResult["~PATH_TO_USER_PHOTO"],
		"SECTION_URL" => $arResult["~PATH_TO_USER_PHOTO_SECTION"],
		"SECTION_EDIT_URL" => $arResult["~PATH_TO_USER_PHOTO_SECTION_EDIT"],
		"SECTION_EDIT_ICON_URL" => $arResult["~PATH_TO_USER_PHOTO_SECTION_EDIT_ICON"],
		"SECTIONS_TOP_URL" => $arResult["~PATH_TO_USER_PHOTO"],
		"UPLOAD_URL" => $arResult["~PATH_TO_USER_PHOTO_ELEMENT_UPLOAD"],

		"DATE_TIME_FORMAT" => $arParams["PHOTO"]["ALL"]["DATE_TIME_FORMAT_SECTION"],

		"ALBUM_PHOTO_THUMBS_SIZE"	=>	$arParams["PHOTO"]["ALL"]["ALBUM_PHOTO_THUMBS_SIZE"],
		"ALBUM_PHOTO_SIZE"	=>	$arParams["PHOTO"]["ALL"]["ALBUM_PHOTO_SIZE"],
		"GALLERY_SIZE" => $arParams["PHOTO"]["ALL"]["GALLERY_SIZE"],
		"RETURN_SECTION_INFO" => "Y",
		"SET_STATUS_404" => $arParams["SET_STATUS_404"],

		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"ADD_CHAIN_ITEM" => "N",
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"DISPLAY_ALBUM_NAME" => "N"
	),
	$component,
	array("HIDE_ICONS" => "Y")
);?><?
// DETAIL LIST
if ($result && intVal($result["ELEMENTS_CNT"]) > 0)
{
	if ($arParams["PHOTO"]["ALL"]["USE_RATING"] == "Y"):
		$arParams["PHOTO"]["ALL"]["PROPERTY_CODE"][] = "PROPERTY_vote_count";
		$arParams["PHOTO"]["ALL"]["PROPERTY_CODE"][] = "PROPERTY_vote_sum";
		$arParams["PHOTO"]["ALL"]["PROPERTY_CODE"][] = "PROPERTY_RATING";
	endif;
	if ($arParams["PHOTO"]["ALL"]["USE_COMMENTS"] == "Y"):
		if ($arParams["PHOTO"]["ALL"]["COMMENTS_TYPE"] == "FORUM")
			$arParams["PHOTO"]["ALL"]["PROPERTY_CODE"][] = "PROPERTY_FORUM_MESSAGE_CNT";
		elseif ($arParams["PHOTO"]["ALL"]["COMMENTS_TYPE"] == "BLOG")
			$arParams["PHOTO"]["ALL"]["PROPERTY_CODE"][] = "PROPERTY_BLOG_COMMENTS_CNT";
	endif;

	// DETAIL LIST
	?><div class="photo-info-box photo-info-box-photo-list">
		<div class="photo-info-box-inner"><?
		$result2 = $APPLICATION->IncludeComponent(
			"bitrix:photogallery.detail.list.ex",
			"",
			Array(
				"IBLOCK_TYPE" => $arParams["PHOTO_USER_IBLOCK_TYPE"],
				"IBLOCK_ID" => $arParams["PHOTO_USER_IBLOCK_ID"],
				"BEHAVIOUR" => "USER",
				"USER_ALIAS" => $arResult["VARIABLES"]["GALLERY"]["CODE"],
				"IS_SOCNET" => "Y",
				"PERMISSION" => $arResult["VARIABLES"]["PERMISSION"],
				"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
				"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
				"INCLUDE_SUBSECTIONS" => "N", // Used to prevent displaying photos from subalbums in this section
				"ELEMENTS_LAST_COUNT" => "",
				"ELEMENT_LAST_TIME" => "",
				"ELEMENTS_LAST_TIME_FROM" => "",
				"ELEMENTS_LAST_TIME_TO" => "",
				"ELEMENT_SORT_FIELD" => $arParams["PHOTO"]["ALL"]["ELEMENT_SORT_FIELD"],
				"ELEMENT_SORT_ORDER" => $arParams["PHOTO"]["ALL"]["ELEMENT_SORT_ORDER"],
				"ELEMENT_SORT_FIELD1" => "",
				"ELEMENT_SORT_ORDER1" => "",
				"ELEMENT_FILTER" => array(),
				"ELEMENT_SELECT_FIELDS" => array(),
				"PROPERTY_CODE" => $arParams["PHOTO"]["ALL"]["PROPERTY_CODE"],
				"DETAIL_URL" => $arResult["~PATH_TO_USER_PHOTO_ELEMENT"],
				"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["~PATH_TO_USER_PHOTO_ELEMENT_SLIDE_SHOW"],
				"GALLERY_URL" => $arResult["~PATH_TO_USER_PHOTO"],
				"SEARCH_URL" => $arResult["~PATH_TO_USER_PHOTO_SEARCH"],
				"SECTION_URL" => $arResult["~PATH_TO_USER_PHOTO_SECTION"],
				"USE_PERMISSIONS" => $arParams["PHOTO"]["ALL"]["USE_PERMISSIONS"],
				"GROUP_PERMISSIONS" => $arParams["PHOTO"]["ALL"]["GROUP_PERMISSIONS"],
				"USE_DESC_PAGE" => $arParams["PHOTO"]["ALL"]["ELEMENTS_USE_DESC_PAGE"],
				"PAGE_ELEMENTS" => $arParams["PHOTO"]["ALL"]["ELEMENTS_PAGE_ELEMENTS"],
				"PAGE_NAVIGATION_TEMPLATE" => $arParams["PHOTO"]["ALL"]["PAGE_NAVIGATION_TEMPLATE"],
				"DATE_TIME_FORMAT" => $arParams["PHOTO"]["ALL"]["DATE_TIME_FORMAT_DETAIL"],
				"ADDITIONAL_SIGHTS" => $arParams["PHOTO"]["ALL"]["~ADDITIONAL_SIGHTS"],
				"PICTURES_SIGHT" => "",
				"GALLERY_SIZE" => $arParams["PHOTO"]["ALL"]["GALLERY_SIZE"],
				"SHOW_PHOTO_USER" => "Y",
				"GALLERY_AVATAR_SIZE" => $arParams["PHOTO"]["TEMPLATE"]["GALLERY_AVATAR_SIZE"],
				"CACHE_TYPE" => $arParams["CACHE_TYPE"],
				"CACHE_TIME" => $arParams["CACHE_TIME"],
				"SET_TITLE" => "N",
				"CELL_COUNT"	=>	$arParams["PHOTO"]["TEMPLATE"]["CELL_COUNT"],
				"THUMBS_SIZE"	=>	$arParams["PHOTO"]["ALL"]["THUMBS_SIZE"],
				"SHOW_PAGE_NAVIGATION"	=>	"bottom",
				"SHOW_CONTROLS"	=>	"Y",
				"SHOW_RATING" => $arParams["PHOTO"]["ALL"]["USE_RATING"],
				"SHOW_SHOWS" => $arParams["PHOTO"]["TEMPLATE"]["SHOW_SHOWS"],
				"SHOW_COMMENTS" => $arParams["PHOTO"]["ALL"]["USE_COMMENTS"],
				"SHOW_TAGS" => $arParams["PHOTO"]["ALL"]["SHOW_TAGS"],
				"COMMENTS_TYPE" => $arParams["PHOTO"]["ALL"]["COMMENTS_TYPE"],
				"USE_RATING" => $arParams["PHOTO"]["ALL"]["USE_RATING"],
				"DISPLAY_AS_RATING" => $arParams["PHOTO"]["ALL"]["DISPLAY_AS_RATING"],
				"RATING_MAIN_TYPE" => $arParams["PHOTO"]["ALL"]["RATING_MAIN_TYPE"],
				"USE_COMMENTS" => $arParams["PHOTO"]["ALL"]["USE_COMMENTS"],
				"DRAG_SORT" => "Y",
				"MORE_PHOTO_NAV" => "Y",
				"CURRENT_ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
				"CURRENT_ELEMENT_CODE" => $arResult["VARIABLES"]["ELEMENT_CODE"],
				"THUMBNAIL_SIZE"	=>	$arParams["PHOTO"]["ALL"]["THUMBNAIL_SIZE"],
				"MAX_VOTE" => $arParams["PHOTO"]["ALL"]["MAX_VOTE"],
				"VOTE_NAMES" => $arParams["PHOTO"]["ALL"]["VOTE_NAMES"],
				"COMMENTS_COUNT" => $arParams["PHOTO"]["ALL"]["COMMENTS_COUNT"],
				"PATH_TO_SMILE" => $arParams["PHOTO"]["ALL"]["PATH_TO_SMILE"],
				"FORUM_ID" => $arParams["PHOTO"]["ALL"]["FORUM_ID"],
				"USE_CAPTCHA" => $arParams["PHOTO"]["ALL"]["USE_CAPTCHA"],
				"POST_FIRST_MESSAGE" => $arParams["PHOTO"]["ALL"]["POST_FIRST_MESSAGE"],
				"PREORDER" => $arParams["PHOTO"]["ALL"]["PREORDER"],
				"SHOW_LINK_TO_FORUM" => "N",
				"BLOG_URL" => $arParams["PHOTO"]["ALL"]["BLOG_URL"],
				"PATH_TO_BLOG" => $arParams["PHOTO"]["ALL"]["PATH_TO_BLOG"],
				"PATH_TO_USER" => $arParams["PHOTO"]["ALL"]["PATH_TO_USER"],
				"NAME_TEMPLATE" => $arParams["PHOTO"]["ALL"]["NAME_TEMPLATE"],
				"SHOW_LOGIN" => $arParams["PHOTO"]["ALL"]["SHOW_LOGIN"]
			),
			$component,
			array("HIDE_ICONS" => "Y")
		);
		?></div>
	</div><?
	if (empty($result2)):
		?>
		<style>
		div.photo-page-section div.photo-info-box-photo-list {
			display: none;}
		</style>
		<?
	endif;
}
// SECTIONS LIST
if (intVal($result["SECTIONS_CNT"]) > 0)
{
	?><div class="photo-info-box photo-info-box-section-list">
		<div class="photo-info-box-inner">
			<div class="photo-header-big">
				<div class="photo-header-inner">
					<?=GetMessage("P_ALBUMS")?>
				</div>
			</div><?
			$result2 = $APPLICATION->IncludeComponent(
			"bitrix:photogallery.section.list",
			"",
			Array(
				"IBLOCK_TYPE" => $arParams["PHOTO_USER_IBLOCK_TYPE"],
				"IBLOCK_ID" => $arParams["PHOTO_USER_IBLOCK_ID"],
				"BEHAVIOUR" => "USER",
				"USER_ALIAS" => $arResult["VARIABLES"]["GALLERY"]["CODE"],
				"PERMISSION" => $arResult["VARIABLES"]["PERMISSION"],
				"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
				"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
				"SORT_BY" => $arParams["PHOTO"]["ALL"]["SECTION_SORT_BY"],
				"SORT_ORD" => $arParams["PHOTO"]["ALL"]["SECTION_SORT_ORD"],
				"DETAIL_URL" => $arResult["~PATH_TO_USER_PHOTO_ELEMENT"],
				"GALLERY_URL" => $arResult["~PATH_TO_USER_PHOTO"],
				"SECTION_URL" => $arResult["~PATH_TO_USER_PHOTO_SECTION"],
				"SECTION_EDIT_URL" => $arResult["~PATH_TO_USER_PHOTO_SECTION_EDIT"],
				"SECTION_EDIT_ICON_URL" => $arResult["~PATH_TO_USER_PHOTO_SECTION_EDIT_ICON"],
				"SECTIONS_TOP_URL" => $arResult["~PATH_TO_USER_PHOTO"],
				"UPLOAD_URL" => $arResult["~PATH_TO_USER_PHOTO_ELEMENT_UPLOAD"],
				"ALBUM_PHOTO_SIZE"	=>	$arParams["PHOTO"]["ALL"]["ALBUM_PHOTO_SIZE"],
				"ALBUM_PHOTO_THUMBS_SIZE"	=>	$arParams["PHOTO"]["ALL"]["ALBUM_PHOTO_THUMBS_SIZE"],
				"PAGE_ELEMENTS" => $arParams["PHOTO"]["ALL"]["SECTION_PAGE_ELEMENTS"],
				"PAGE_NAVIGATION_TEMPLATE" => $arParams["PHOTO"]["ALL"]["PAGE_NAVIGATION_TEMPLATE"],
				"DATE_TIME_FORMAT" => $arParams["PHOTO"]["ALL"]["DATE_TIME_FORMAT_SECTION"],
				"GALLERY_SIZE" => $arParams["PHOTO"]["ALL"]["GALLERY_SIZE"],
				"SHOW_PHOTO_USER" => $arParams["PHOTO"]["ALL"]["SHOW_PHOTO_USER"],
				"GALLERY_AVATAR_SIZE" => $arParams["PHOTO"]["ALL"]["GALLERY_AVATAR_SIZE"],
				"CACHE_TYPE" => $arParams["CACHE_TYPE"],
				"CACHE_TIME" => $arParams["CACHE_TIME"],
				"SET_TITLE" => $arParams["SET_TITLE"],
				"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
				"SHOW_TAGS" => $arParams["SHOW_TAGS"],
				"USE_COMMENTS" => $arParams["PHOTO"]["ALL"]["USE_COMMENTS"],
				"SHOW_COMMENTS" => $arParams["PHOTO"]["ALL"]["USE_COMMENTS"],
				"COMMENTS_TYPE" => $arParams["PHOTO"]["ALL"]["COMMENTS_TYPE"],
				"MAX_VOTE" => $arParams["PHOTO"]["ALL"]["MAX_VOTE"],
				"VOTE_NAMES" => $arParams["PHOTO"]["ALL"]["VOTE_NAMES"],
				"COMMENTS_COUNT" => $arParams["PHOTO"]["ALL"]["COMMENTS_COUNT"],
				"PATH_TO_SMILE" => $arParams["PHOTO"]["ALL"]["PATH_TO_SMILE"],
				"FORUM_ID" => $arParams["PHOTO"]["ALL"]["FORUM_ID"],
				"USE_CAPTCHA" => $arParams["PHOTO"]["ALL"]["USE_CAPTCHA"],
				"POST_FIRST_MESSAGE" => $arParams["PHOTO"]["ALL"]["POST_FIRST_MESSAGE"],
				"PREORDER" => $arParams["PHOTO"]["ALL"]["PREORDER"],
				"SHOW_LINK_TO_FORUM" => "N",
				"BLOG_URL" => $arParams["PHOTO"]["ALL"]["BLOG_URL"],
				"PATH_TO_BLOG" => $arParams["PHOTO"]["ALL"]["PATH_TO_BLOG"],
				"PATH_TO_USER" => $arParams["PHOTO"]["ALL"]["PATH_TO_USER"],
				"NAME_TEMPLATE" => $arParams["PHOTO"]["ALL"]["NAME_TEMPLATE"],
				"SHOW_LOGIN" => $arParams["PHOTO"]["ALL"]["SHOW_LOGIN"],
				"DISPLAY_AS_RATING" => $arParams["PHOTO"]["ALL"]["DISPLAY_AS_RATING"]
			),
			$component,
			array("HIDE_ICONS" => "Y")
		);
		?></div>
	</div><?
	if (empty($result2["SECTIONS"]))
	{
	?>
	<style>
	div.photo-page-section div.photo-info-box-section-list {
		display: none;}
	</style>
	<?
	}
}
?>