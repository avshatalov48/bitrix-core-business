<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);


return [
	'code' => 'store-chats-dark',
	'name' => Loc::getMessage("LANDING_DEMO_STORE_CHATS_DARK--NAME"),
	'description' => Loc::getMessage("LANDING_DEMO_STORE_CHATS_DARK--DESC"),
	'active' => true,
	'singleton' => true,
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'Y',
	'type' => 'store',
	'sort' => 10,
	'lock_delete' => true,
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'VIEW_USE' => 'Y',
			'VIEW_TYPE' => 'mobile',
			'UP_SHOW' => 'Y',
			'THEME_CODE' => '3corporate',
			'THEMEFONTS_CODE' => 'Roboto',
			'THEMEFONTS_CODE_H' => 'Roboto',
			'THEMEFONTS_SIZE' => '1',
			'THEMEFONTS_USE' => 'Y',
			'BACKGROUND_USE' => 'Y',
			'BACKGROUND_COLOR' => '#000000',
		],
		'TITLE' => Loc::getMessage("LANDING_DEMO_STORE_CHATS_DARK--NAME"),
	],
	'layout' => [
		'code' => 'header_footer',
		'ref' => [
			1 => 'store-chats-dark/header',
			2 => 'store-chats-dark/footer',
		],
	],
	'syspages' => [
		'order' => 'store-chats-dark/order',
		'feedback' => 'store-chats-dark/order',
		// 'catalog' => 'store-chats-dark/catalog',
	],
	// todo: do not delete all this comments, please! This is for podborki
	// 'folders' => [
	// 	'store-chats-dark/catalog' => [
	// 		0 => 'store-chats-dark/catalog',
	// 		1 => 'store-chats-dark/catalog_order',
	// 	],
	// ],
	'items' => [
		0 => 'store-chats-dark/mainpage',
		1 => 'store-chats-dark/header',
		2 => 'store-chats-dark/footer',
		3 => 'store-chats-dark/contacts',
		4 => 'store-chats-dark/webform',
		5 => 'store-chats-dark/order',
		6 => 'store-chats-dark/about',
		7 => 'store-chats-dark/payinfo',
		8 => 'store-chats-dark/cutaway',
		// 9 => 'store-chats-dark/catalog',
		// 10 => 'store-chats-dark/catalog_order',
	],
];