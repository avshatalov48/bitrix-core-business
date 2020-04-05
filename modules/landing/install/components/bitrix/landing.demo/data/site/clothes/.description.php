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
	'code' => 'clothes',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CLOTHES-SITE--NAME'),
	'description' => Loc::getMessage('LANDING_DEMO_STORE_CLOTHES-SITE--DESC'),
	'type' => 'store',
	'sort' => 40,
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'B24BUTTON_CODE' => $buttons[0],
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'ltr',
			'UP_SHOW' => 'Y',
			'THEME_CODE' => 'travel',
			'THEME_CODE_TYPO' => 'travel',
		),
	),
	'layout' => array(
		'code' => 'header_footer',
		'ref' => array(
			1 => 'clothes/header',
			2 => 'clothes/footer',
		),
	),
	'folders' => array(
		'clothes/catalog' => array(
			0 => 'clothes/detailpage',
			1 => 'clothes/filter',
			2 => 'clothes/compare',
		),
		'clothes/personal' => array(
			0 => 'clothes/cart',
			1 => 'clothes/checkout',
			2 => 'clothes/menu',
			3 => 'clothes/payment',
		),
	),
	'syspages' => array(
		'mainpage' => 'clothes/mainpage',
		'catalog' => 'clothes/catalog',
		'personal' => 'clothes/personal',
		'cart' => 'clothes/cart',
		'order' => 'clothes/checkout',
		'compare' => 'clothes/compare',
		'payment' => 'clothes/payment',
	),
	'items' => array(
		0 => 'clothes/mainpage',
		1 => 'clothes/catalog',
		2 => 'clothes/detailpage',
		3 => 'clothes/personal',
		4 => 'clothes/cart',
		5 => 'clothes/checkout',
		6 => 'clothes/delivery',
		7 => 'clothes/faq',
		8 => 'clothes/about',
		9 => 'clothes/contacts',
		10 => 'clothes/footer',
		11 => 'clothes/filter',
		12 => 'clothes/header',
		13 => 'clothes/menu',
		14 => 'clothes/news1',
		15 => 'clothes/news2',
		16 => 'clothes/news3',
		17 => 'clothes/guarantee',
		18 => 'clothes/header_main',
		19 => 'clothes/compare',
		20 => 'clothes/payment',
	),
);