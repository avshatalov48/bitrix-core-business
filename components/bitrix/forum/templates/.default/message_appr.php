<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:forum.message.approve",
	"",
	array(
		"FID" => $arResult["FID"] ?? null,
		"TID" => $arResult["TID"] ?? null,

		"URL_TEMPLATES_INDEX" => $arResult["URL_TEMPLATES_INDEX"] ?? null,
		"URL_TEMPLATES_FORUMS"	=>	$arResult["URL_TEMPLATES_FORUMS"] ?? null,
		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"] ?? null,
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"] ?? null,
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"] ?? null,
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"] ?? null,
		"URL_TEMPLATES_PM_EDIT" => $arResult["URL_TEMPLATES_PM_EDIT"] ?? null,
		"URL_TEMPLATES_MESSAGE_SEND" => $arResult["URL_TEMPLATES_MESSAGE_SEND"] ?? null,
		"URL_TEMPLATES_MESSAGE_APPR" =>  $arResult["URL_TEMPLATES_MESSAGE_APPR"] ?? null,
		"URL_TEMPLATES_TOPIC_NEW"	=>	$arResult["URL_TEMPLATES_TOPIC_NEW"] ?? null,

		"USER_FIELDS" => $arParams["USER_FIELDS"] ?? null,
		"MESSAGES_PER_PAGE" => $arParams["MESSAGES_PER_PAGE"] ?? null,
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"] ?? null,
		"WORD_LENGTH" => $arParams["WORD_LENGTH"] ?? null,
		"IMAGE_SIZE" => $arParams["IMAGE_SIZE"] ?? null,
		"ATTACH_MODE" => $arParams["ATTACH_MODE"] ?? null,
		"ATTACH_SIZE" => $arParams["ATTACH_SIZE"] ?? null,
		"DATE_FORMAT" =>  $arResult["DATE_FORMAT"] ?? null,
		"DATE_TIME_FORMAT" =>  $arResult["DATE_TIME_FORMAT"] ?? null,
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null,

		"SET_NAVIGATION" => $arResult["SET_NAVIGATION"] ?? null,
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"] ?? null,
		"SET_TITLE" => $arResult["SET_TITLE"] ?? null,
		"CACHE_TYPE" => $arResult["CACHE_TYPE"] ?? null,
		"CACHE_TIME" => $arResult["CACHE_TIME"] ?? null,

		"SEND_MAIL" => $arParams["SEND_MAIL"] ?? null,
		"SEND_ICQ" => "A",
		"SEO_USER" => $arParams["SEO_USER"] ?? null,
		"HIDE_USER_ACTION" => $arParams["HIDE_USER_ACTION"] ?? null
	),
	$component
);?><?
?>
