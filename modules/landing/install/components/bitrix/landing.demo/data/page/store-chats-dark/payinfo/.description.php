<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'parent' => 'store-chats-dark',
	'code' => 'store-chats-dark/payinfo',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-PAYINFO-NAME'),
	'description' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-PAYINFO-DESC'),
	'active' => true,
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'N',
	'type' => 'store',
	'version' => 3,
	'fields' => array(
		'TITLE' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-PAYINFO-NAME'),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => array(
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-PAYINFO-RICH_NAME'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-PAYINFO-RICH_DESC'),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/store-chats/payinfo/preview.jpg',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => '3corporate',
			'THEME_CODE_TYPO' => '3corporate',
		),
	),
	'layout' => array(),
	'items' => array(
		'0' => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'access' => 'X',
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-PAYINFO-TEXT1'),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-PAYINFO-TEXT2'),
				),
			),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block js-animation animation-none g-pt-0 g-pb-5 u-block-border u-block-border-margin-sm animation-none',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title h2 g-color-white text-left g-font-size-27',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-size-16 text-left',
				),
			),
		),
		'1' => array(
			'code' => 'store.salescenter.payment.pay',
			'nodes' => array(
				'bitrix:salescenter.payment.pay' => array(
					'TEMPLATE_MODE' => 'darkmode',
				)
			),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block g-pt-10 g-pb-10 u-block-border u-block-border-margin-md',
				),
			),
		),
	),
);