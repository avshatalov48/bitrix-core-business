<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);


return array(
	'code' => 'store-instagram',
	'name' => Loc::getMessage("LANDING_DEMO_STORE_INSTAGRAM--NAME"),
	'description' => Loc::getMessage("LANDING_DEMO_STORE_INSTAGRAM--DESC"),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'Y',
	'type' => 'store',
	'sort' => 10,
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'UP_SHOW' => 'Y',
			'THEME_CODE' => '1construction',
			'THEMEFONTS_CODE' => 'Roboto',
			'THEMEFONTS_CODE_H' => 'Montserrat',
			'THEMEFONTS_SIZE' => '1',
			'THEMEFONTS_USE' => 'Y',
		),
		'TITLE' => Loc::getMessage("LANDING_DEMO_STORE_INSTAGRAM--NAME"),
	),
	'layout' => array(
		'code' => 'header_footer',
		'ref' => array(
			1 => 'store-instagram/header',
			2 => 'store-instagram/footer',
		),
	),
	'folders' => array(),
	'syspages' => array(
		'order' => 'store-instagram/checkout',
		'cart' => 'store-instagram/cart',
		'payment' => 'store-instagram/payment',
	),
	'items' => array(
		0 => 'store-instagram/mainpage',
		1 => 'store-instagram/checkout',
		2 => 'store-instagram/payment',
		3 => 'store-instagram/cart',
		4 => 'store-instagram/header',
		5 => 'store-instagram/header_main',
		6 => 'store-instagram/footer',
	),
	'active' => \LandingSiteDemoComponent::checkActive([
		'ONLY_IN' => [],
		'EXCEPT' => ['ru'],
	]),
);