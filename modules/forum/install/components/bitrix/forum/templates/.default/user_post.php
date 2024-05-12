<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:forum.user.post",
	"",
	array(
		"UID" =>  $arResult["UID"] ?? null,
		"mode" =>  $arResult["mode"] ?? null,

		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"] ?? null,
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"] ?? null,
		"URL_TEMPLATES_MESSAGE" => $arResult["URL_TEMPLATES_MESSAGE"] ?? null,
		"URL_TEMPLATES_USER_LIST" =>  $arResult["URL_TEMPLATES_USER_LIST"] ?? null,
		"URL_TEMPLATES_PROFILE_VIEW" => ($arParams["SEO_USER"] == "TEXT" ? "" : $arResult["URL_TEMPLATES_PROFILE_VIEW"]),
		"URL_TEMPLATES_USER_POST" =>  $arResult["URL_TEMPLATES_USER_POST"] ?? null,
		"URL_TEMPLATES_PM_EDIT" => $arResult["URL_TEMPLATES_PM_EDIT"] ?? null,
		"URL_TEMPLATES_MESSAGE_SEND" => $arResult["URL_TEMPLATES_MESSAGE_SEND"] ?? null,

		"USER_FIELDS" => $arParams["USER_FIELDS"] ?? null,
		"MESSAGES_PER_PAGE" => $arParams["MESSAGES_PER_PAGE"] ?? null,
		"FID_RANGE" => $arParams["FID"] ?? null,
		"DATE_FORMAT" =>  $arParams["DATE_FORMAT"] ?? null,
		"NAME_TEMPLATE"	=> $arParams["NAME_TEMPLATE"] ?? null,
		"DATE_TIME_FORMAT" =>  $arParams["DATE_TIME_FORMAT"] ?? null,
		"PAGE_NAVIGATION_TEMPLATE" =>  $arParams["PAGE_NAVIGATION_TEMPLATE"] ?? null,
		"WORD_LENGTH" => $arParams["WORD_LENGTH"] ?? null,
		"IMAGE_SIZE" => $arParams["IMAGE_SIZE"] ?? null,
		"ATTACH_MODE" => $arParams["ATTACH_MODE"] ?? null,
		"ATTACH_SIZE" => $arParams["ATTACH_SIZE"] ?? null,
		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"] ?? null,
		"SET_TITLE" => $arParams["SET_TITLE"] ?? null,
		"SEND_MAIL" => $arParams["SEND_MAIL"] ?? null,
		"SEND_ICQ" => $arParams["SEND_ICQ"] ?? null,

		"SEO_USER" => $arParams["SEO_USER"] ?? null,

		"CACHE_TIME" => $arParams["CACHE_TIME"] ?? null,
		"CACHE_TYPE" => $arParams["CACHE_TYPE"] ?? null,
	),
	$component
);
?>
