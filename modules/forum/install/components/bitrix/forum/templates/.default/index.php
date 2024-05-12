<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$arForums = $APPLICATION->IncludeComponent("bitrix:forum.index", "",
	array(
		"URL_TEMPLATES_INDEX" => $arResult["URL_TEMPLATES_INDEX"] ?? null,
		"URL_TEMPLATES_FORUMS" => $arResult["URL_TEMPLATES_FORUMS"] ?? null,
		"URL_TEMPLATES_LIST" => $arResult["URL_TEMPLATES_LIST"] ?? null,
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"] ?? null,
		"URL_TEMPLATES_MESSAGE" => $arResult["URL_TEMPLATES_MESSAGE"] ?? null,
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"] ?? null,
		"URL_TEMPLATES_MESSAGE_APPR" => $arResult["URL_TEMPLATES_MESSAGE_APPR"] ?? null,
		"URL_TEMPLATES_RSS" => $arResult["URL_TEMPLATES_RSS"] ?? null,

		"GID" => $arResult["GID"] ?? null,
		"FORUMS_PER_PAGE" => $arResult["FORUMS_PER_PAGE"] ?? null,
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"] ?? null,
		"PAGE_NAVIGATION_WINDOW" => $arParams["PAGE_NAVIGATION_WINDOW"] ?? null,
		"FID" => $arParams["FID"] ?? null,
		"DATE_FORMAT" => $arResult["DATE_FORMAT"] ?? null,
		"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"] ?? null,
		"NAME_TEMPLATE" =>  $arParams["NAME_TEMPLATE"] ?? null,
		"WORD_LENGTH" => $arParams["WORD_LENGTH"] ?? null,
		"MINIMIZE_SQL" => $arParams["MINIMIZE_SQL"] ?? null,

		"SHOW_FORUMS_LIST" =>  "Y",
		"SHOW_FORUM_ANOTHER_SITE" => $arResult["SHOW_FORUM_ANOTHER_SITE"] ?? null,

		"SET_TITLE" => $arParams["SET_TITLE"] ?? null,
		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"] ?? null,
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"] ?? null,
		"CACHE_TIME" => $arResult["CACHE_TIME"] ?? null,
		"CACHE_TYPE" => $arResult["CACHE_TYPE"] ?? null,

		"TMPLT_SHOW_ADDITIONAL_MARKER"	=>	$arParams["~TMPLT_SHOW_ADDITIONAL_MARKER"] ?? null,
		"WORD_WRAP_CUT" => $arParams["WORD_WRAP_CUT"] ?? null,
		"SHOW_RSS" => $arParams["USE_RSS"] ?? null
	),
	$component
);?><?
if (sizeof($arParams['SHOW_STATISTIC_BLOCK']) > 0)
{
	$APPLICATION->IncludeComponent("bitrix:forum.statistic", ".default", Array(
		"FID"	=>	0,
		"TID"	=>	0,
		"PERIOD"	=>	$arParams["TIME_INTERVAL_FOR_USER_STAT"] ?? null,
		"SHOW"		=>	$arParams["SHOW_STATISTIC_BLOCK"] ?? null,
		"SHOW_FORUM_ANOTHER_SITE"	=>	$arParams["SHOW_FORUM_ANOTHER_SITE"] ?? null,
		"FORUM_ID"	=>	$arForums,

		"URL_TEMPLATES_PROFILE_VIEW"	=>	$arResult["URL_TEMPLATES_PROFILE_VIEW"] ?? null,

		"CACHE_TYPE" => $arParams["CACHE_TYPE"] ?? null,
		"CACHE_TIME" => $arParams["CACHE_TIME"] ?? null,
		"CACHE_TIME_USER_STAT" => $arParams["CACHE_TIME_USER_STAT"] ?? null,
		"CACHE_TIME_FOR_FORUM_STAT" => $arParams["CACHE_TIME_FOR_FORUM_STAT"] ?? null,
		"WORD_LENGTH"	=>	$arParams["WORD_LENGTH"] ?? null,
		"WORD_WRAP_CUT" => $arParams["WORD_WRAP_CUT"] ?? null,
		"SEO_USER" => $arParams["SEO_USER"] ?? null,
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null
	),
	$component);
}
include_once(str_replace(array("\\", "//"), "/", __DIR__."/footer.php"));
?>
