<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PaySystem;

$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_TRUE;

$licensePrefix = Loader::includeModule('bitrix24') ? \CBitrix24::getLicensePrefix() : '';
$portalZone = Application::getInstance()->getLicense()->getRegion();

if (Loader::includeModule('bitrix24'))
{
	if ($licensePrefix !== 'ru')
	{
		$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
	}
}
elseif (!Loader::includeModule('crm') || $portalZone !== 'ru')
{
	$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
}

$description = [
	'MAIN' => Loc::getMessage('SALE_HPS_TBANK_BUSINESS_DESCRIPTION_MAIN'),
];

$data = [
	'NAME' => Loc::getMessage('SALE_HPS_TBANK_BUSINESS_NAME_MSGVER_1'),
	'DESCRIPTION' => Loc::getMessage('SALE_HPS_TBANK_BUSINESS_DESCRIPTION_MAIN'),
	'PUBLIC_DESCRIPTION' => Loc::getMessage('SALE_HPS_TBANK_BUSINESS_DESCRIPTION_PUBLIC'),
	'SORT' => 1500,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => [
		'TBB_TEST_MODE' => [
			'NAME' => Loc::getMessage('SALE_HPS_TBANK_TBB_TEST_MODE'),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_TBB',
			'INPUT' => [
				'TYPE' => 'Y/N'
			],
			'DEFAULT' => [
				'PROVIDER_KEY' => 'INPUT',
				'PROVIDER_VALUE' => 'N',
			],
		],
		'TBB_COMMENT_TEMPLATE' => [
			'NAME' => Loc::getMessage('SALE_HPS_TBANK_TBB_COMMENT_TEMPLATE'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_TBANK_TBB_COMMENT_TEMPLATE_DESCRIPTION'),
			'SORT' => 1000,
			'GROUP' => 'CONNECT_SETTINGS_TBB',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_TBANK_TBB_COMMENT_TEMPLATE_DEFAULT_VALUE'),
			],
		],
	],
];
