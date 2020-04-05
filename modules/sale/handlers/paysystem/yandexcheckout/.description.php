<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$description = array(
	'RETURN' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_RETURN'),
	'RESTRICTION' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_RESTRICTION'),
	'COMMISSION' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_COMMISSION'),
	'MAIN' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_DESCRIPTION'),
);

if (IsModuleInstalled('bitrix24'))
{
	$description['REFERRER'] = Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_REFERRER');
}

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$host = $request->isHttps() ? 'https' : 'http';

$isAvailable = \Bitrix\Sale\PaySystem\Manager::HANDLER_AVAILABLE_TRUE;

$licensePrefix = \Bitrix\Main\Loader::includeModule("bitrix24") ? \CBitrix24::getLicensePrefix() : "";
if (IsModuleInstalled("bitrix24") && !in_array($licensePrefix, ["ru"]))
{
	$isAvailable = \Bitrix\Sale\PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
}

$data = array(
	'NAME' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT'),
	'SORT' => 500,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => array(
		"YANDEX_CHECKOUT_SHOP_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_CHECKOUT_SHOP_ID"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_YANDEX_CHECKOUT_SHOP_ID_DESC"),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_YANDEX',
		),
		"YANDEX_CHECKOUT_SECRET_KEY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_CHECKOUT_SECRET_KEY"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_YANDEX_CHECKOUT_SECRET_KEY_DESC"),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_YANDEX'
		),
		"YANDEX_CHECKOUT_SHOP_ARTICLE_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_CHECKOUT_SHOP_ARTICLE_ID"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_YANDEX_CHECKOUT_SHOP_ARTICLE_ID_DESC"),
			'SORT' => 250,
			'GROUP' => 'CONNECT_SETTINGS_YANDEX'
		),
		"YANDEX_CHECKOUT_DESCRIPTION" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_CHECKOUT_PAYMENT_DESCRIPTION"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_YANDEX_CHECKOUT_PAYMENT_DESCRIPTION_DESC"),
			'SORT' => 250,
			'GROUP' => 'CONNECT_SETTINGS_YANDEX',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => Loc::getMessage("SALE_HPS_YANDEX_CHECKOUT_PAYMENT_DESCRIPTION_TEMPLATE"),
			)
		),
		"YANDEX_CHECKOUT_RETURN_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_CHECKOUT_RETURN_URL"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_YANDEX_CHECKOUT_RETURN_URL_DESC"),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_YANDEX',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => $host.'://'.$request->getHttpHost().'/'
			)
		),
		"PS_CHANGE_STATUS_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_CHECKOUT_CHANGE_STATUS_PAY"),
			'SORT' => 400,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
		),
	)
);