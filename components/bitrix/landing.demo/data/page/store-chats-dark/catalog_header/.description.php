<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'old_id' => 13,
	'code' => 'store-chats-dark/catalog_header',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_CATALOG_HEADER-NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 3,
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'BACKGROUND_USE' => 'Y',
			'BACKGROUND_COLOR' => '#ffffff',
		],
	],
	'layout' => [
		'code' => 'empty',
	],
	'items' => [
		0 => [
			'code' => 'store.store_v3_menu_3',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => [
						'href' => '#',
						'target' => '_self',
					],
				],
			]
		],
		1 => [
			'code' => '01.big_with_text_3_1',
			'nodes' => [
				'.landing-block-node-img' => [
					0 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/blocks/1.jpg',
					],
				],
				'.landing-block-node-title' => [
					0 => Loc::getMessage("LANDING_DEMO_STORE_CHATS_CATALOG_HEADER-TEXT_1"),
				],
				'.landing-block-node-text' => [
					0 => Loc::getMessage("LANDING_DEMO_STORE_CHATS_CATALOG_HEADER-TEXT_2"),
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block landing-block-node-img u-bg-overlay g-flex-centered g-bg-img-hero g-height-auto g-bg-size-cover g-pt-55 g-pb-80 g-mb-30 g-bg-none--after l-d-xs-none l-d-md-none',
				],
				'.landing-block-node-container' => [
					0 => 'landing-block-node-container container text-center u-bg-overlay__inner g-mx-0',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-line-height-1 text-left g-color-black g-text-transform-none g-font-size-46 g-font-roboto g-font-weight-600 g-mb-10',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text text-left g-color-black g-font-size-22 g-font-roboto g-font-weight-400 g-mb-0',
				],
			],
		],
	],
];