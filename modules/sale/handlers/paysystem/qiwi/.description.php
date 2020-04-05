<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$isAvailable = \Bitrix\Sale\PaySystem\Manager::HANDLER_AVAILABLE_TRUE;

$licensePrefix = \Bitrix\Main\Loader::includeModule("bitrix24") ? \CBitrix24::getLicensePrefix() : "";
if (IsModuleInstalled("bitrix24") && !in_array($licensePrefix, ["ru"]))
{
	$isAvailable = \Bitrix\Sale\PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
}

$data = array(
	'NAME' => Loc::getMessage("SALE_HPS_QIWI_NAME"),
	'SORT' => 750,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => array(
		"QIWI_SHOP_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_QIWI_SHOP_ID"),
			'GROUP' => 'CONNECT_SETTINGS_QIWI',
			'SORT' => 100,

		),
		"QIWI_API_LOGIN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_QIWI_API_LOGIN"),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_QIWI',

		),
		"QIWI_API_PASSWORD" => array(
			"NAME" => Loc::getMessage("SALE_HPS_QIWI_API_PASS"),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_QIWI',

		),
		"QIWI_NOTICE_PASSWORD" => array(
			"NAME" => Loc::getMessage("SALE_HPS_QIWI_NOTICE_PASSWORD"),
			'SORT' => 400,
			'GROUP' => 'CONNECT_SETTINGS_QIWI',

		),
		"BUYER_PERSON_PHONE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_QIWI_CLIENT_PHONE"),
			'SORT' => 500,
			'GROUP' => 'BUYER_PERSON',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "PHONE",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"PAYMENT_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_QIWI_ORDER_ID"),
			'SORT' => 600,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "ID",
				"PROVIDER_KEY" => "PAYMENT"
			)
		),
		"PAYMENT_SHOULD_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_QIWI_SHOULD_PAY"),
			'SORT' => 700,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "SUM",
				"PROVIDER_KEY" => "PAYMENT"
			)
		),
		"PAYMENT_CURRENCY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_QIWI_CURRENCY"),
			'SORT' => 800,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "CURRENCY",
				"PROVIDER_KEY" => "PAYMENT"
			)
		),
		"QIWI_BILL_LIFETIME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_QIWI_BILL_LIFETIME"),
			'SORT' => 900,
			'GROUP' => 'CONNECT_SETTINGS_QIWI',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "240",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"QIWI_AUTHORIZATION" => array(
			"NAME" => Loc::getMessage("SALE_HPS_QIWI_AUTHORIZATION"),
			'SORT' => 1000,
			'GROUP' => 'CONNECT_SETTINGS_QIWI',
			'DEFAULT' => array(
				"PROVIDER_KEY" => "VALUE",
				'PROVIDER_VALUE' => "OPEN",
			)
		),
		"QIWI_SUCCESS_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_QIWI_SUCCESS_URL"),
			'SORT' => 1100,
			'GROUP' => 'CONNECT_SETTINGS_QIWI',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "http://{$host}/personal/order/",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"QIWI_FAIL_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_QIWI_FAIL_URL"),
			'SORT' => 1200,
			'GROUP' => 'CONNECT_SETTINGS_QIWI',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "http://{$host}/personal/order/",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"PS_CHANGE_STATUS_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_QIWI_CHANGE_STATUS_PAY"),
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			)
		)
	)
);
?>
