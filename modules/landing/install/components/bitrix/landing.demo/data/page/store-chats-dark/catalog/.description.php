<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'old_id' => 10,
	'code' => 'store-chats-dark/catalog',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_CATALOG-NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 3,
	'fields' => [
		'RULE' => '(.*?)',
		'ADDITIONAL_FIELDS' => [
			'BACKGROUND_USE' => 'Y',
			'BACKGROUND_COLOR' => '#ffffff',
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/store_v3/catalog/preview.jpg',
			'VIEW_USE' => 'Y',
			'VIEW_TYPE' => 'adaptive',
			'CSSBLOCK_USE' => 'Y',
			'CSSBLOCK_CODE' =>
				'@media (min-width: 1200px) {'
				. '.landing-viewtype--adaptive .landing-layout-flex,'
				. '.landing-viewtype--adaptive .landing-header + .landing-main {'
				. 'max-width: 960px;'
				. '}'
				. '@media (min-width: 992px) {'
				. '.landing-viewtype--adaptive .landing-header .container,'
				. '.landing-viewtype--adaptive .landing-footer .container {'
				. 'max-width: 960px;'
				. '}',
		],
	],
	'layout' => [
		'code' => 'header_footer',
		'ref' => [
			1 => 'store-chats-dark/catalog_header',
			2 => 'store-chats-dark/catalog_footer',
		],
	],
	'items' => [
		1 => [
			'code' => 'store.catalog.compilation',
			'access' => 'W',
			'style' => [
				'.landing-component' => [
					0 => 'landing-component',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-0 g-pb-0',
				],
			],
			'attrs' => [
				'bitrix:catalog.section' => [],
			],
		],
	],
];