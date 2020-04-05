<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$pageId = "group_content_search";
include("util_group_menu.php");

define("SONET_GROUP_NEEDED", true);
include("util_group_profile.php");
$arGroupFields = $arGroup;

if (!CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), SONET_ENTITY_GROUP, $arResult["VARIABLES"]["group_id"], "search", "view", CSocNetUser::IsCurrentUserModuleAdmin()))
{
	ShowError(GetMessage("GROUP_CONTENT_SEARCH_DISABLED"));
	return false;
}

if (
	isset($arGroupFields["NAME"])
	&& strlen(trim($arGroupFields["NAME"])) > 0
)
{
	$feature = "search";
	$arEntityActiveFeatures = CSocNetFeatures::GetActiveFeaturesNames(SONET_ENTITY_GROUP, $arResult["VARIABLES"]["group_id"]);		
	$strFeatureTitle = ((array_key_exists($feature, $arEntityActiveFeatures) && StrLen($arEntityActiveFeatures[$feature]) > 0) ? $arEntityActiveFeatures[$feature] : GetMessage("GROUP_CONTENT_SEARCH_TITLE"));

	$GLOBALS["APPLICATION"]->SetTitle($arGroupFields["NAME"].": ".$strFeatureTitle);
}
?>
<?$APPLICATION->IncludeComponent("bitrix:search.page", "tags_icons", array(
	"RESTART" => $arParams["SEARCH_RESTART"],
	"USE_LANGUAGE_GUESS" => $arParams["SEARCH_USE_LANGUAGE_GUESS"],
	"CHECK_DATES" => "N",
	"USE_TITLE_RANK" => "N",
	"FILTER_NAME" => $arParams["SEARCH_FILTER_NAME"],
	"FILTER_DATE_NAME" => $arParams["SEARCH_FILTER_DATE_NAME"],
	"arrFILTER" => array(
		0 => "socialnetwork",
	),
	"arrFILTER_socialnetwork" => array(
		0 => $arResult["VARIABLES"]["group_id"],	
	),
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
	"PAGER_TITLE" => GetMessage("GROUP_CONTENT_SEARCH_RESULTS"),
	"PAGER_SHOW_ALWAYS" => "N",
	"PAGER_TEMPLATE" => "",
	"TAGS_SORT" => "NAME",
	"TAGS_PAGE_ELEMENTS" => $arParams["SEARCH_TAGS_PAGE_ELEMENTS"],
	"TAGS_PERIOD" => $arParams["SEARCH_TAGS_PERIOD"],
	"TAGS_URL_SEARCH" => CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_GROUP_CONTENT_SEARCH"], array("group_id" => $arResult["VARIABLES"]["group_id"])),
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
	"PATH_TO_USER" => $arParams["PATH_TO_USER"],
	"PATH_TO_GROUP_BLOG" => $arResult["PATH_TO_GROUP_BLOG"],
	"PATH_TO_GROUP_FORUM" => $arResult["PATH_TO_GROUP_FORUM"],
	"PATH_TO_GROUP_FILES" => $arResult["PATH_TO_GROUP_FILES"],
	"PATH_TO_GROUP_FILES_SECTION" => $arResult["PATH_TO_GROUP_FILES"],
	"PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"],
	"PATH_TO_GROUP_TASKS_SECTION" => $arResult["PATH_TO_GROUP_TASKS"],
	"PATH_TO_GROUP_PHOTO" => $arResult["PATH_TO_GROUP_PHOTO"],
	"PATH_TO_GROUP_PHOTO_SECTION" => $arResult["PATH_TO_GROUP_PHOTO_SECTION"],
	"PATH_TO_GROUP_CALENDAR" => $arResult["PATH_TO_GROUP_CALENDAR"],
	"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"],
	"FILES_GROUP_IBLOCK_ID" => $arParams["FILES_GROUP_IBLOCK_ID"],
	"CALENDAR_GROUP_IBLOCK_ID" => $arParams["CALENDAR_GROUP_IBLOCK_ID"],
	"PHOTO_GROUP_IBLOCK_ID" => $arParams["PHOTO_GROUP_IBLOCK_ID"],	),
	$component
);?>
