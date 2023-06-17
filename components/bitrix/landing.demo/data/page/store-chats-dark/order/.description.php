<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'old_id' => 7,
	'code' => 'store-chats-dark/order',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-ORDER-NAME'),
	'description' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-ORDER-DESC'),
	'type' => 'store',
	'version' => 3,
	'lock_delete' => true,
	'fields' => [
		'RULE' => NULL,
		'ADDITIONAL_FIELDS' => [
			'B24BUTTON_CODE' => 'N',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-ORDER-RICH_NAME'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-ORDER-RICH_DESC'),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/store-chats/order/preview.jpg',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => '3corporate',
			'BACKGROUND_USE' => 'Y',
			'BACKGROUND_COLOR' => '#1c1c22',
			'BACKGROUND_PICTURE' => 'https://cdn.bitrix24.site/bitrix/images/landing/bg/store-chat-gray.jpg',
			'BACKGROUND_POSITION' => 'no_repeat',
			'CSSBLOCK_USE' => 'Y',
			'CSSBLOCK_CODE' =>
				'.landing-viewtype--mobile .landing-public-mode {'
				. 'outline: none;'
				. '}',
		],
	],
	'layout' => [
		'code' => 'empty',
	],
	
	'items' => [
		'0' => [
			'code' => 'store.salescenter.order.details',
			'access' => 'W',
			'nodes' => [
				'bitrix:salescenter.order.details' => [
					'TEMPLATE_MODE' => 'graymode',
					'SHOW_HEADER' => 'Y',
				]
			],
		],
		'1' => [
			'code' => '61.1.phone_w_btn_rght',
			'access' => 'X',
			'nodes' => [
				'bitrix:landing.blocks.crm_contacts' => [
					'TEMPLATE_MODE' => 'graymode',
					'BUTTON_TITLE' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-ORDER-TEXT3'),
					'BUTTON_POSITION' => 'right',
					'BUTTON_CLASSES' => 'btn g-rounded-50 g-btn-type-outline g-btn-px-l g-btn-size-md g-btn-darkgray text-uppercase',
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-pt-20 g-pb-0 g-bg-transparent u-block-border-none',
				],
			],
		],
		'2' => [
			'code' => '26.separator',
			'nodes' => [
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-bg-transparent g-pt-20 g-pb-10',
				],
				'.landing-block-line' => [
					0 => 'landing-block-line g-brd-gray-dark-v2 my-0',
				],
			],
		],
	],
];