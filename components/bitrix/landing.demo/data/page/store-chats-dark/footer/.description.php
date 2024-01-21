<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'old_id' => 4,
	'parent' => 'store-chats-dark',
	'code' => 'store-chats-dark/footer',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-FOOTER-NAME'),
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
		'TITLE' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-FOOTER-NAME'),
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
			'code' => '55.1.list_of_links',
			'access' => 'X',
			'cards' => array(
				'.landing-block-node-list-item' => array(
					'source' => array(
						0 => array(
							'value' => 0,
							'type' => 'card',
						),
						1 => array(
							'value' => 0,
							'type' => 'card',
						),
						2 => array(
							'value' => 0,
							'type' => 'card',
						),
						3 => array(
							'value' => 0,
							'type' => 'card',
						),
						4 => array(
							'value' => 0,
							'type' => 'card',
						),
					),
				),
			),
			'nodes' => [
				'.landing-block-node-link' => [
					0 => [
						'href' => '#landing@landing[store-chats-dark/about]',
						'target' => '_self',
					],
					1 => [
						'href' => '#landing@landing[store-chats-dark/contacts]',
						'target' => '_self',
					],
					2 => [
						'href' => '#landing@landing[store-chats-dark/cutaway]',
						'target' => '_self',
					],
					3 => [
						'href' => '#landing@landing[store-chats-dark/payinfo]',
						'target' => '_self',
					],
					4 => [
						'href' => '#landing@landing[store-chats-dark/webform]',
						'target' => '_self',
					],
				],
				'.landing-block-node-link-text' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-FOOTER-TEXT1'),
					1 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-FOOTER-TEXT2'),
					2 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-FOOTER-TEXT5'),
					3 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-FOOTER-TEXT3'),
					4 => Loc::getMessage('LANDING_DEMO_STORE_CHATS_DARK-FOOTER-TEXT4'),
				],
			],
			'style' => array(
				'.landing-block-node-list-container' => array(
					0 => 'landing-block-node-list-container row no-gutters justify-content-center',
				),
				'.landing-block-node-list-item' => array(
					0 => 'landing-block-node-list-item g-brd-bottom g-brd-1 g-py-12 js-animation animation-none landing-card g-brd-white-opacity-0_2 g-font-size-18 g-pt-18 g-pb-18',
					1 => 'landing-block-node-list-item g-brd-bottom g-brd-1 g-py-12 js-animation animation-none landing-card g-brd-white-opacity-0_2 g-font-size-18 g-pt-18 g-pb-18',
					2 => 'landing-block-node-list-item g-brd-bottom g-brd-1 g-py-12 js-animation animation-none landing-card g-brd-white-opacity-0_2 g-font-size-18 g-pt-18 g-pb-18',
					3 => 'landing-block-node-list-item g-brd-bottom g-brd-1 g-py-12 js-animation animation-none landing-card g-brd-white-opacity-0_2 g-font-size-18 g-pt-18 g-pb-18',
					4 => 'landing-block-node-list-item g-brd-bottom g-brd-1 g-py-12 js-animation animation-none landing-card g-brd-white-opacity-0_2 g-font-size-18 g-pt-18 g-pb-18',
				),
				'.landing-block-node-link' => array(
					0 => 'landing-block-node-link row no-gutters justify-content-between align-items-center g-text-decoration-none--hover g-color-primary--hover g-font-size-18 g-color-white',
					1 => 'landing-block-node-link row no-gutters justify-content-between align-items-center g-text-decoration-none--hover g-color-primary--hover g-font-size-18 g-color-white',
					2 => 'landing-block-node-link row no-gutters justify-content-between align-items-center g-text-decoration-none--hover g-color-primary--hover g-font-size-18 g-color-white',
					3 => 'landing-block-node-link row no-gutters justify-content-between align-items-center g-text-decoration-none--hover g-color-primary--hover g-font-size-18 g-color-white',
					4 => 'landing-block-node-link row no-gutters justify-content-between align-items-center g-text-decoration-none--hover g-color-primary--hover g-font-size-18 g-color-white',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-10 g-pb-10 g-pl-15 g-pr-15 u-block-border-none g-theme-bitrix-bg-dark-v3',
				),
			),
		),
		'1' => array(
			'code' => '17.copyright',
			'nodes' => array(
				'.landing-block-node-text' => array(
					0 => '2022 &copy; All rights reserved',
				),
			),
		),
	),
);