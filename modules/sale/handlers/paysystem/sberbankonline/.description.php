<?php
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

$description = array(
	'MAIN' => Loc::getMessage('SALE_HPS_SBERBANK_DESCRIPTION_MAIN'),
);

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$host = $request->isHttps() ? 'https' : 'http';

$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_TRUE;

$portalZone = Loader::includeModule('intranet') ? CIntranetUtils::getPortalZone() : "";
$licensePrefix = Loader::includeModule('bitrix24') ? \CBitrix24::getLicensePrefix() : "";

if (
	(Loader::includeModule('intranet') && $portalZone !== 'ru')
	|| (Loader::includeModule("bitrix24") && $licensePrefix !== 'ru')
)
{
	$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
}

$data = array(
	'NAME' => Loc::getMessage('SALE_HPS_SBERBANK'),
	'SORT' => 500,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => array(
		"SBERBANK_LOGIN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_SBERBANK_LOGIN"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_SBERBANK_LOGIN_DESC"),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_SBERBANK',
		),
		"SBERBANK_PASSWORD" => array(
			"NAME" => Loc::getMessage("SALE_HPS_SBERBANK_PASSWORD"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_SBERBANK_PASSWORD_DESC"),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_SBERBANK'
		),
		"SBERBANK_SECRET_KEY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_SBERBANK_SECRET_KEY"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_SBERBANK_SECRET_KEY_DESC"),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_SBERBANK'
		),
		"SBERBANK_RETURN_SUCCESS_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_SBERBANK_RETURN_SUCCESS_URL"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_SBERBANK_RETURN_SUCCESS_URL_DESC"),
			'SORT' => 400,
			'GROUP' => 'CONNECT_SETTINGS_SBERBANK',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => $host.'://'.$request->getHttpHost().'/bitrix/tools/sale_ps_success.php',
			)
		),
		"SBERBANK_RETURN_FAIL_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_SBERBANK_RETURN_FAIL_URL"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_SBERBANK_RETURN_FAIL_URL_DESC"),
			'SORT' => 500,
			'GROUP' => 'CONNECT_SETTINGS_SBERBANK',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => $host.'://'.$request->getHttpHost().'/bitrix/tools/sale_ps_fail.php',
			)
		),
		"SBERBANK_ORDER_DESCRIPTION" => array(
			"NAME" => Loc::getMessage("SALE_HPS_SBERBANK_ORDER_DESCRIPTION"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_SBERBANK_ORDER_DESCRIPTION_DESC"),
			'SORT' => 600,
			'GROUP' => 'CONNECT_SETTINGS_SBERBANK',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'VALUE',
					'PROVIDER_VALUE' => Loc::getMessage("SALE_HPS_SBERBANK_ORDER_DESCRIPTION_TEMPLATE"),
			)
		),
		"SBERBANK_TEST_MODE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_SBERBANK_TEST_MODE"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_SBERBANK_TEST_MODE_DESC"),
			'SORT' => 700,
			'GROUP' => 'CONNECT_SETTINGS_SBERBANK',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
		),
		"PS_CHANGE_STATUS_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_SBERBANK_CHANGE_STATUS_PAY"),
			'SORT' => 800,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => array(
				"PROVIDER_KEY" => "INPUT",
				"PROVIDER_VALUE" => "Y",
			)
		),
	)
);