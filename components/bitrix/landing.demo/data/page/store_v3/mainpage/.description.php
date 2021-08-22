<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'parent' => 'store_v3',
	'code' => 'store_v3/mainpage',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_MAINPAGE_TITLE_NEW'),
	'description' => Loc::getMessage('LANDING_DEMO_STORE_MAINPAGE_DESC_NEW'),
	'type' => 'store',
	'version' => 3,
	'fields' => [
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/store_v3/mainpage/preview.jpg',
		],
	],
	'layout' => [
		'code' => 'without_right',
		'ref' => [
			1 => 'store_v3/header',
			2 => 'store_v3/sidebar',
			3 => 'store_v3/footer',
		],
	],
	'items' => [
		0 => [
			'code' => 'store.catalog.list_store_v3',
			'cards' => [],
			'nodes' => [
				'bitrix:catalog.section' => [
					'CYCLIC_LOADING' => 'Y',
					'SECTIONS_OFFSET_MODE' => 'F',
				],
			],
			'style' => [
				'.landing-component' => [
					0 => 'landing-component',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-0 g-pt-20--sm g-pb-40',
				],
			],
		],
	],
];