<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

$buttons = \Bitrix\Landing\Hook\Page\B24button::getButtons();
$buttons = array_keys($buttons);

return array(
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
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'B24BUTTON_CODE' => $buttons[0],
			'VIEW_USE' => 'Y',
			'VIEW_TYPE' => 'mobile',
			'UP_SHOW' => 'Y',
			'THEME_CODE' => '3corporate',
			'THEME_CODE_TYPO' => '3corporate',
			'BACKGROUND_USE' => 'Y',
			'BACKGROUND_COLOR' => '#2C2C36',
		),
		'TITLE' => Loc::getMessage("LANDING_DEMO_STORE_CHATS_DARK--NAME"),
	),
	'layout' => array(
		'code' => 'header_footer',
		'ref' => array(
			1 => 'store-chats-dark/header',
			2 => 'store-chats-dark/footer',
		),
	),
	'syspages' => array(
		'order' => 'store-chats-dark/order',
	),
	'items' => array(
		0 => 'store-chats-dark/mainpage',
		1 => 'store-chats-dark/header',
		2 => 'store-chats-dark/footer',
		3 => 'store-chats-dark/contacts',
		4 => 'store-chats-dark/webform',
		5 => 'store-chats-dark/order',
		6 => 'store-chats-dark/about',
		7 => 'store-chats-dark/payinfo',
	),
	'site_group_item' => 'Y',
	'site_group_parent' => 'store-chats',
);