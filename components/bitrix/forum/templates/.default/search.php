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
		"SEARCH" => $_REQUEST["q"],
		"TAGS" => $_REQUEST["~TAGS"],
		"CHECK_DATES" => $arParams["CHECK_DATES"],
		"SORT" => $arParams["TAGS_SORT"],
		"PAGE_ELEMENTS" => 50,
		"PERIOD" => $_REQUEST["DATE_CHANGE"],
		"URL_SEARCH" => $arResult["URL_TEMPLATES_SEARCH"],
		"TAGS_INHERIT" => $arParams["TAGS_INHERIT"],
		"FONT_MAX" => (empty($arParams["FONT_MAX"]) ? "30" : $arParams["FONT_MAX"]),
		"FONT_MIN" => (empty($arParams["FONT_MIN"]) ? "8" : $arParams["FONT_MIN"]),
		"COLOR_NEW" => (empty($arParams["COLOR_NEW"]) ? "707C8F" : $arParams["COLOR_NEW"]),
		"COLOR_OLD" => (empty($arParams["COLOR_OLD"]) ? "C0C0C0" : $arParams["COLOR_OLD"]),
		"PERIOD_NEW_TAGS" => $arParams["PERIOD_NEW_TAGS"],
		"SHOW_CHAIN" => $arParams["SHOW_CHAIN"],
		"COLOR_TYPE" => $arParams["COLOR_TYPE"],
		"WIDTH" => $arParams["WIDTH"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"RESTART" => $arParams["RESTART"],
		"NO_WORD_LOGIC" => $arParams["NO_WORD_LOGIC"],
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
		"RESTART" => $arParams["RESTART"],
		"NO_WORD_LOGIC" => $arParams["NO_WORD_LOGIC"],

		"URL_TEMPLATES_INDEX" => $arResult["URL_TEMPLATES_INDEX"],
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"],
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"],
		
		"FID_RANGE" => $arParams["FID"],
		"TOPICS_PER_PAGE" => $arParams["TOPICS_PER_PAGE"],
		"DATE_FORMAT" =>  $arParams["DATE_FORMAT"],
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"PAGE_NAVIGATION_WINDOW" => $arParams["PAGE_NAVIGATION_WINDOW"],
		
		"SET_TITLE" => $arResult["SET_TITLE"],
		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"CACHE_TIME" => $arResult["CACHE_TIME"],
		"CACHE_TYPE" => $arResult["CACHE_TYPE"],
	),
	$component 
);
?>