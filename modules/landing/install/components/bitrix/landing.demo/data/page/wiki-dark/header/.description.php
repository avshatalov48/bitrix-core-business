<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'old_id' => '228',
	'code' => 'wiki-dark/header',
	'name' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_HEADER"),
	'description' => null,
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'N',
	'active' => false,
	'type' => ['knowledge', 'group'],
	'version' => 3,
	'fields' => [
		'TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_HEADER"),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'METAMAIN_USE' => 'N',
			'METAMAIN_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_HEADER"),
			'METAMAIN_DESCRIPTION' => '',
			'METAOG_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_HEADER"),
			'METAOG_DESCRIPTION' => '',
			'METAOG_IMAGE' => '',
			'PIXELFB_USE' => 'N',
			'GACOUNTER_USE' => 'N',
			'GACOUNTER_SEND_CLICK' => 'N',
			'GACOUNTER_SEND_SHOW' => 'N',
			'METAROBOTS_INDEX' => 'Y',
			'BACKGROUND_USE' => 'N',
			'BACKGROUND_POSITION' => 'center',
			'YACOUNTER_USE' => 'N',
			'GTM_USE' => 'N',
			'PIXELVK_USE' => 'N',
			'HEADBLOCK_USE' => 'N',
			'CSSBLOCK_USE' => 'N',
		],
	],
	'layout' => [],
	'items' => [
		'#block15134' => [
			'old_id' => 15134,
			'code' => '0.menu_26',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-menu' => [
					[
						0 => [
							'text' => 'Main page',
							'href' => '#landing1237',
							'target' => '_self',
						],
						1 => [
							'text' => 'Office',
							'href' => '#landing1239',
							'target' => '_self',
						],
					],
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-pt-20 g-pb-20 u-header u-header--sticky u-header--relative g-brd-top g-theme-bitrix-brd-v4 g-brd-4 g-bg-transparent',
				],
			],
		],
	],
];