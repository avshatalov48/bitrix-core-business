<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'old_id' => 12,
	'code' => 'store-chats-dark/catalog_footer',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_CATALOG_FOOTER-NAME'),
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
			'code' => '55.1.list_of_links',
			'access' => 'X',
			'cards' => [
				'.landing-block-node-list-item' => [
					'source' => [
						0 => [
							'value' => 0,
							'type' => 'card',
						],
						1 => [
							'value' => 0,
							'type' => 'card',
						],
						2 => [
							'value' => 0,
							'type' => 'card',
						],
						3 => [
							'value' => 0,
							'type' => 'card',
						],
						4 => [
							'value' => 0,
							'type' => 'card',
						],
					],
				],
			],
			'nodes' => [
				'.landing-block-node-link' => [
					0 => [
						'href' => '#landing1',
						'target' => '_self',
					],
					1 => [
						'href' => '#landing2',
						'target' => '_self',
					],
					2 => [
						'href' => '#landing3',
						'target' => '_self',
					],
					3 => [
						'href' => '#landing8',
						'target' => '_self',
					],
					4 => [
						'href' => '#landing9',
						'target' => '_self',
					],
				],
				'.landing-block-node-link-text' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_CATALOG_FOOTER-TEXT1'),
					1 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_CATALOG_FOOTER-TEXT2'),
					2 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_CATALOG_FOOTER-TEXT5'),
					3 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_CATALOG_FOOTER-TEXT3'),
					4 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_CATALOG_FOOTER-TEXT4'),
				],
			],
			'style' => [
				'.landing-block-node-list-container' => [
					0 => 'landing-block-node-list-container row no-gutters justify-content-center',
				],
				'.landing-block-node-list-item' => [
					0 => 'landing-block-node-list-item g-brd-bottom g-brd-1 g-py-12 js-animation animation-none landing-card g-brd-white-opacity-0_2 g-font-size-18 g-pt-18 g-pb-18',
					1 => 'landing-block-node-list-item g-brd-bottom g-brd-1 g-py-12 js-animation animation-none landing-card g-brd-white-opacity-0_2 g-font-size-18 g-pt-18 g-pb-18',
					2 => 'landing-block-node-list-item g-brd-bottom g-brd-1 g-py-12 js-animation animation-none landing-card g-brd-white-opacity-0_2 g-font-size-18 g-pt-18 g-pb-18',
					3 => 'landing-block-node-list-item g-brd-bottom g-brd-1 g-py-12 js-animation animation-none landing-card g-brd-white-opacity-0_2 g-font-size-18 g-pt-18 g-pb-18',
					4 => 'landing-block-node-list-item g-brd-bottom g-brd-1 g-py-12 js-animation animation-none landing-card g-brd-white-opacity-0_2 g-font-size-18 g-pt-18 g-pb-18',
				],
				'.landing-block-node-link' => [
					0 => 'landing-block-node-link row no-gutters justify-content-between align-items-center g-text-decoration-none--hover g-color-primary--hover g-font-size-18 g-color-white',
					1 => 'landing-block-node-link row no-gutters justify-content-between align-items-center g-text-decoration-none--hover g-color-primary--hover g-font-size-18 g-color-white',
					2 => 'landing-block-node-link row no-gutters justify-content-between align-items-center g-text-decoration-none--hover g-color-primary--hover g-font-size-18 g-color-white',
					3 => 'landing-block-node-link row no-gutters justify-content-between align-items-center g-text-decoration-none--hover g-color-primary--hover g-font-size-18 g-color-white',
					4 => 'landing-block-node-link row no-gutters justify-content-between align-items-center g-text-decoration-none--hover g-color-primary--hover g-font-size-18 g-color-white',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-10 g-pb-10 g-pl-15 g-pr-15 u-block-border-none g-theme-bitrix-bg-dark-v3',
				],
			],
		],
	],
];