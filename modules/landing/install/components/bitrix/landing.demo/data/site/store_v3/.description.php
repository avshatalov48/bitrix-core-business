<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);


return [
	'code' => 'store_v3',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_SITE_NAME_NEW'),
	'description' => Loc::getMessage('LANDING_DEMO_STORE_SITE_DESCRIPTION_NEW'),
	'active' => Manager::isB24(),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'show_in_list' => 'Y',
	'type' => 'store',
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'BACKGROUND_USE' => 'Y',
			'BACKGROUND_COLOR' => '#ffffff',
			'B24BUTTON_COLOR' => 'custom',
			'B24BUTTON_COLOR_VALUE' => '#6cb70e',
			'UP_SHOW' => 'N',
			'THEME_COLOR' => '#333333',
			'THEMEFONTS_CODE_H' => 'Roboto',
			'THEMEFONTS_CODE' => 'Roboto',
			'THEMEFONTS_SIZE' => '1.14286',
			'THEMEFONTS_USE' => 'Y',
			'VIEW_USE' => 'Y',
			'VIEW_TYPE' => 'adaptive',
			'LAYOUT_BREAKPOINT' => 'desktop',
			'CSSBLOCK_USE' => 'Y',
			'CSSBLOCK_CODE' =>
				'@media (max-width: 990px) {'
				.'.b24-widget-button-position-bottom-right{right: 16px;bottom:16px;}'
				.'.b24-widget-button-position-top-right{right: 16px;top:16px;}'
				.'.b24-widget-button-position-bottom-left{left: 16px;bottom:16px;}'
				.'.b24-widget-button-position-top-left{left: 16px;top:16px;}'
				.'}',
		],
	],
	'layout' => [],
	'folders' => [
		'store_v3/technicalpages' => [
			0 => 'store_v3/footer',
			1 => 'store_v3/header',
			2 => 'store_v3/header2',
			3 => 'store_v3/sidebar',
		],
		'store_v3/catalog' => [
			0 => 'store_v3/detailpage',
		],
	],
	'syspages' => [
		'mainpage' => 'store_v3/mainpage',
		'catalog' => 'store_v3/catalog',
		'order' => 'store_v3/checkout',
		'payment' => 'store_v3/payment',
		'cart' => 'store_v3/checkout',
		'payinfo' => 'store_v3/payinfo',
		'feedback' => 'store_v3/cutaway',
	],
	'master_pages' => [
		'store_v3/contacts',
		'store_v3/payinfo',
	],
	'items' => [
		0 => 'store_v3/mainpage',
		1 => 'store_v3/sidebar',
		2 => 'store_v3/header2',
		3 => 'store_v3/header',
		4 => 'store_v3/footer',
		5 => 'store_v3/detailpage',
		6 => 'store_v3/technicalpages',
		7 => 'store_v3/catalog',
		8 => 'store_v3/cutaway',
		9 => 'store_v3/contacts',
		10 => 'store_v3/payinfo',
		11 => 'store_v3/payment',
		12 => 'store_v3/checkout',
	],
];