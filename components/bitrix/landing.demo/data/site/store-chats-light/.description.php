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
	'code' => 'store-chats-light',
	'name' => Loc::getMessage("LANDING_DEMO_STORE_CHATS_LIGHT--NAME"),
	'description' => Loc::getMessage("LANDING_DEMO_STORE_CHATS_LIGHT--DESC"),
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
			'THEMEFONTS_CODE' => 'g-font-roboto',
			'THEMEFONTS_CODE_H' => 'g-font-roboto',
			'THEMEFONTS_SIZE' => '1',
			'THEMEFONTS_USE' => 'Y',
			'BACKGROUND_USE' => 'Y',
			'BACKGROUND_COLOR' => '#F6F6F9',
		),
		'TITLE' => Loc::getMessage("LANDING_DEMO_STORE_CHATS_LIGHT--NAME"),
	),
	'layout' => array(
		'code' => 'header_footer',
		'ref' => array(
			1 => 'store-chats-light/header',
			2 => 'store-chats-light/footer',
		),
	),
	'syspages' => array(
		'order' => 'store-chats-light/order',
	),
	'items' => array(
		0 => 'store-chats-light/mainpage',
		1 => 'store-chats-light/header',
		2 => 'store-chats-light/footer',
		3 => 'store-chats-light/contacts',
		4 => 'store-chats-light/webform',
		5 => 'store-chats-light/order',
		6 => 'store-chats-light/about',
		7 => 'store-chats-light/payinfo',
		8 => 'store-chats-light/cutaway',
	),
	'site_group_item' => 'Y',
	'site_group_parent' => 'store-chats',
);