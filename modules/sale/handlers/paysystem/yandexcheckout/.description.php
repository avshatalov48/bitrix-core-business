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

$data = array(
	'NAME' => Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT'),
	'SORT' => 500,
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
		"YANDEX_CHECKOUT_RETURN_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_CHECKOUT_RETURN_URL"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_YANDEX_CHECKOUT_RETURN_URL_DESC"),
			'SORT' => 200,
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