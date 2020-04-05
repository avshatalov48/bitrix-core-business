<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$isAvailable = \Bitrix\Sale\PaySystem\Manager::HANDLER_AVAILABLE_TRUE;

$licensePrefix = \Bitrix\Main\Loader::includeModule("bitrix24") ? \CBitrix24::getLicensePrefix() : "";
if (IsModuleInstalled("bitrix24") && in_array($licensePrefix, ["ua", "ru", "by", "kz"]))
{
	$isAvailable = \Bitrix\Sale\PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
}

$data = array(
	'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_TITLE'),
	'SORT' => 100,
	'IS_AVAILABLE' => $isAvailable,
	'CODES' => array(
		'AUTHORIZE_LOGIN' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_LOGIN'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_LOGIN_DESC'),
			'SORT' => 100,
			'GROUP' => 'CONNECT_SETTINGS_AUTHORIZE',
		),
		'AUTHORIZE_TRANSACTION_KEY' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_TRANSACTION_KEY'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_TRANSACTION_KEY_DESC'),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_AUTHORIZE',
		),
		'AUTHORIZE_SECRET_KEY' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_SECRET_KEY'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_SECRET_KEY_DESC'),
			'SORT' => 200,
			'GROUP' => 'CONNECT_SETTINGS_AUTHORIZE',
		),
		'PAYMENT_ID' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_PAYMENT_ID'),
			'SORT' => 300,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'ACCOUNT_NUMBER'
			)
		),
		'PAYMENT_SHOULD_PAY' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_SHOULD_PAY'),
			'SORT' => 400,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'SUM'
			)
		),
		'PS_CHANGE_STATUS_PAY' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_CHANGE_STATUS_PAY'),
			'SORT' => 500,
			'GROUP' => 'GENERAL_SETTINGS',
			'INPUT' => array(
				'TYPE' => 'Y/N'
			),
		),
		'PS_IS_TEST' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_IS_TEST'),
			'SORT' => 600,
			'GROUP' => 'GENERAL_SETTINGS',
			'INPUT' => array(
				'TYPE' => 'Y/N'
			)
		),
		'BUYER_PERSON_NAME_FIRST' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_FIRST_NAME_NAME'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_FIRST_NAME_DESCR'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
		),
		'BUYER_PERSON_NAME_LAST' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_LAST_NAME_NAME'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_LAST_NAME_DESCR'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
		),
		'BUYER_PERSON_COMPANY_NAME' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_COMPANY_NAME'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_COMPANY_DESCR'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
		),
		'BUYER_PERSON_ADDRESS' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_ADDRESS_NAME'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_ADDRESS_DESCR'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
		),
		'BUYER_PERSON_CITY' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_CITY_NAME'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_CITY_DESCR'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
		),
		'BUYER_PERSON_STATE' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_STATE_NAME'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_STATE_DESCR'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
		),
		'BUYER_PERSON_ZIP' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_ZIP_NAME'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_ZIP_DESCR'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
		),
		'BUYER_PERSON_COUNTRY' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_COUNTRY_NAME'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_COUNTRY_DESCR'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
		),
		'BUYER_PERSON_PHONE' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_PHONE_NAME'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_PHONE_DESCR'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
		),
		'BUYER_PERSON_FAX' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_FAX_NAME'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_FAX_DESCR'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
		),
		'BUYER_PERSON_EMAIL' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_EMAIL_NAME'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_EMAIL_DESCR'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
		),
		'SHIP_BUYER_PERSON_NAME_FIRST' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_SHIP_FIRST_NAME_NAME'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_SHIP_FIRST_NAME_DESCR'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
		),
		'SHIP_BUYER_PERSON_NAME_LAST' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_SHIP_LAST_NAME_NAME'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_SHIP_LAST_NAME_DESCR'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
		),
		'SHIP_BUYER_PERSON_COMPANY' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_SHIP_COMPANY_NAME'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_SHIP_COMPANY_DESCR'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
		),
		'SHIP_BUYER_PERSON_ADDRESS' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_SHIP_ADDRESS_NAME'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_SHIP_ADDRESS_DESCR'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
		),
		'SHIP_BUYER_PERSON_CITY' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_SHIP_CITY_NAME'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_SHIP_CITY_DESCR'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
		),
		'SHIP_BUYER_PERSON_STATE' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_SHIP_STATE_NAME'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_SHIP_STATE_DESCR'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
		),
		'SHIP_BUYER_PERSON_ZIP' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_SHIP_ZIP_NAME'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_SHIP_ZIP_DESCR'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
		),
		'SHIP_BUYER_PERSON_COUNTRY' => array(
			'NAME' => Loc::getMessage('SALE_HPS_AUTHORIZE_SHIP_COUNTRY_NAME'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_AUTHORIZE_SHIP_COUNTRY_DESCR'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
		)
	)
);
?>