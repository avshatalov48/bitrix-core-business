<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:forum.user.profile.view",
	"",
	array(
		"UID" =>  $arResult["UID"] ?? null,

		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"] ?? null,
		"URL_TEMPLATES_MESSAGE" => $arResult["URL_TEMPLATES_MESSAGE"] ?? null,
		"URL_TEMPLATES_PROFILE" => $arResult["URL_TEMPLATES_PROFILE"] ?? null,
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"] ?? null,
		"URL_TEMPLATES_USER_LIST" => $arResult["URL_TEMPLATES_USER_LIST"] ?? null,
		"URL_TEMPLATES_PM_LIST" => $arResult["URL_TEMPLATES_PM_LIST"] ?? null,
		"URL_TEMPLATES_MESSAGE_SEND" => $arResult["URL_TEMPLATES_MESSAGE_SEND"] ?? null,
		"URL_TEMPLATES_USER_POST" => $arResult["URL_TEMPLATES_USER_POST"] ?? null,
		"URL_TEMPLATES_PM_EDIT" => $arResult["URL_TEMPLATES_PM_EDIT"] ?? null,
		"URL_TEMPLATES_SUBSCR_LIST" => $arResult["URL_TEMPLATES_SUBSCR_LIST"] ?? null,

		"FID_RANGE" => $arParams["FID"] ?? null,
		"DATE_FORMAT" =>  $arParams["DATE_FORMAT"] ?? null,
		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"] ?? null,
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null,
		"WORD_LENGTH" => $arParams["WORD_LENGTH"] ?? null,
		"SEND_MAIL" => $arParams["SEND_MAIL"] ?? null,
		"SEND_ICQ" => $arParams["SEND_ICQ"] ?? null,
		"USER_PROPERTY" => $arParams["USER_PROPERTY"] ?? null,
		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"] ?? null,

		"SHOW_RATING" => $arParams["SHOW_RATING"] ?? null,
		"RATING_ID" => $arParams["RATING_ID"] ?? null,
		"RATING_TYPE" => $arParams["RATING_TYPE"] ?? null,

		"CACHE_TIME" => $arParams["CACHE_TIME"] ?? null,
		"CACHE_TYPE" => $arParams["CACHE_TYPE"] ?? null,

		"SET_TITLE" => $arParams["SET_TITLE"] ?? null,
		"SEO_USER" => $arParams["SEO_USER"] ?? null,
	),
	$component
);
?><?
?>
