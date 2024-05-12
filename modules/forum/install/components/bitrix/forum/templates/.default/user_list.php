<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:forum.user.list",
	"",
	array(
		"SEND_MAIL" => $arParams["SEND_MAIL"] ?? null,
		"SEND_ICQ" => "A",
		"SHOW_USER_STATUS" => $arParams["SHOW_USER_STATUS"] ?? null,

		"URL_TEMPLATES_MESSAGE_SEND" => $arResult["URL_TEMPLATES_MESSAGE_SEND"] ?? null,
		"URL_TEMPLATES_PM_EDIT" => $arResult["URL_TEMPLATES_PM_EDIT"] ?? null,
		"URL_TEMPLATES_PROFILE_VIEW" =>  $arResult["URL_TEMPLATES_PROFILE_VIEW"] ?? null,
		"URL_TEMPLATES_USER_POST" =>  $arResult["URL_TEMPLATES_USER_POST"] ?? null,

		"USERS_PER_PAGE" => $arResult["USERS_PER_PAGE"] ?? null,
		"DATE_FORMAT" =>  $arResult["DATE_FORMAT"] ?? null,
		"DATE_TIME_FORMAT" =>  $arResult["DATE_TIME_FORMAT"] ?? null,
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null,
		"PAGE_NAVIGATION_TEMPLATE" =>  $arParams["PAGE_NAVIGATION_TEMPLATE"] ?? null,
		"PAGE_NAVIGATION_WINDOW" =>  $arParams["PAGE_NAVIGATION_WINDOW"] ?? null,
		"WORD_LENGTH" => $arParams["WORD_LENGTH"] ?? null,
		"USER_PROPERTY" => $arParams["USER_PROPERTY"] ?? null,

		"SET_NAVIGATION" => $arResult["SET_NAVIGATION"] ?? null,

		"SET_TITLE" => $arResult["SET_TITLE"] ?? null,
		"CACHE_TIME" => $arResult["CACHE_TIME"] ?? null,
		"CACHE_TYPE" => $arResult["CACHE_TYPE"] ?? null,

		"SEO_USER" => $arParams["SEO_USER"] ?? null
	),
	$component
);
?>
