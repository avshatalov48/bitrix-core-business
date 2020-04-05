<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'parent' => 'store-chats-dark',
	'code' => 'store-chats-dark/header',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-HEADER-NAME'),
	'description' => '',
	'active' => true,
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'N',
	'type' => 'store',
	'version' => 3,
	'fields' => array(
		'TITLE' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-HEADER-NAME'),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => array(
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => '3corporate',
			'THEME_CODE_TYPO' => '3corporate',
		),
	),
	'layout' => array(),
	'items' => array(
		'0' => array(
			'code' => '35.8.header_logo_and_slogan_row',
			'nodes' => array(
				'.landing-block-node-logo' => array(
					0 => array(
						'alt' => 'Logo',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/logos/chats-store-dark-small.png',
						'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/logos/chats-store-dark-small.png',
						'url' => '{"text":"","href":"#system_mainpage","target":"_self","enabled":true}',
					),
				),
			),
			'style' => array(
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text h5 g-font-size-13 mb-0 g-color-white-opacity-0_7',
				),
				'#wrapper' => array(
					0 => 'landing-block landing-block-menu g-pt-20 g-pb-0',
				),
			)
		),
		'1' => array(
			'code' => '26.separator',
			'style' => array(
				'.landing-block-line' => array(
					0 => 'landing-block-line g-brd-gray-dark-v4 my-0',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-10 g-pb-10 g-pl-5 g-pr-5',
				),
			)
		),
	),
);