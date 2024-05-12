<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
	"bitrix:forum.message.send",
	"",
	array(
		"UID" => $arResult["UID"] ?? null,
		"TYPE" => $arResult["TYPE"] ?? null,
		"SEND_MAIL" => $arParams["SEND_MAIL"] ?? null,
		"SEND_ICQ" => $arParams["SEND_ICQ"] ?? null,

		"URL_TEMPLATES_MESSAGE_SEND" => $arResult["URL_TEMPLATES_MESSAGE_SEND"] ?? null,
		"URL_TEMPLATES_PROFILE_VIEW" => ($arParams["SEO_USER"] == "TEXT" ? "" : $arResult["URL_TEMPLATES_PROFILE_VIEW"]),

		"SET_NAVIGATION" => $arResult["SET_NAVIGATION"] ?? null,

		"CACHE_TIME" => $arResult["CACHE_TIME"] ?? null,
		"CACHE_TYPE" => $arResult["CACHE_TYPE"] ?? null,
		"SET_TITLE" => $arResult["SET_TITLE"] ?? null,

		"SEO_USER" => $arParams["SEO_USER"] ?? null,
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null
	),
	$component
);
?>
