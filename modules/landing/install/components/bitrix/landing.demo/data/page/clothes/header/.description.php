<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'code' => 'clothes/header',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CLOTHES-HEADER--NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'RULE' => NULL,
		'ADDITIONAL_FIELDS' => array(
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/clothes/header/preview.jpg',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => 'travel',
		),
	),
	'layout' => array(
		'code' => 'empty',
		'ref' => array(),
	),
	'items' => array(
		0 => array(
			'code' => '35.6.header_with_contacts_search_wo_logo',
			'cards' => array(
				'.landing-block-node-card' => 2,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => [
					0 => 'landing-block-node-card-icon icon icon-screen-smartphone',
					1 => 'landing-block-node-card-icon icon icon-clock',
				],
				'.landing-block-node-card-title' => [
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-HEADER--TEXT_1"),
					1 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-HEADER--TEXT_2"),
				],
				'.landing-block-node-card-link' => array(
					0 => [
						'href' => 'tel:+74952128506',
						'text' => '+7 (495) 212 85 06',
					],
				
				),
				'.landing-block-node-card-text' => [
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-HEADER--TEXT_8"),
				],
			),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block landing-block-menu l-d-xs-none g-bg-white g-brd-bottom g-brd-gray-light-v4 g-pt-0 g-pb-0',
				),
			),
			'attrs' => array(),
		),
		1 => array(
			'code' => '0.menu_03',
			'cards' => array(
				'.landing-block-node-menu-list-item' => 8,
			),
			'nodes' => array(
				'.landing-block-node-menu-logo' => array(
					0 => array(
						'alt' => 'Logo',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/eshop/logo_wo_icon.png',
						'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/eshop/logo_wo_icon@2x.png',
					),
				),
				'.landing-block-node-menu-logo-link' => array(
					0 => array(
						'href' => '#system_mainpage',
					),
				),
				'.landing-block-node-menu-list-item-link' => array(
					3 => array(
						'text' => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-HEADER--TEXT_3"),
						'href' => '#landing@landing[clothes/faq]',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => NULL,
							'data-url' => NULL,
						),
					),
					4 => array(
						'text' => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-HEADER--TEXT_4"),
						'href' => '#landing@landing[clothes/delivery]',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => NULL,
							'data-url' => NULL,
						),
					),
					5 => array(
						'text' => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-HEADER--TEXT_5"),
						'href' => '#landing@landing[clothes/guarantee]',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => NULL,
							'data-url' => NULL,
						),
					),
					6 => array(
						'text' => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-HEADER--TEXT_6"),
						'href' => '#landing@landing[clothes/about]',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => NULL,
							'data-url' => NULL,
						),
					),
					7 => array(
						'text' => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-HEADER--TEXT_7"),
						'href' => '#landing@landing[clothes/contacts]',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => NULL,
							'data-url' => NULL,
						),
					),
				),
			),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block landing-block-menu g-bg-white u-header u-header--static u-header--relative',
				),
			),
			'attrs' => array(),
		),
		2 => array(
			'code' => 'store.breadcrumb_dark_bg_text_left',
			'cards' => array(),
			'nodes' => array(),
			'style' => array(),
			'attrs' => array(),
		),
	),
);