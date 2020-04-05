<?php
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_TRUE;

$portalZone = Loader::includeModule('intranet') ? CIntranetUtils::getPortalZone() : "";
$licensePrefix = Loader::includeModule('bitrix24') ? \CBitrix24::getLicensePrefix() : "";

if (
	(Loader::includeModule('intranet') && $portalZone !== 'ua')
	|| (Loader::includeModule("bitrix24") && $licensePrefix !== 'ua')
)
{
	$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
}

$data = array(
	'NAME' => 'LiqPay',
	'SORT' => 400,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => array(
		"LIQPAY_MERCHANT_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_LIQPAY_MERCHANT_ID"),
			'GROUP' => 'CONNECT_SETTINGS_LIQPAY',
			'SORT' => 100,
		),
		"LIQPAY_SIGN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_LIQPAY_SIGN"),
			'GROUP' => 'CONNECT_SETTINGS_LIQPAY',
			'SORT' => 200,
		),
		"LIQPAY_PATH_TO_RESULT_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_LIQPAY_PATH_TO_RESULT_URL"),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_LIQPAY',
			"DEFAULT" => array(
				"PROVIDER_VALUE" => (!IsModuleInstalled('crm')) ? "https://".$_SERVER["HTTP_HOST"]."/personal/orders/" : '',
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"LIQPAY_PATH_TO_SERVER_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_LIQPAY_PATH_TO_SERVER_URL"),
			'SORT' => 400,
			'GROUP' => 'CONNECT_SETTINGS_LIQPAY',
			"DEFAULT" => array(
				"PROVIDER_VALUE" => "https://".$_SERVER["HTTP_HOST"]."/bitrix/tools/sale_ps_result.php",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"PAYMENT_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_LIQPAY_ORDER_ID"),
			'SORT' => 500,
			'GROUP' => 'PAYMENT',
			"DEFAULT" => array(
				"PROVIDER_VALUE" => "ID",
				"PROVIDER_KEY" => "PAYMENT"
			)
		),
		"PAYMENT_CURRENCY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_LIQPAY_CURRENCY"),
			'SORT' => 600,
			'GROUP' => 'PAYMENT',
			"DEFAULT" => array(
				"PROVIDER_VALUE" => "CURRENCY",
				"PROVIDER_KEY" => "PAYMENT"
			)
		),
		"PAYMENT_SHOULD_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_LIQPAY_SHOULD_PAY"),
			'SORT' => 700,
			'GROUP' => 'PAYMENT',
			"DEFAULT" => array(
				"PROVIDER_VALUE" => "SUM",
				"PROVIDER_KEY" => "PAYMENT"
			)
		),
		"BUYER_PERSON_PHONE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_LIQPAY_PHONE"),
			'SORT' => 800,
			'GROUP' => 'BUYER_PERSON',
			"DEFAULT" => array(
				"PROVIDER_VALUE" => "PHONE",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"LIQPAY_PAY_METHOD" => array(
			"NAME" => Loc::getMessage("SALE_HPS_LIQPAY_PAYMENT_PM"),
			'SORT' => 900,
			'GROUP' => 'CONNECT_SETTINGS_LIQPAY'
		),
		"LIQPAY_PAYMENT_DESCRIPTION" => array(
			"NAME" => Loc::getMessage("SALE_HPS_LIQPAY_PAYMENT_DESCRIPTION"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_LIQPAY_PAYMENT_DESCRIPTION_DESC"),
			'SORT' => 1000,
			'GROUP' => 'CONNECT_SETTINGS_LIQPAY',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => Loc::getMessage("SALE_HPS_LIQPAY_PAYMENT_DESCRIPTION_TEMPLATE"),
			)
		),
	)
);

