<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'code' => 'clothes/personal',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CLOTHES-PERSONAL--NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'RULE' => NULL,
		'ADDITIONAL_FIELDS' => array(
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/clothes/personal/preview.jpg',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => 'travel',
			'THEME_CODE_TYPO' => 'travel',
		),
	),
	'layout' => array(
		'code' => 'without_left',
		'ref' => array(
			1 => 'clothes/header',
			2 => 'clothes/menu',
			3 => 'clothes/footer',
		),
	),
	'items' => array(
		0 => array(
			'code' => 'store.personal',
			'cards' => array(),
			'nodes' => array(),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block g-pt-30 g-pb-30',
				),
			),
			'attrs' => array(),
		),
	),
);