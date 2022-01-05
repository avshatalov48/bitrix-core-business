<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

use \Bitrix\Rest\AppTable;
use \Bitrix\Rest\Marketplace\Client;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Rest\Engine\Access;
use \Bitrix\Main\UI\PageNavigation;

$APPLICATION->SetTitle(Loc::getMessage("MARKETPLACE_INSTALLED"));

if(!\Bitrix\Main\Loader::includeModule('rest'))
{
	return;
}

$arResult['ADMIN'] = \CRestUtil::isAdmin();

if(!$arResult['ADMIN'])
{
	ShowError(GetMessage("RMI_ACCESS_DENIED"));
	return;
}
else if (!\Bitrix\Rest\OAuthService::getEngine()->isRegistered())
{
	try
	{
		\Bitrix\Rest\OAuthService::register();
		\Bitrix\Rest\OAuthService::getEngine()->getClient()->getApplicationList();
	}
	catch(\Bitrix\Main\SystemException $e)
	{
		ShowError($e->getMessage());
		return;
	}
	if (!\Bitrix\Rest\OAuthService::getEngine()->isRegistered())
	{
		ShowError(GetMessage("RMI_ACCESS_DENIED_OAUTH_SERVICE_IS_NOT_REGISTERED"));
		return;
	}
}

$arParams['NAVIGATION_NAME'] = $arParams['NAVIGATION_NAME'] ?? 'PAGE_MARKET_INSTALLED';
$arParams['NAVIGATION_PAGE_SIZE'] = 20;
$arResult['SUBSCRIPTION_BUY_URL'] = \Bitrix\Rest\Marketplace\Url::getSubscriptionBuyUrl();

