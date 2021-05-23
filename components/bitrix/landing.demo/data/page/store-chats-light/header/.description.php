<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'parent' => 'store-chats-light',
	'code' => 'store-chats-light/header',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_LIGHT-HEADER-NAME'),
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
		'TITLE' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_LIGHT-HEADER-NAME'),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => array(
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => '3corporate',

		),
	),
	'layout' => array(),
	'items' => array(
		'0' => array(
			'code' => '35.9.header_shop_and_phone',
			'nodes' => array(
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title text-left g-font-size-24 g-color-black g-font-weight-700 g-mb-0 g-letter-spacing-0',
				),
				'.landing-block-node-phone' => array(
					0 => 'landing-block-node-phone g-font-size-18 g-color-black-opacity-0_8 g-color-primary--hover',
				),
				'#wrapper' => array(
					0 => 'landing-block landing-block-menu g-pt-20 g-pb-20',
				),
			)
		),
	),
);