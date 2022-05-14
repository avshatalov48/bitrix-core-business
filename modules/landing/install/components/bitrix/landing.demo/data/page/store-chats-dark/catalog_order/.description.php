<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'code' => 'store-chats-dark/catalog_order',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_ORDER-NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 3,
	'lock_delete' => true,
	'fields' => [
		'RULE' => NULL,
		'ADDITIONAL_FIELDS' => [
			'BACKGROUND_USE' => 'Y',
			'BACKGROUND_COLOR' => '#ffffff',
			'B24BUTTON_CODE' => 'N',
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/store_v3/checkout/preview.jpg',
			// 'VIEW_TYPE' => 'adaptive',
		],
	],
	'layout' => [
		'code' => 'empty',
	],
	'items' => [
		0 => [
			'code' => 'store.store_v3_menu_3',
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
					0 => Loc::getMessage("LANDING_DEMO_STORE_CHATS_ORDER-TEXT_1"),
				],
				'.landing-block-node-text' => [
					0 => Loc::getMessage("LANDING_DEMO_STORE_CHATS_ORDER-TEXT_2"),
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block landing-block-node-img u-bg-overlay g-flex-centered g-bg-img-hero g-height-auto g-pb-90 g-bg-size-cover g-pt-30 g-mb-20 g-bg-none--after l-d-xs-none l-d-md-none',
				],
				'.landing-block-node-container' => [
					0 => 'landing-block-node-container container text-center u-bg-overlay__inner g-mx-0',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title landing-semantic-title-image-medium g-line-height-1 g-mt-20 text-left g-color-black g-text-transform-none g-font-size-46 g-font-roboto g-font-weight-600 g-mb-10',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text landing-semantic-text-image-medium text-left g-color-black g-font-size-22 g-font-roboto g-font-weight-400 g-mb-0',
				],
			],
		],
		2 => [
			'code' => '52.5.link_back',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_ORDER-BACK'),
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-bg-white l-d-xs-none l-d-md-none',
				],
			],
		],
		3 => [
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
		4 => [
			'code' => 'store.order_store_v3',
			'cards' => [],
			'nodes' => [],
			'access' => 'W',
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-bg-white g-pt-0 g-pb-0',
				],
			],
			'attrs' => [],
		],
	],
];