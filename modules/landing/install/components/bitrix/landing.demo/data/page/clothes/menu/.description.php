<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'code' => 'clothes/menu',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CLOTHES-MENU--NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'RULE' => NULL,
		'ADDITIONAL_FIELDS' => array(
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/clothes/menu/preview.jpg',
			'VIEW_USE' => 'Y',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => 'travel',
			'THEME_CODE_TYPO' => 'travel',
		),
	),
	'layout' => array(
		'code' => 'empty',
		'ref' => array(),
	),
	'items' => array(
		0 => array(
			'code' => 'store.personal.menu',
			'cards' => array(),
			'nodes' => array(),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block g-pt-30 g-pb-20 g-pl-20 g-pr-20',
				),
			),
			'attrs' => array(),
		),
	),
);