<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);


return array(
	'code' => 'store-mini-one-element/',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_MINI_ONE_ELEMENT_SITE_TXT_1'),
	'description' => Loc::getMessage('LANDING_DEMO_STORE_MINI_ONE_ELEMENT_SITE_DESC'),
	'type' => 'store',
	'sort' => 20,
	'fields' =>array(
			'ADDITIONAL_FIELDS' =>array(
					'VIEW_USE' => 'N',
					'VIEW_TYPE' => 'no',
					'UP_SHOW' => 'Y',
					'THEME_CODE' => 'event',
					'THEMEFONTS_CODE' => 'Open Sanss',
					'THEMEFONTS_CODE_H' => 'Cormorant Infant',
					'THEMEFONTS_SIZE' => '1.14286',
					'THEMEFONTS_USE' => 'Y',
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