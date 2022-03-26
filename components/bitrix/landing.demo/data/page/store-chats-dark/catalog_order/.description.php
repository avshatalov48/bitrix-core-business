<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'code' => 'store-chats-dark/catalog_order',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CHATS-ORDER-NAME'),
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
		],
	],
	'layout' => [
		'code' => 'empty',
	],
	'items' => [
		0 => [
			'code' => '35.9.header_shop_and_phone',
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-700 g-font-size-20 g-font-size-25--lg g-text-decoration-none--hover g-nowrap g-text-overflow-ellipsis g-overflow-hidden text-left g-letter-spacing-0_5 text-uppercase',
				],
				'.landing-block-node-phone' => [
					0 => 'landing-block-node-phone g-font-size-17 g-font-weight-500 g-nowrap mb-0',
				],
			],
		],
		1 => [
			'code' => '52.5.link_back',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS-ORDER-BACK'),
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-bg-white l-d-xs-none l-d-md-none',
				],
			],
		],
		2 => [
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
		3 => [
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