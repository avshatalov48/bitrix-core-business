<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

$arResult["SOCSERV"] = array();

if (isset($arParams["FACEBOOK"]) && !empty($arParams["FACEBOOK"]))
	$arResult["SOCSERV"]["FACEBOOK"] = array(
		"LINK" => $arParams["FACEBOOK"],
		"CLASS" => "fb",
		"NAME" => "Facebook",
	);

if (isset($arParams["GOOGLE"]) && !empty($arParams["GOOGLE"]))
	$arResult["SOCSERV"]["GOOGLE"] = array(
		"LINK" => $arParams["GOOGLE"],
		"CLASS" => "gp",
		"NAME" => "Google+",
	);

if (isset($arParams["TWITTER"]) && !empty($arParams["TWITTER"]))
	$arResult["SOCSERV"]["TWITTER"] = array(
		"LINK" => $arParams["TWITTER"],
		"CLASS" => "tw",
		"NAME" => "Twitter",
	);

if (isset($arParams["VKONTAKTE"]) && !empty($arParams["VKONTAKTE"]))
	$arResult["SOCSERV"]["VKONTAKTE"] = array(
		"LINK" => $arParams["VKONTAKTE"],
		"CLASS" => "vk",
		"NAME" => "Vkontakte",
	);

if (isset($arParams["INSTAGRAM"]) && !empty($arParams["INSTAGRAM"]))
	$arResult["SOCSERV"]["INSTAGRAM"] = array(
		"LINK" => $arParams["INSTAGRAM"],
		"CLASS" => "in",
		"NAME" => "Instagram",
	);

$arResult['FACEBOOK_CONVERSION_ENABLED'] =
	\Bitrix\Main\Loader::includeModule('sale')
	&& \Bitrix\Sale\Internals\FacebookConversion::isEventEnabled('Contact')
;

$this->IncludeComponentTemplate();
?>