<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$description = array(
	'RETURN' => Loc::getMessage('SALE_HPS_YANDEX_RETURN'),
	'RESTRICTION' => Loc::getMessage('SALE_HPS_YANDEX_RESTRICTION'),
	'COMMISSION' => Loc::getMessage('SALE_HPS_YANDEX_COMMISSION'),
	'MAIN' => Loc::getMessage('SALE_HPS_YANDEX_DESCRIPTION')
);

if (IsModuleInstalled('bitrix24'))
{
	$description['REFERRER'] = Loc::getMessage('SALE_HPS_YANDEX_REFERRER');
}

$data = array(
	'NAME' => Loc::getMessage('SALE_HPS_YANDEX'),
	'SORT' => 500,
	'CODES' => array(
		"YANDEX_SHOP_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_SHOP_ID"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_YANDEX_SHOP_ID_DESC"),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_YANDEX',
		),
		"YANDEX_SCID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_SCID"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_YANDEX_SCID_DESC"),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_YANDEX',
		),
		"YANDEX_SHOP_KEY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_SHOP_KEY"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_YANDEX_SHOP_KEY_DESC"),
			'SORT' => 300,
			'GROUP' => 'CONNECT_SETTINGS_YANDEX',
		),
		"PAYMENT_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_PAYMENT_ID"),
			'SORT' => 400,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'ACCOUNT_NUMBER'
			)
		),
		"PAYMENT_DATE_INSERT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_PAYMENT_DATE"),
			'SORT' => 500,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'DATE_BILL'
			)
		),
		"PAYMENT_SHOULD_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_SHOULD_PAY"),
			'SORT' => 600,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'SUM'
			)
		),
		"PS_CHANGE_STATUS_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_CHANGE_STATUS_PAY"),
			'SORT' => 700,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
		),
		"PS_IS_TEST" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_IS_TEST"),
			'SORT' => 900,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			)
		),
		"PAYMENT_BUYER_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_YANDEX_BUYER_ID"),
			'SORT' => 1000,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'ORDER',
				'PROVIDER_VALUE' => 'USER_ID'
			)
		),
	)
);