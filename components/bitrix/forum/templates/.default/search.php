<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(IsModuleInstalled("search") && $arParams["SHOW_TAGS"] == "Y")
{
	$arFilter = is_array($arParams["FID"]) ? $arParams["FID"] : array();
	$res = array();
	if (!empty($_REQUEST["FORUM_ID"]))
		$res = (is_array($_REQUEST["FORUM_ID"]) ? $_REQUEST["FORUM_ID"] : array($_REQUEST["FORUM_ID"]));
	if (empty($arFilter) && !empty($res))
		$arFilter = $res;
	elseif (!empty($arFilter) && !empty($res))
		$arFilter = array_intersect($res, $arFilter);

?>
<div class="tags-cloud">
<?
	$APPLICATION->IncludeComponent("bitrix:search.tags.cloud", ".default",
		Array(
		"SEARCH" => $_REQUEST["q"] ?? null,
		"TAGS" => $_REQUEST["~TAGS"] ?? null,
		"CHECK_DATES" => $arParams["CHECK_DATES"] ?? null,
		"SORT" => $arParams["TAGS_SORT"] ?? null,
		"PAGE_ELEMENTS" => 50,
		"PERIOD" => $_REQUEST["DATE_CHANGE"] ?? null,
		"URL_SEARCH" => $arResult["URL_TEMPLATES_SEARCH"] ?? null,
		"TAGS_INHERIT" => $arParams["TAGS_INHERIT"] ?? null,
		"FONT_MAX" => (empty($arParams["FONT_MAX"]) ? "30" : $arParams["FONT_MAX"]),
		"FONT_MIN" => (empty($arParams["FONT_MIN"]) ? "8" : $arParams["FONT_MIN"]),
		"COLOR_NEW" => (empty($arParams["COLOR_NEW"]) ? "707C8F" : $arParams["COLOR_NEW"]),
		"COLOR_OLD" => (empty($arParams["COLOR_OLD"]) ? "C0C0C0" : $arParams["COLOR_OLD"]),
		"PERIOD_NEW_TAGS" => $arParams["PERIOD_NEW_TAGS"] ?? null,
		"SHOW_CHAIN" => $arParams["SHOW_CHAIN"] ?? null,
		"COLOR_TYPE" => $arParams["COLOR_TYPE"] ?? null,
		"WIDTH" => $arParams["WIDTH"] ?? null,
		"CACHE_TIME" => $arParams["CACHE_TIME"] ?? null,
		"CACHE_TYPE" => $arParams["CACHE_TYPE"] ?? null,
		"RESTART" => $arParams["RESTART"] ?? null,
		"NO_WORD_LOGIC" => $arParams["NO_WORD_LOGIC"] ?? null,
		"arrFILTER" => array("forum"),
		"arrFILTER_forum" => $arFilter
		), $component);
?>
</div>
<?
}
$APPLICATION->IncludeComponent(
	"bitrix:forum.search",
	"",
	array(
		"RESTART" => $arParams["RESTART"] ?? null,
		"NO_WORD_LOGIC" => $arParams["NO_WORD_LOGIC"] ?? null,

		"URL_TEMPLATES_INDEX" => $arResult["URL_TEMPLATES_INDEX"] ?? null,
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"] ?? null,
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"] ?? null,

		"FID_RANGE" => $arParams["FID"] ?? null,
		"TOPICS_PER_PAGE" => $arParams["TOPICS_PER_PAGE"] ?? null,
		"DATE_FORMAT" =>  $arParams["DATE_FORMAT"] ?? null,
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"] ?? null,
		"PAGE_NAVIGATION_WINDOW" => $arParams["PAGE_NAVIGATION_WINDOW"] ?? null,

		"SET_TITLE" => $arResult["SET_TITLE"] ?? null,
		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"] ?? null,
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"] ?? null,
		"CACHE_TIME" => $arResult["CACHE_TIME"] ?? null,
		"CACHE_TYPE" => $arResult["CACHE_TYPE"] ?? null,
	),
	$component
);
?>
