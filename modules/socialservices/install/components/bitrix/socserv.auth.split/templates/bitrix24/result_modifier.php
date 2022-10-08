<?
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

$socServAvailableList = ["Dropbox", "GoogleOAuth", "Office365", "Box", "YandexOAuth", "LiveIDOAuth", "zoom"];
$portalPrefix = '';
if (Loader::includeModule('bitrix24'))
{
	$portalPrefix = \CBitrix24::getLicensePrefix();
}

if (isset($arResult["AUTH_SERVICES"]) && is_array($arResult["AUTH_SERVICES"]))
{
	foreach ($arResult["AUTH_SERVICES"] as $serviceCode => $data)
	{
		if (
			!in_array($serviceCode, $socServAvailableList)
			|| in_array($serviceCode, \CSocServAuthManager::listServicesBlockedByZone($portalPrefix), true)
		)
		{
			unset($arResult["AUTH_SERVICES"][$serviceCode]);
		}
	}

	$arResult["AUTH_SERVICES_DISK"] = $arResult["AUTH_SERVICES"];
	if (isset($arResult["AUTH_SERVICES_DISK"]["zoom"]))
	{
		unset($arResult["AUTH_SERVICES_DISK"]["zoom"]);
	}
}

$arResult["DB_SOCSERV_USER_DISK"] = [];

if (isset($arResult["DB_SOCSERV_USER"]) && is_array($arResult["DB_SOCSERV_USER"]))
{
	foreach ($arResult["DB_SOCSERV_USER"] as $key => $data)
	{
		if (!in_array($data["EXTERNAL_AUTH_ID"], $socServAvailableList))
		{
			unset($arResult["DB_SOCSERV_USER"][$key]);
		}
		elseif ($data["EXTERNAL_AUTH_ID"] !== "zoom")
		{
			$arResult["DB_SOCSERV_USER_DISK"][] = $data;
		}
	}
}