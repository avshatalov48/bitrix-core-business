<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arCurrentValues */

$currencyList = array();
$baseCurrencyCode = "";

if (CModule::IncludeModule("currency"))
{
	$currencyList = \Bitrix\Currency\CurrencyManager::getCurrencyList();
}

if (isset($_REQUEST['src_site']) && $_REQUEST['src_site'] && is_string($_REQUEST['src_site']))
{
	$siteId = $_REQUEST['src_site'];
}
else
{
	$siteId = \CSite::GetDefSite();
}

if (Bitrix\Main\Loader::includeModule('sale'))
{
	$personTypeList = Bitrix\Sale\PersonType::load($siteId);
	foreach ($personTypeList as $personTypeElement)
	{
		$personTypes[$personTypeElement["ID"]] = $personTypeElement['NAME'];
	}
	$baseCurrencyCode = Bitrix\Sale\Internals\SiteCurrencyTable::getSiteCurrency($siteId);
}

$arComponentParameters = array(
	"PARAMETERS" => array(
		"PATH_TO_BASKET" => array(
			"NAME" => GetMessage("SAPP_PATH_TO_BASKET"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/personal/cart",
			"COLS" => 25,
		),

		"PATH_TO_PAYMENT" => array(
			"NAME" => GetMessage("SAPP_PATH_TO_PAYMENT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/personal/order/payment",
			"COLS" => 25,
		),

		"SELL_CURRENCY" => array(
			"NAME"=>GetMessage("SAPP_SELL_CURRENCY"),
			"TYPE"=>"LIST",
			"MULTIPLE"=>"N",
			"VALUES" => $currencyList,
			"COLS"=>25,
			"ADDITIONAL_VALUES"=>"N",
			"DEFAULT"=>$baseCurrencyCode
		),
	)
);

if (
	!empty($arCurrentValues['SELL_AMOUNT'])
	&& ($arCurrentValues["REFRESHED_COMPONENT_MODE"] ?? 'N') !== 'Y'
)
{
	$arAmount = array();
	$arAvAmount = unserialize(Bitrix\Main\Config\Option::get("sale", "pay_amount"), ['allowed_classes' => false]);
	if (empty($arAvAmount))
	{
		$arAvAmount = array (
			array ('AMOUNT' => 10,'CURRENCY' => 'EUR'),
			array ('AMOUNT' => 20,'CURRENCY' => 'EUR'),
			array ('AMOUNT' => 30,'CURRENCY' => 'EUR'),
			array ('AMOUNT' => 40,'CURRENCY' => 'EUR')
		);
	}

	if (CModule::IncludeModule("sale"))
	{
		if (!empty($arAvAmount))
		{
			foreach ($arAvAmount as $key => $val)
			{
				$arAmount[$key] = SaleFormatCurrency($val["AMOUNT"], $val["CURRENCY"]);
			}
		}
	}

	$arComponentParameters['PARAMETERS']['SELL_AMOUNT'] = array(
		"NAME"=>GetMessage("SAPP_SELL_AMOUNT"),
		"TYPE"=>"LIST",
		"MULTIPLE"=>"Y",
		"VALUES" => $arAmount,
		"COLS"=>25,
		"ADDITIONAL_VALUES"=>"N",
	);

	$arComponentParameters["PARAMETERS"]['REFRESHED_COMPONENT_MODE'] =  array(
		"NAME" => GetMessage("SAPP_REFRESHED_COMPONENT_MODE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"PARENT" => "BASE",
		"REFRESH" => "Y"
	);

	$arComponentParameters["PARAMETERS"]["REDIRECT_TO_CURRENT_PAGE"] = array(
		"NAME" => GetMessage("SAPP_REDIRECT_TO_CURRENT_PAGE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
	);

	$arComponentParameters["PARAMETERS"]["VAR"] = array(
		"NAME" => GetMessage("SAPP_VAR"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "buyMoney",
		"COLS" => 25,
	);

	$arComponentParameters["PARAMETERS"]['CALLBACK_NAME'] = array(
		"NAME" => GetMessage("SAPP_CALLBACK_NAME"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "PayUserAccountDeliveryOrderCallback",
		"COLS" => 25,
	);

	$arComponentParameters["PARAMETERS"]['SET_TITLE'] = array();
}
else
{
	if(empty($arCurrentValues['SELL_TOTAL']))
	{
		$valuesList = array(100,200,500,1000,5000);
	}
	else
	{
		$valuesList = $arCurrentValues['SELL_TOTAL'];
	}

	$paySystemList = array(GetMessage("SAPP_SHOW_ALL"));

	$paySystemManagerResult = Bitrix\Sale\PaySystem\Manager::getList(array('select' => array('ID','NAME')));

	while ($paySystem = $paySystemManagerResult->fetch())
	{
		if (!empty($paySystem['NAME']))
		{
			$paySystemList[$paySystem['ID']] = $paySystem['NAME'].' ['.$paySystem['ID'].']';
		}
	}

	if (isset($personTypes))
	{
		$arComponentParameters['PARAMETERS']['PERSON_TYPE'] = array(
			"NAME"=>GetMessage("SAPP_SELL_USER_TYPES"),
			"TYPE"=>"LIST",
			"MULTIPLE"=>"N",
			"VALUES"=>$personTypes,
			"DEFAULT" => "1",
			"SIZE" => count($personTypes),
			"COLS"=>25,
			"ADDITIONAL_VALUES"=>"N",
		);
	}

	if (isset($paySystemList))
	{
		$arComponentParameters['PARAMETERS']['ELIMINATED_PAY_SYSTEMS'] = array(
			"NAME"=>GetMessage("SAPP_ELIMINATED_PAY_SYSTEMS"),
			"TYPE"=>"LIST",
			"MULTIPLE"=>"Y",
			"DEFAULT" => "0",
			"VALUES"=>$paySystemList,
			"SIZE" => 6,
			"COLS"=>25,
			"ADDITIONAL_VALUES"=>"N",
		);
	}

	$arComponentParameters["PARAMETERS"]['REFRESHED_COMPONENT_MODE'] =  array(
		"NAME" => GetMessage("SAPP_REFRESHED_COMPONENT_MODE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"PARENT" => "BASE",
		"REFRESH" => "Y"
	);

	$arComponentParameters['PARAMETERS']['SELL_VALUES_FROM_VAR'] = array(
		"NAME"=>GetMessage("SAPP_SELL_VALUES_FROM_VAR"),
		"TYPE"=>"CHECKBOX",
		"MULTIPLE"=>"N",
		"DEFAULT" => "N",
		"ADDITIONAL_VALUES"=>"N",
		"REFRESH" => "Y"
	);

	if ($arCurrentValues['SELL_VALUES_FROM_VAR'] != 'Y')
	{
		$arComponentParameters['PARAMETERS']['SELL_SHOW_FIXED_VALUES'] = array(
			"NAME"=>GetMessage("SAPP_SELL_SHOW_FIXED_VALUES"),
			"TYPE"=>"CHECKBOX",
			"DEFAULT" => "Y",
			"REFRESH" => "Y"
		);

		if ($arCurrentValues['SELL_SHOW_FIXED_VALUES'] != 'N')
		{
			$arComponentParameters['PARAMETERS']['SELL_TOTAL'] = array(
				"NAME"=>GetMessage("SAPP_SELL_AMOUNT"),
				"TYPE"=>"STRING",
				"MULTIPLE"=>"Y",
				"DEFAULT" => $valuesList,
				"COLS"=>25,
				"ADDITIONAL_VALUES"=>"N",
			);
		}

		$arComponentParameters['PARAMETERS']['SELL_USER_INPUT'] = array(
			"NAME"=>GetMessage("SAPP_ACCEPT_USER_AMOUNT"),
			"TYPE"=>"CHECKBOX",
			"MULTIPLE"=>"N",
			"DEFAULT" => "Y",
			"ADDITIONAL_VALUES"=>"N",
		);
	}
	else
	{
		$arComponentParameters["PARAMETERS"]['SELL_VAR_PRICE_VALUE'] = array(
			"NAME" => GetMessage("SAPP_NAME_PRICE_VALUE"),
			"DEFAULT" => '={$_REQUEST["VALUE_OF_PAYMENT"]}',
			"MULTIPLE"=>"N",
			"COLS"=>25,
			"ADDITIONAL_VALUES"=>"N"
		);
		$arComponentParameters["PARAMETERS"]['SELL_SHOW_RESULT_SUM'] = array(
			"NAME"=>GetMessage("SAPP_SELL_SHOW_RESULT_SUM"),
			"TYPE"=>"CHECKBOX",
			"DEFAULT" => "Y",
		);
	}

	$arComponentParameters["PARAMETERS"]['SET_TITLE'] = array();
}
