<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'old_id' => '228',
	'code' => 'wiki-light/sidebar',
	'name' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_SEDEBAR_TITLE"),
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
		'TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_SEDEBAR_TITLE"),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'METAMAIN_USE' => 'N',
			'METAMAIN_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_SEDEBAR_TITLE"),
			'METAMAIN_DESCRIPTION' => '',
			'METAOG_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_SEDEBAR_TITLE"),
			'METAOG_DESCRIPTION' => '',
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/wiki-light/sidebar/preview.jpg',
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
		'#block3364' => [
			'old_id' => 3364,
			'code' => '32.16.img_one_big_sidebar',
			'access' => 'X',
			'anchor' => '',
			'nodes' => [
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block landing-block-menu g-pl-auto g-pr-auto g-mt-auto g-pt-30 g-pb-30',
				],
			],
		],
		'#block3427' => [
			'old_id' => 3427,
			'code' => '0.menu_25',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-menu' => [
					[
						0 => [
							'text' => 'Bitrix24 Knowledge Base',
							'href' => '#landing227',
							'target' => '_self',
						],
						1 => [
							'text' => 'Tasks and Projects',
							'href' => '#landing229',
							'target' => '_self',
							'children' => [
								0 => [
									'text' => 'Article example',
									'href' => '#landing230',
									'target' => '_self',
								],
							]
						],
					],
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-pt-30 g-pb-30',
				],
				'.landing-block-node-navbar' => [
					0 => 'landing-block-node-navbar g-px-15 navbar navbar-expand-md g-brd-0 u-navbar-color-primary--hover u-navbar-color-gray-dark-v3 u-navbar-align-around',
				],
				'.landing-block-node-menu' => [
					0 => 'landing-block-node-menu navbar-nav g-menu-multilevel js-scroll-nav flex-column list-unstyled w-100 g-mb-0',
				],
			],
		],
	],
];