if (!\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24'))
{
	$arResult['POPUP_BUY_SUBSCRIPTION_PRIORITY'] = true;
}

$arResult["FILTER"]["FILTER_ID"] = "marketplace_installed";
$filterOptions = new \Bitrix\Main\UI\Filter\Options($arResult["FILTER"]["FILTER_ID"]);
$filterData = $filterOptions->getFilter();

$this->arResult["AJAX_MODE"] = false;
if (isset($_POST["action"]) && $_POST["action"] == "setFilter" && check_bitrix_sessid())
{
	$this->arResult["AJAX_MODE"] = true;
}
$curUri = new \Bitrix\Main\Web\Uri(
	$this->request->getRequestUri()
);
$curUri->deleteParams(
	[
		$arParams['NAVIGATION_NAME'],
		'amp;'.$arParams['NAVIGATION_NAME'],
		'amp;IFRAME_TYPE',
		'amp;IFRAME'
	]
);

$arResult['CUR_URI'] = $curUri->getUri();

$arCodes = array();

AppTable::updateAppStatusInfo();
Client::getNumUpdates();

$arResult["NAV_OBJECT"] = new PageNavigation($arParams['NAVIGATION_NAME']);
$arResult["NAV_OBJECT"]
	->allowAllRecords(false)
	->setPageSize($arParams['NAVIGATION_PAGE_SIZE'])
	->initFromUri();

$filter = [
	'!=STATUS' => AppTable::STATUS_LOCAL,
];

if (isset($filterData["ACTIVE"]))
{
	$filter['=ACTIVE'] = $filterData["ACTIVE"] === AppTable::ACTIVE ? AppTable::ACTIVE : AppTable::INACTIVE;
}

if (isset($filterData["UPDATES"]))
{
	$appUpdates = Client::getAvailableUpdate();
	if ($filterData["UPDATES"] === 'Y')
	{
		$filter['=CODE'] = array_keys($appUpdates);
	}
	elseif ($filterData["UPDATES"] === 'N')
	{
		$filter['!=CODE'] = array_keys($appUpdates);
	}
}

$dbApps = AppTable::getList(
	[
		'filter' => $filter,
		'select' => [
			'*',
			'MENU_NAME' => 'LANG.MENU_NAME',
		],
		'offset' => $arResult['NAV_OBJECT']->getOffset(),
		'limit' => $arResult['NAV_OBJECT']->getLimit(),
		'count_total' => true,
	]
);

$arResult['NAV_OBJECT']->setRecordCount($dbApps->getCount());
while ($app = $dbApps->Fetch())
{

	$arCodes[] = $app["CODE"];
	$app['APP_STATUS'] = AppTable::getAppStatusInfo($app, str_replace(
		array("#app#"),
		array(urlencode($app['CODE'])),
		$arParams['DETAIL_URL_TPL']
	));
	if(isset($app['APP_STATUS']['MESSAGE_REPLACE']['#DAYS#']))
	{
		$app['APP_STATUS']['MESSAGE_REPLACE']['#DAYS#']++;
		$app['APP_STATUS']['MESSAGE_REPLACE']['#DAYS#'] = FormatDate("ddiff", time(), time() + 24 * 60 * 60 * $app['APP_STATUS']['MESSAGE_REPLACE']["#DAYS#"]);
	}

	$app['REST_ACCESS'] = Access::isAvailable($app["CODE"]) && Access::isAvailableCount(Access::ENTITY_TYPE_APP, $app['CODE']);

	if (!$app['REST_ACCESS'])
	{
		$app['REST_ACCESS_HELPER_CODE'] = Access::getHelperCode(Access::ACTION_INSTALL, Access::ENTITY_TYPE_APP, $app);
	}

	$arResult["ITEMS"][$app["CODE"]] = $app;
}


if (!empty($arCodes))
{
	$arAppsBuy = Client::getBuy($arCodes);

	if (isset($arAppsBuy['ITEMS']) && is_array($arAppsBuy['ITEMS']))
	{
		foreach ($arAppsBuy['ITEMS'] as $key => $app)
		{
			if (isset($filterData['UPDATES']) && $filterData['UPDATES'] === 'Y')
			{
				if ($app['TYPE'] === AppTable::TYPE_CONFIGURATION)
				{
					unset($arResult['ITEMS'][$key]);
					continue;
				}
				$arResult['ITEMS'][$key]['UPDATES_AVAILABLE'] = 'Y';
			}

			$arResult['ITEMS'][$key]['VER'] = $app["VER"];
			$arResult['ITEMS'][$key]['NAME'] = $app["NAME"];
			$arResult['ITEMS'][$key]['ICON'] = $app["ICON"];
			$arResult['ITEMS'][$key]['DESC'] = $app["DESC"];
			$arResult['ITEMS'][$key]['PUBLIC'] = $app["PUBLIC"];
			$arResult['ITEMS'][$key]['DEMO'] = $app["DEMO"];
			$arResult['ITEMS'][$key]['PARTNER_NAME'] = $app["PARTNER_NAME"];
			$arResult['ITEMS'][$key]['PARTNER_URL'] = $app["PARTNER_URL"];
			$arResult['ITEMS'][$key]['OTHER_REGION'] = $app["OTHER_REGION"];
			$arResult['ITEMS'][$key]['VENDOR_SHOP_LINK'] = $app["VENDOR_SHOP_LINK"];
			$arResult['ITEMS'][$key]['TYPE'] = $app["TYPE"];
			$arResult['ITEMS'][$key]['CAN_INSTALL'] = \CRestUtil::canInstallApplication($app);

			if (
				is_array($app["PRICE"])
				&& !empty($app["PRICE"])
				&& $arResult['ADMIN']
				&& $app['BY_SUBSCRIPTION'] !== 'Y'
			)
			{
				$arResult['ITEMS'][$key]['PRICE'] = $app["PRICE"];
				$arResult['ITEMS'][$key]['PRICE'] = $app["PRICE"];
				$arResult['ITEMS'][$key]['BUY'] = array();
				foreach ($app["PRICE"] as $num => $price) {
					if ($num > 1) {
						$arResult['ITEMS'][$key]['BUY'][] = array(
							'LINK' => Client::getBuyLink($num, $app['CODE']),
							'TEXT' => Loc::getMessage("RMP_APP_TIME_LIMIT_" . $num) . ' - ' . $price
						);
					}
				}
			}
		}
	}
}


$arResult['FILTER']['FILTER'] = array(
	array(
		"id"      => "UPDATES",
		"name"    => Loc::getMessage("MARKETPLACE_FILTER_UPDATES"),
		"type"  => "checkbox",
		"default" => true
	),
	array(
		"id"    => "ACTIVE",
		"name"  => Loc::getMessage("MARKETPLACE_FILTER_INSTALLED"),
		"type"  => "checkbox",
		"default" => true
	),
);

$this->arResult["FILTER"]["FILTER_PRESETS"] = array(
	"new" => array(
		"name" => Loc::getMessage("MARKETPLACE_FILTER_UPDATES"),
		'default' => false,
		"fields" => array(
			"UPDATES" => "Y"
		)
	)
);

CJSCore::Init(array('marketplace'));


$this->IncludeComponentTemplate();
?>