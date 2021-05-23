<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'parent' => 'store-chats-light',
	'code' => 'store-chats-light/footer',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_LIGHT-FOOTER-NAME'),
	'description' => '',
	'active' => true,
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'N',
	'type' => 'store',
	'version' => 3,
	'fields' => [
		'TITLE' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_LIGHT-FOOTER-NAME'),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => '3corporate',

		],
	],
	'layout' => [],
	'items' => [
		'0' => [
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
						4 => array(
							'value' => 0,
							'type' => 'card',
						),
					],
				],
			],
			'nodes' => [
				'.landing-block-node-link' => [
					0 => [
						'href' => '#landing@landing[store-chats-light/about]',
						'target' => '_self',
					],
					1 => [
						'href' => '#landing@landing[store-chats-light/contacts]',
						'target' => '_self',
					],
					2 => [
						'href' => '#landing@landing[store-chats-light/cutaway]',
						'target' => '_self',
					],
					3 => [
						'href' => '#landing@landing[store-chats-light/payinfo]',
						'target' => '_self',
					],
					4 => [
						'href' => '#landing@landing[store-chats-light/webform]',
						'target' => '_self',
					],
				],
				'.landing-block-node-link-text' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_LIGHT-FOOTER-TEXT1'),
					1 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_LIGHT-FOOTER-TEXT2'),
					2 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_LIGHT-FOOTER-TEXT5'),
					3 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_LIGHT-FOOTER-TEXT3'),
					4 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_LIGHT-FOOTER-TEXT4'),
				],
			],
			'style' => [
				'.landing-block-node-list-container' => [
					0 => 'landing-block-node-list-container row no-gutters justify-content-center',
				],
				'.landing-block-node-list-item' => [
					0 => 'landing-block-node-list-item g-brd-bottom g-brd-1 g-py-12 js-animation animation-none landing-card g-brd-gray-light-v5 g-font-size-18 g-pt-18 g-pb-18',
					1 => 'landing-block-node-list-item g-brd-bottom g-brd-1 g-py-12 js-animation animation-none landing-card g-brd-gray-light-v5 g-font-size-18 g-pt-18 g-pb-18',
					2 => 'landing-block-node-list-item g-brd-bottom g-brd-1 g-py-12 js-animation animation-none landing-card g-brd-gray-light-v5 g-font-size-18 g-pt-18 g-pb-18',
					3 => 'landing-block-node-list-item g-brd-bottom g-brd-1 g-py-12 js-animation animation-none landing-card g-brd-gray-light-v5 g-font-size-18 g-pt-18 g-pb-18',
					4 => 'landing-block-node-list-item g-brd-bottom g-brd-1 g-py-12 js-animation animation-none landing-card g-brd-gray-light-v5 g-font-size-18 g-pt-18 g-pb-18',
				],
				'.landing-block-node-link' => [
					0 => 'landing-block-node-link row no-gutters justify-content-between align-items-center g-text-decoration-none--hover g-color-primary--hover g-font-size-18 g-color-gray-dark-v1',
					1 => 'landing-block-node-link row no-gutters justify-content-between align-items-center g-text-decoration-none--hover g-color-primary--hover g-font-size-18 g-color-gray-dark-v1',
					2 => 'landing-block-node-link row no-gutters justify-content-between align-items-center g-text-decoration-none--hover g-color-primary--hover g-font-size-18 g-color-gray-dark-v1',
					3 => 'landing-block-node-link row no-gutters justify-content-between align-items-center g-text-decoration-none--hover g-color-primary--hover g-font-size-18 g-color-gray-dark-v1',
					4 => 'landing-block-node-link row no-gutters justify-content-between align-items-center g-text-decoration-none--hover g-color-primary--hover g-font-size-18 g-color-gray-dark-v1',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-10 g-pb-10 g-pl-15 g-pr-15 u-block-border-none g-bg-white',
				],
			],
		],
		'1' => [
			'code' => '17.copyright',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '2020 &copy; All rights reserved',
				],
			],
		],
	],
];