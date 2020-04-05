<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'code' => 'store-chats-light/webform',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_LIGHT-WEBFORM-NAME'),
	'description' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_LIGHT-WEBFORM-DESC'),
	'type' => 'store',
	'version' => 3,
	'fields' => array(
		'RULE' => null,
		'ADDITIONAL_FIELDS' => array(
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_LIGHT-WEBFORM-RICH_NAME'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_LIGHT-WEBFORM-RICH_DESC'),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/store-chats/webform/preview.jpg',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => '3corporate',
			'THEME_CODE_TYPO' => '3corporate',
		),
	),
	
	'is_webform_page' => 'Y',
	
	'items' => array(
		'0' => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'access' => 'X',
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_LIGHT-WEBFORM-TEXT1'),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_LIGHT-WEBFORM-TEXT2'),
				),
			),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block js-animation animation-none g-pt-0 g-pb-5 u-block-border u-block-border-margin-sm animation-none',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title h2 g-color-gray-dark-v1 text-left g-font-size-27',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-gray-dark-v4 g-font-size-16 text-left',
				),
			),
		),
		'1' => array(
			'code' => '33.14.form_2_light_no_text_simple',
			'nodes' => array(
				'.landing-block-node-bgimg' => array(
					0 => array(
						'src' => null,
					),
				),
			),
			'style' => array(
				'#wrapper' => array(
					0 => 'g-pos-rel landing-block text-center g-color-gray-dark-v1 g-pt-30 g-pb-30 g-bg-white u-block-border u-block-border-margin-md g-rounded-6',
				),
			),
		),
	),
);