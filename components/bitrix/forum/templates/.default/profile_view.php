<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:forum.user.profile.view",
	"",
	array(
		"UID" =>  $arResult["UID"],
		
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"],
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"],
		"URL_TEMPLATES_PROFILE" => $arResult["URL_TEMPLATES_PROFILE"],
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"],
		"URL_TEMPLATES_USER_LIST" => $arResult["URL_TEMPLATES_USER_LIST"],
		"URL_TEMPLATES_PM_LIST" =>  $arResult["URL_TEMPLATES_PM_LIST"],
		"URL_TEMPLATES_MESSAGE_SEND" =>  $arResult["URL_TEMPLATES_MESSAGE_SEND"],
		"URL_TEMPLATES_USER_POST" =>  $arResult["URL_TEMPLATES_USER_POST"],
		"URL_TEMPLATES_PM_EDIT" =>  $arResult["URL_TEMPLATES_PM_EDIT"],
		"URL_TEMPLATES_SUBSCR_LIST" =>  $arResult["URL_TEMPLATES_SUBSCR_LIST"],
		
		"FID_RANGE" => $arParams["FID"],
		"DATE_FORMAT" =>  $arParams["DATE_FORMAT"],
		"DATE_TIME_FORMAT" =>  $arParams["DATE_TIME_FORMAT"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"WORD_LENGTH" =>  $arParams["WORD_LENGTH"],
		"SEND_MAIL" => $arParams["SEND_MAIL"],
		"SEND_ICQ" => $arParams["SEND_ICQ"],
		"USER_PROPERTY" => $arParams["USER_PROPERTY"],
		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"],

		"SHOW_RATING" => $arParams["SHOW_RATING"], 
		"RATING_ID" => $arParams["RATING_ID"],
		"RATING_TYPE" => $arParams["RATING_TYPE"],
		
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		
		"SET_TITLE" => $arParams["SET_TITLE"],
		"SEO_USER" => $arParams["SEO_USER"],
	),
	$component
);
?><?
?>
