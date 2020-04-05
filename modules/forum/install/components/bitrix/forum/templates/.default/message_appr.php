<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:forum.message.approve",
	"",
	array(
		"FID" => $arResult["FID"],
		"TID" => $arResult["TID"],
		
		"URL_TEMPLATES_INDEX" => $arResult["URL_TEMPLATES_INDEX"],
		"URL_TEMPLATES_FORUMS"	=>	$arResult["URL_TEMPLATES_FORUMS"],
		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"],
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"],
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"],
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"],
		"URL_TEMPLATES_PM_EDIT" => $arResult["URL_TEMPLATES_PM_EDIT"],
		"URL_TEMPLATES_MESSAGE_SEND" => $arResult["URL_TEMPLATES_MESSAGE_SEND"],
		"URL_TEMPLATES_MESSAGE_APPR" =>  $arResult["URL_TEMPLATES_MESSAGE_APPR"],
		"URL_TEMPLATES_TOPIC_NEW"	=>	$arResult["URL_TEMPLATES_TOPIC_NEW"],

		"USER_FIELDS" => $arParams["USER_FIELDS"],
		"MESSAGES_PER_PAGE" => $arParams["MESSAGES_PER_PAGE"],
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"WORD_LENGTH" => $arParams["WORD_LENGTH"],
		"IMAGE_SIZE" => $arParams["IMAGE_SIZE"],
		"ATTACH_MODE" => $arParams["ATTACH_MODE"],
		"ATTACH_SIZE" => $arParams["ATTACH_SIZE"],
		"DATE_FORMAT" =>  $arResult["DATE_FORMAT"],
		"DATE_TIME_FORMAT" =>  $arResult["DATE_TIME_FORMAT"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],

		"SET_NAVIGATION" => $arResult["SET_NAVIGATION"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"SET_TITLE" => $arResult["SET_TITLE"],
		"CACHE_TYPE" => $arResult["CACHE_TYPE"],
		"CACHE_TIME" => $arResult["CACHE_TIME"],
		
		"SEND_MAIL" => $arParams["SEND_MAIL"],
		"SEND_ICQ" => "A",
		"SEO_USER" => $arParams["SEO_USER"],
		"HIDE_USER_ACTION" => $arParams["HIDE_USER_ACTION"]
	),
	$component
);?><?
?>