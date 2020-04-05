<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$arForums = $APPLICATION->IncludeComponent("bitrix:forum.index", "", 
	array(
		"URL_TEMPLATES_INDEX" =>  $arResult["URL_TEMPLATES_INDEX"],
		"URL_TEMPLATES_FORUMS" =>  $arResult["URL_TEMPLATES_FORUMS"],
		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"],
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"],
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"],
		"URL_TEMPLATES_PROFILE_VIEW" =>  $arResult["URL_TEMPLATES_PROFILE_VIEW"],
		"URL_TEMPLATES_MESSAGE_APPR" =>  $arResult["URL_TEMPLATES_MESSAGE_APPR"],
		"URL_TEMPLATES_RSS" => $arResult["URL_TEMPLATES_RSS"],
		
		"GID" =>  $arResult["GID"],
		"FORUMS_PER_PAGE" => $arResult["FORUMS_PER_PAGE"],
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"PAGE_NAVIGATION_WINDOW" => $arParams["PAGE_NAVIGATION_WINDOW"], 
		"FID" =>  $arParams["FID"],
		"DATE_FORMAT" =>  $arResult["DATE_FORMAT"],
		"DATE_TIME_FORMAT" =>  $arResult["DATE_TIME_FORMAT"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"WORD_LENGTH" => $arParams["WORD_LENGTH"],
		"MINIMIZE_SQL" => $arParams["MINIMIZE_SQL"], 
		
		"SHOW_FORUMS_LIST" =>  "Y",
		"SHOW_FORUM_ANOTHER_SITE" =>  $arResult["SHOW_FORUM_ANOTHER_SITE"],
		
		"SET_TITLE" => $arParams["SET_TITLE"],
		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"], 
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"CACHE_TIME" => $arResult["CACHE_TIME"],
		"CACHE_TYPE" => $arResult["CACHE_TYPE"], 
		
		"TMPLT_SHOW_ADDITIONAL_MARKER"	=>	$arParams["~TMPLT_SHOW_ADDITIONAL_MARKER"],
		"WORD_WRAP_CUT" => $arParams["WORD_WRAP_CUT"], 
		"SHOW_RSS" => $arParams["USE_RSS"]	
	),
	$component
);?><?
if (sizeof($arParams['SHOW_STATISTIC_BLOCK']) > 0)
{
	$APPLICATION->IncludeComponent("bitrix:forum.statistic", ".default", Array(
		"FID"	=>	0,
		"TID"	=>	0,
		"PERIOD"	=>	$arParams["TIME_INTERVAL_FOR_USER_STAT"],
		"SHOW"		=>	$arParams["SHOW_STATISTIC_BLOCK"],
		"SHOW_FORUM_ANOTHER_SITE"	=>	$arParams["SHOW_FORUM_ANOTHER_SITE"],
		"FORUM_ID"	=>	$arForums,
		
		"URL_TEMPLATES_PROFILE_VIEW"	=>	$arResult["URL_TEMPLATES_PROFILE_VIEW"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TIME_USER_STAT" => $arParams["CACHE_TIME_USER_STAT"], 
		"CACHE_TIME_FOR_FORUM_STAT" => $arParams["CACHE_TIME_FOR_FORUM_STAT"],
		"WORD_LENGTH"	=>	$arParams["WORD_LENGTH"], 
		"WORD_WRAP_CUT" => $arParams["WORD_WRAP_CUT"], 
		"SEO_USER" => $arParams["SEO_USER"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]
	),
	$component);
}
include_once(str_replace(array("\\", "//"), "/", dirname(__FILE__)."/footer.php"));
?>
