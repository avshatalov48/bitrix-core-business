<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:forum.user.post",
	"",
	array(
		"UID" =>  $arResult["UID"],
		"mode" =>  $arResult["mode"],
		
		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"],
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"],
		"URL_TEMPLATES_MESSAGE" => $arResult["URL_TEMPLATES_MESSAGE"],
		"URL_TEMPLATES_USER_LIST" =>  $arResult["URL_TEMPLATES_USER_LIST"],
		"URL_TEMPLATES_PROFILE_VIEW" => ($arParams["SEO_USER"] == "TEXT" ? "" : $arResult["URL_TEMPLATES_PROFILE_VIEW"]),
		"URL_TEMPLATES_USER_POST" =>  $arResult["URL_TEMPLATES_USER_POST"],
		"URL_TEMPLATES_PM_EDIT" => $arResult["URL_TEMPLATES_PM_EDIT"],
		"URL_TEMPLATES_MESSAGE_SEND" => $arResult["URL_TEMPLATES_MESSAGE_SEND"],

		"USER_FIELDS" => $arParams["USER_FIELDS"],
		"MESSAGES_PER_PAGE" => $arParams["MESSAGES_PER_PAGE"],
		"FID_RANGE" => $arParams["FID"],
		"DATE_FORMAT" =>  $arParams["DATE_FORMAT"],
		"NAME_TEMPLATE"	=> $arParams["NAME_TEMPLATE"],
		"DATE_TIME_FORMAT" =>  $arParams["DATE_TIME_FORMAT"],
		"PAGE_NAVIGATION_TEMPLATE" =>  $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"WORD_LENGTH" => $arParams["WORD_LENGTH"],
		"IMAGE_SIZE" => $arParams["IMAGE_SIZE"],
		"ATTACH_MODE" => $arParams["ATTACH_MODE"],
		"ATTACH_SIZE" => $arParams["ATTACH_SIZE"],
		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"SEND_MAIL" => $arParams["SEND_MAIL"],
		"SEND_ICQ" => $arParams["SEND_ICQ"],

		"SEO_USER" => $arParams["SEO_USER"],

		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	),
	$component 
);
?>