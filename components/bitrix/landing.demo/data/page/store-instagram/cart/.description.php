<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'code' => 'store-instagram/cart',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_INSTAGRAM--CART--NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'RULE' => NULL,
		'ADDITIONAL_FIELDS' => array(
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => 'travel',
			'THEMEFONTS_CODE' => 'g-font-roboto',
			'THEMEFONTS_CODE_H' => 'g-font-roboto',
			'THEMEFONTS_SIZE' => '1',
			'THEMEFONTS_USE' => 'Y',
		),
	),
	'layout' => array(
		'code' => 'header_footer',
		'ref' => array(
			1 => 'store-instagram/header',
			2 => 'store-instagram/footer',
		),
	),
	'items' => array(
		0 => array(
			'code' => 'store.cart',
			'cards' => array(),
			'nodes' => array(),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block g-bg-white g-pt-30 g-pb-10',
				),
			),
			'attrs' => array(),
		),
	),
);