<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:forum.user.list",
	"",
	array(
		"SEND_MAIL" => $arParams["SEND_MAIL"],
		"SEND_ICQ" => "A",
		"SHOW_USER_STATUS" => $arParams["SHOW_USER_STATUS"],
		
		"URL_TEMPLATES_MESSAGE_SEND" => $arResult["URL_TEMPLATES_MESSAGE_SEND"],
		"URL_TEMPLATES_PM_EDIT" => $arResult["URL_TEMPLATES_PM_EDIT"],
		"URL_TEMPLATES_PROFILE_VIEW" =>  $arResult["URL_TEMPLATES_PROFILE_VIEW"],
		"URL_TEMPLATES_USER_POST" =>  $arResult["URL_TEMPLATES_USER_POST"],
		
		"USERS_PER_PAGE" => $arResult["USERS_PER_PAGE"],
		"DATE_FORMAT" =>  $arResult["DATE_FORMAT"],
		"DATE_TIME_FORMAT" =>  $arResult["DATE_TIME_FORMAT"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"PAGE_NAVIGATION_TEMPLATE" =>  $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"PAGE_NAVIGATION_WINDOW" =>  $arParams["PAGE_NAVIGATION_WINDOW"],
		"WORD_LENGTH" => $arParams["WORD_LENGTH"],
		"USER_PROPERTY" => $arParams["USER_PROPERTY"],
		
		"SET_NAVIGATION" => $arResult["SET_NAVIGATION"],
		
		"SET_TITLE" => $arResult["SET_TITLE"],
		"CACHE_TIME" => $arResult["CACHE_TIME"],
		"CACHE_TYPE" => $arResult["CACHE_TYPE"],
		
		"SEO_USER" => $arParams["SEO_USER"]
	),
	$component 
);
?>