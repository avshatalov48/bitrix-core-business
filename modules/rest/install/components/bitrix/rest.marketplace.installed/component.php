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

if(!\Bitrix\Main\Loader::includeModule('rest'))
{
	return;
}

$arResult['ADMIN'] = \CRestUtil::isAdmin();

if(!$arResult['ADMIN'] || !\Bitrix\Rest\OAuthService::getEngine()->isRegistered())
{
	return;
}

$arResult["FILTER"]["FILTER_ID"] = "marketplace_installed";
$filterOptions = new \Bitrix\Main\UI\Filter\Options($arResult["FILTER"]["FILTER_ID"]);
$filterData = $filterOptions->getFilter();

$this->arResult["AJAX_MODE"] = false;
if (isset($_POST["action"]) && $_POST["action"] == "setFilter" && check_bitrix_sessid())
{
	$this->arResult["AJAX_MODE"] = true;
}

$arCodes = array();
$updateCodes = array();
$updateStatuses = array();

AppTable::updateAppStatusInfo();

$dbApps = AppTable::getList(array(
	'filter' => array(
		'!=STATUS' => AppTable::STATUS_LOCAL,
	),
	'select' => array(
		'*', 'MENU_NAME' => 'LANG.MENU_NAME',
	)
));

while ($app = $dbApps->Fetch())
{
	if (isset($filterData["ACTIVE"] ) && $filterData["ACTIVE"] != $app["ACTIVE"])
	{
		continue;
	}

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

	$arResult["ITEMS"][$app["CODE"]] = $app;

	if ($app["ACTIVE"] == "Y")
	{
		$updateCodes[$app["CODE"]] = $app["VERSION"];
		$updateStatuses[$app["CODE"]] = $app["STATUS"];
	}
}

if (!empty($arCodes))
{
	$arAppsBuy = Client::getBuy($arCodes);

	foreach ($arAppsBuy["ITEMS"] as $key => $app)
	{
		$arResult['ITEMS'][$key]['VER'] = $app["VER"];
		$arResult['ITEMS'][$key]['NAME'] = $app["NAME"];
		$arResult['ITEMS'][$key]['ICON'] = $app["ICON"];
		$arResult['ITEMS'][$key]['DESC'] = $app["DESC"];
		$arResult['ITEMS'][$key]['PUBLIC'] = $app["PUBLIC"];
		$arResult['ITEMS'][$key]['DEMO'] = $app["DEMO"];
		$arResult['ITEMS'][$key]['PARTNER_NAME'] = $app["PARTNER_NAME"];
		$arResult['ITEMS'][$key]['PARTNER_URL'] = $app["PARTNER_URL"];
		$arResult['ITEMS'][$key]['CAN_INSTALL'] = \CRestUtil::canInstallApplication($app);

		if(is_array($app["PRICE"]) && !empty($app["PRICE"]) && $arResult['ADMIN'])
		{
			$arResult['ITEMS'][$key]['PRICE'] = $app["PRICE"];
			$arResult['ITEMS'][$key]['PRICE'] = $app["PRICE"];
			$arResult['ITEMS'][$key]['BUY'] = array();
			foreach($app["PRICE"] as $num => $price)
			{
				if($num > 1)
				{
					$arResult['ITEMS'][$key]['BUY'][] = array(
						'LINK' => Client::getBuyLink($num, $app['CODE']),
						'TEXT' => Loc::getMessage("RMP_APP_TIME_LIMIT_".$num).' - '.$price
					);
				}
			}
		}
	}
}

// updates
$arUpdatesItems = array();

if (!empty($updateCodes))
{
	$curNumUpdates = Client::getAvailableUpdateNum();

	$arUpdates = \Bitrix\Rest\Marketplace\Client::getUpdates($updateCodes);
	if(is_array($arUpdates) && !empty($arUpdates))
	{
		$newNumUpdates = Client::getAvailableUpdateNum();
		if ($curNumUpdates != $newNumUpdates)
		{
			$arResult["NEW_NUM_UPDATES"] = $newNumUpdates;
		}

		foreach ($arUpdates["ITEMS"] as $key => $app)
		{
			$arResult['ITEMS'][$app["CODE"]]["UPDATES_AVAILABLE"] = "Y";
			$arResult['ITEMS'][$app["CODE"]]["STATUS"] = $updateStatuses[$app["CODE"]];

			if ($filterData["UPDATES"] == "Y")
			{
				$arUpdatesItems[$app["CODE"]] = $arResult['ITEMS'][$app["CODE"]];
			}
			elseif ($filterData["UPDATES"] == "N")
			{
				unset($arResult['ITEMS'][$app["CODE"]]);
			}
		}
	}
}

if (isset($filterData["UPDATES"]) && $filterData["UPDATES"] == "Y")
{
	$arResult['ITEMS'] = $arUpdatesItems;
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

$APPLICATION->SetTitle(Loc::getMessage("MARKETPLACE_INSTALLED"));

$this->IncludeComponentTemplate();
?>