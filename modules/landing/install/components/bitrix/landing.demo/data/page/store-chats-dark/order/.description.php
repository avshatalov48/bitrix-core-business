<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'code' => 'store-chats-dark/order',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-ORDER-NAME'),
	'description' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-ORDER-DESC'),
	'type' => 'store',
	'version' => 3,
	'fields' => array(
		'RULE' => NULL,
		'ADDITIONAL_FIELDS' => array(
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-ORDER-RICH_NAME'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-ORDER-RICH_DESC'),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/store-chats/order/preview.jpg',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => '3corporate',
			'THEME_CODE_TYPO' => '3corporate',
		),
	),
	
	
	'items' => array(
		'0' => array(
			'code' => '27.3.one_col_fix_title',
			'access' => 'X',
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-ORDER-TEXT5'),
				),
			),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block js-animation animation-none g-pt-0 g-pb-5 u-block-border u-block-border-margin-sm animation-none text-center',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title h2 g-color-white text-left g-font-size-27 container g-pl-0 g-pr-0',
				),
			),
		),
		'1' => array(
			'code' => 'store.salescenter.order.details',
			'nodes' => array(
				'bitrix:salescenter.order.details' => array(
					'TEMPLATE_MODE' => 'darkmode',
				)
			),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block g-pt-10 g-pb-10 u-block-border u-block-border-margin-md',
				),
			),
		),
		'2' => array(
			'code' => '52.3.mini_text_titile_with_btn_right',
			'access' => 'X',
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-ORDER-TEXT4'),
				),
				'.landing-block-node-text' => array(
					0 => '<a href="tel:+469548521" target="_self">+469 548 521</a>',
				),
				'.landing-block-node-button' => array(
					0 => array(
						'href' => 'tel:+469548521',
						'text' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-ORDER-TEXT3'),
					),
				),
			),
			'style' => array(
				'.landing-block-node-container' => array(
					0 => 'landing-block-node-container row g-flex-centered align-items-center',
				),
				'.landing-block-node-text-container' => array(
					0 => 'landing-block-node-text-container text-left col-8 js-animation animation-none',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title h6 g-color-white-opacity-0_7 g-mb-5 text-left g-font-size-16',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-color-white g-font-size-16',
				),
				'.landing-block-node-button-container' => array(
					0 => 'landing-block-node-button-container text-right col-4 js-animation animation-none d-flex justify-content-end',
				),
				'.landing-block-node-button' => array(
					0 => 'landing-block-node-button btn btn-sm text-uppercase g-px-15 font-weight-bold g-mb-0 g-rounded-15 u-btn-outline-gray g-color-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-10 g-pb-10 g-theme-bitrix-bg-dark-v1 u-block-border u-block-border-margin-md g-rounded-6',
				),
			),
		),
		'3' => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'access' => 'X',
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-ORDER-TEXT1'),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-ORDER-TEXT2'),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title h2 g-color-white-opacity-0_7 g-font-size-16 text-left g-mb-5',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-size-16 text-left',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation animation-none g-pt-10 g-pb-10 u-block-border-first g-rounded-6 u-block-border-margin-md u-block-border g-theme-bitrix-bg-dark-v1',
				),
			),
		),
		'4' => array(
			'code' => '16.1.google_map',
			'access' => 'X',
			'style' => array(
				'#wrapper' => array(
					0 => 'landing_block g-height-1 g-min-height-70vh u-block-border u-block-border-margin-md g-rounded-6 u-block-border-end g-theme-bitrix-bg-dark-v1 g-pt-10 g-pb-10 g-pl-10 g-pr-10',
				),
			),
		),
	),
);