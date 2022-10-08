<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'old_id' => 3,
	'code' => 'store-chats-dark/cutaway',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-CUTAWAY-NAME'),
	'description' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-CUTAWAY-DESC'),
	'type' => 'store',
	'version' => 3,
	'fields' => [
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-CUTAWAY-RICH_NAME'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-CUTAWAY-RICH_DESC'),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/store-chats/cutaway/preview.jpg',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => '3corporate',

			'CSSBLOCK_USE' => 'Y',
			'CSSBLOCK_CODE' => '.b24-widget-button-wrapper{display:none!important;}',
		],
	],
	'layout' => [
		'code' => 'header_only',
		'ref' => [
			1 => 'store-chats-dark/header',
		],
	],
	
	
	'items' => [
		'0' => [
			'code' => '60.1.openlines_buttons',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-CUTAWAY-TEXT1'),
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-pt-25 g-pb-25 u-block-border-none g-theme-bitrix-bg-dark-v3',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-mb-10 g-color-white-opacity-0_8 text-left g-font-size-16 font-weight-normal',
				],
			],
		],
		'1' => [
			'code' => '27.3.one_col_fix_title',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-CUTAWAY-TEXT2'),
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title h2 g-color-white-opacity-0_8 text-left g-font-size-16 g-mb-5 container g-pl-15 g-pr-15 font-weight-normal',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation animation-none g-pt-25 g-pb-5 u-block-border-none g-bg-transparent text-center',
				],
			],
		],
		'2' => [
			'code' => '15.2.social_circles',
			'access' => 'X',
			'cards' => array(
				'.landing-block-node-list-item' => array(
					'source' => array(
						0 => array(
							'value' => 'instagram',
							'type' => 'preset',
						),
						1 => array(
							'value' => 'telegram',
							'type' => 'preset',
						),
						2 => array(
							'value' => 'facebook',
							'type' => 'preset',
						),
						3 => array(
							'value' => 'whatsapp',
							'type' => 'preset',
						),
					),
				),
			),
			'style' => [
				'.landing-block-node-list' => [
					0 => 'landing-block-node-list row no-gutters list-inline g-mb-0 justify-content-around',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-20 g-pb-20 u-block-border-none g-bg-transparent',
				],
			],
		],
	],
];