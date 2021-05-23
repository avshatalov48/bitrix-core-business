<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'code' => 'store-chats-dark/contacts',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-CONTACTS-NAME'),
	'description' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-CONTACTS-DESC'),
	'type' => 'store',
	'version' => 3,
	'fields' => array(
		'RULE' => null,
		'ADDITIONAL_FIELDS' => array(
			'B24BUTTON_CODE' => 'N',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-CONTACTS-RICH_NAME'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-CONTACTS-RICH_DESC'),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/store-chats/contacts/preview.jpg',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => '3corporate',
		),
	),
	'items' => array(
		'0' => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'access' => 'X',
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-CONTACTS-TEXT1'),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-CONTACTS-TEXT2'),
				),
			),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block js-animation animation-none g-pt-25 g-pb-20 u-block-border-none animation-none g-theme-bitrix-bg-dark-v3',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title h2 g-color-white text-left g-font-size-27',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white text-left',
				),
			),
		),
		'1' => array(
			'code' => '27.3.one_col_fix_title',
			'access' => 'X',
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-CONTACTS-TEXT3'),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title h2 g-color-white-opacity-0_8 text-left g-font-size-16 g-mb-5 container g-pl-15 g-pr-15 font-weight-normal',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation animation-none g-pt-25 g-pb-5 u-block-border-none g-bg-transparent text-center',
				),
			),
		),
		'2' => array(
			'code' => '15.social',
			'access' => 'X',
			'nodes' => array(),
			'style' => array(
				'.landing-block-node-list-item-link' => array(
					0 => 'landing-block-node-list-item-link d-block g-color-white text-center g-font-size-24 g-pt-10 g-pb-10 g-pl-30 g-pr-30 g-bg-facebook--hover g-bg-facebook g-mb-10 g-ml-5 g-mr-5',
					1 => 'landing-block-node-list-item-link d-block g-color-white text-center g-font-size-24 g-pt-10 g-pb-10 g-pl-30 g-pr-30 g-bg-instagram--hover g-bg-instagram g-mb-10 g-ml-5 g-mr-5',
					2 => 'landing-block-node-list-item-link d-block g-color-white text-center g-font-size-24 g-pt-10 g-pb-10 g-pl-30 g-pr-30 g-bg-twitter--hover g-bg-twitter g-mb-10 g-ml-5 g-mr-5',
					3 => 'landing-block-node-list-item-link d-block g-color-white text-center g-font-size-24 g-pt-10 g-pb-10 g-pl-30 g-pr-30 g-bg-youtube--hover g-bg-youtube g-mb-10 g-ml-5 g-mr-5',
					4 => 'landing-block-node-list-item-link d-block g-color-white text-center g-font-size-24 g-pt-10 g-pb-10 g-pl-30 g-pr-30 g-bg-telegram--hover g-bg-telegram g-mb-10 g-ml-5 g-mr-5',
					5 => 'landing-block-node-list-item-link d-block g-color-white text-center g-font-size-24 g-pt-10 g-pb-10 g-pl-30 g-pr-30 g-bg-whatsapp--hover g-bg-whatsapp g-mb-10 g-ml-5 g-mr-5',
				),
				'#wrapper' => array(
					0 => 'landing-block u-block-border-none g-bg-transparent g-pb-20 g-pl-5 g-pr-5',
				),
			),
		),
		'3' => array(
			'code' => '52.3.mini_text_titile_with_btn_right',
			'access' => 'X',
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-CONTACTS-TEXT8'),
				),
				'.landing-block-node-button' => array(
					0 => array(
						'text' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-CONTACTS-TEXT7'),
					),
				),
				'.landing-block-node-text' => array(
					0 => array(
						'text' => '<a href="tel:#PHONE1#">#PHONE1#</a>',
					),
				),
			),
			'style' => array(
				'.landing-block-node-container' => array(
					0 => 'landing-block-node-container row g-flex-centered align-items-end',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title h6 g-color-white-opacity-0_8 g-mb-25 text-left g-font-size-16 font-weight-normal',
				),
				'.landing-block-node-text-container' => array(
					0 => 'landing-block-node-text-container text-left col-8 js-animation animation-none',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-color-white g-font-size-18',
				),
				'.landing-block-node-button-container' => array(
					0 => 'landing-block-node-button-container text-right col-4 js-animation animation-none d-flex justify-content-end',
				),
				'.landing-block-node-button' => array(
					0 => 'landing-block-node-button btn g-btn-type-solid g-btn-size-md g-btn-px-m g-text-transform-none g-mb-0 g-theme-bitrix-btn-v6 g-rounded-20 g-color-white',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-20 g-pb-20 g-theme-bitrix-bg-dark-v3 u-block-border-none',
				),
			),
		),
		'4' => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'access' => 'X',
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-CONTACTS-TEXT5'),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-CONTACTS-TEXT6'),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title h2 g-color-white-opacity-0_8 g-font-size-16 text-left g-mb-5 font-weight-normal',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-size-18 text-left',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation animation-none g-pt-25 g-pb-10 u-block-border-none g-bg-transparent',
				),
			),
		),
		'5' => array(
			'code' => '16.1.google_map',
			'access' => 'X',
			'style' => array(
				'#wrapper' => array(
					0 => 'landing_block g-height-1 g-min-height-50vh u-block-border-none g-theme-bitrix-bg-dark-v3 g-pt-10 g-pb-10 g-pl-10 g-pr-10',
				),
			),
		),
		'6' => array(
			'code' => '26.separator',
			'nodes' => array(
			),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block g-bg-transparent g-pt-15 g-pb-10',
				),
				'.landing-block-line' => array(
					0 => 'landing-block-line g-brd-transparent my-0',
				),
			),
		),
	),
);