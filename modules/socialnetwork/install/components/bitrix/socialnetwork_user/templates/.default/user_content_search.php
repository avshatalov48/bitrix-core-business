<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$pageId = "user_content_search";
include("util_menu.php");
include("util_profile.php");

if (!CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), SONET_ENTITY_USER, $arResult["VARIABLES"]["user_id"], "search", "view", CSocNetUser::IsCurrentUserModuleAdmin()))
{
	ShowError(GetMessage("USER_CONTENT_SEARCH_DISABLED"));
	return false;
}

if(
	isset($arUserFields["NAME"])
	&& strlen(trim($arUserFields["NAME"])) > 0
)
{
	$feature = "search";
	$arEntityActiveFeatures = CSocNetFeatures::GetActiveFeaturesNames(SONET_ENTITY_USER, $arResult["VARIABLES"]["user_id"]);		
	$strFeatureTitle = ((array_key_exists($feature, $arEntityActiveFeatures) && StrLen($arEntityActiveFeatures[$feature]) > 0) ? $arEntityActiveFeatures[$feature] : GetMessage("USER_CONTENT_SEARCH_TITLE"));
	
	$GLOBALS["APPLICATION"]->SetTitle($arUserFields["NAME"].": ".$strFeatureTitle);
}
?>
<?$APPLICATION->IncludeComponent("bitrix:search.page", "tags_icons_user", array(
	"RESTART" => $arParams["SEARCH_RESTART"],
	"USE_LANGUAGE_GUESS" => $arParams["SEARCH_USE_LANGUAGE_GUESS"],
	"CHECK_DATES" => "N",
	"USE_TITLE_RANK" => "N",
	"FILTER_NAME" => $arParams["SEARCH_FILTER_NAME"],
	"FILTER_DATE_NAME" => $arParams["SEARCH_FILTER_DATE_NAME"],
	"arrFILTER" => array(
		0 => "socialnetwork_user",
	),
	"arrFILTER_socialnetwork_user" => $arResult["VARIABLES"]["user_id"],
	"SHOW_WHERE" => "N",
	"arrWHERE_SONET" => array(
		0 => "forum",
		1 => "blog",
		2 => "tasks",
		3 => "photo",
		4 => "files"
	),
	"DEFAULT_SORT" => (strlen($_REQUEST["tags"]) > 0 ? "date" : $arParams["SEARCH_DEFAULT_SORT"]),
	"PAGE_RESULT_COUNT" => $arParams["SEARCH_PAGE_RESULT_COUNT"],
	"AJAX_MODE" => "N",
	"AJAX_OPTION_SHADOW" => "Y",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600",
	"PAGER_TITLE" => GetMessage("USER_CONTENT_SEARCH_RESULTS"),
	"PAGER_SHOW_ALWAYS" => "N",
	"PAGER_TEMPLATE" => "",
	"TAGS_SORT" => "NAME",
	"TAGS_PAGE_ELEMENTS" => $arParams["SEARCH_TAGS_PAGE_ELEMENTS"],
	"TAGS_PERIOD" => $arParams["SEARCH_TAGS_PERIOD"],
	"TAGS_URL_SEARCH" => CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_USER_CONTENT_SEARCH"], array("user_id" => $arResult["VARIABLES"]["user_id"])),
	"TAGS_INHERIT" => "Y",
	"FONT_MAX" => $arParams["SEARCH_TAGS_FONT_MAX"],
	"FONT_MIN" => $arParams["SEARCH_TAGS_FONT_MIN"],
	"COLOR_NEW" => $arParams["SEARCH_TAGS_COLOR_NEW"],
	"COLOR_OLD" => $arParams["SEARCH_TAGS_COLOR_OLD"],
	"PERIOD_NEW_TAGS" => "",
	"SHOW_CHAIN" => "Y",
	"COLOR_TYPE" => "Y",
	"WIDTH" => "100%",
	"AJAX_OPTION_ADDITIONAL" => "",
	"SHOW_RATING" => $arParams["SHOW_RATING"],
	"RATING_TYPE" => $arParams["RATING_TYPE"],
	"PATH_TO_USER" => $arResult["PATH_TO_USER"],
	"PATH_TO_USER_BLOG" => $arResult["PATH_TO_USER_BLOG"],
	"PATH_TO_USER_FORUM" => $arResult["PATH_TO_USER_FORUM"],
	"PATH_TO_USER_FILES" => $arResult["PATH_TO_USER_FILES"],
	"PATH_TO_USER_FILES_SECTION" => $arResult["PATH_TO_USER_FILES"],
	"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
	"PATH_TO_USER_TASKS_SECTION" => $arResult["PATH_TO_USER_TASKS"],
	"PATH_TO_USER_PHOTO" => $arResult["PATH_TO_USER_PHOTO"],
	"PATH_TO_USER_PHOTO_SECTION" => $arResult["PATH_TO_USER_PHOTO_SECTION"],
	"PATH_TO_USER_CALENDAR" => $arResult["PATH_TO_USER_CALENDAR"],
	"SOCNET_USER_ID" => $arResult["VARIABLES"]["user_id"],
	"FILES_USER_IBLOCK_ID" => $arParams["FILES_USER_IBLOCK_ID"],
	"CALENDAR_USER_IBLOCK_ID" => $arParams["CALENDAR_USER_IBLOCK_ID"],
	"PHOTO_USER_IBLOCK_ID" => $arParams["PHOTO_USER_IBLOCK_ID"],	),
	$component
);?>
