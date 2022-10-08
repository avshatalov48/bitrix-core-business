<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'old_id' => 11,
	'code' => 'store-chats-dark/catalog_detail',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DETAIL-NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 3,
	'lock_delete' => true,
	'fields' => [
		'RULE' => '(item)/([^/]+)',
		'ADDITIONAL_FIELDS' => [
			'BACKGROUND_USE' => 'Y',
			'BACKGROUND_COLOR' => '#ffffff',
			'B24BUTTON_CODE' => 'N',
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/store_v3/detailpage/preview.jpg',
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
			'code' => '27.3.one_col_fix_title',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '#title#',
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block text-center container g-pb-25 g-pt-0 l-d-xs-none l-d-md-none',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title landing-semantic-title-medium g-my-0 container g-pl-0 g-pr-0 text-left g-font-size-30 g-font-weight-500',
				],
			],
		],
		2 => [
			'code' => 'store.catalog.detail_store_v3',
			'access' => 'W',
			'cards' => [],
			'nodes' => [],
			'style' => [],
			'attrs' => [
				'bitrix:catalog.element' => [],
			],
		],
	],
];