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
	'code' => 'store-mini-one-element/',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_MINI_ONE_ELEMENT_SITE_TXT_1'),
	'description' => Loc::getMessage('LANDING_DEMO_STORE_MINI_ONE_ELEMENT_SITE_DESC'),
	'type' => 'store',
	'sort' => 20,
	'fields' =>array(
			'ADDITIONAL_FIELDS' =>array(
					'B24BUTTON_CODE' => $buttons[0],
					'VIEW_USE' => 'N',
					'VIEW_TYPE' => 'no',
					'UP_SHOW' => 'Y',
					'THEME_CODE' => 'event',
					'THEME_CODE_TYPO' => 'event',
				),
		),
	'layout' => array(),
	'folders' =>array(),
	'syspages' =>array(
			'order' => 'store-mini-one-element/buying',
			'cart' => 'store-mini-one-element/buying',
			'payment' => 'store-mini-one-element/payment',
		),
	'items' =>array(
			0 => 'store-mini-one-element/handmade',
			1 => 'store-mini-one-element/buying',
			2 => 'store-mini-one-element/payment',
		),
);