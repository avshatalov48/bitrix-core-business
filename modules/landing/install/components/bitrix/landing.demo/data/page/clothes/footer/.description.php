<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'code' => 'clothes/footer',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CLOTHES-FOOTER--NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'RULE' => NULL,
		'ADDITIONAL_FIELDS' => array(
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/clothes/footer/preview.jpg',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => 'travel',

		),
	),
	'layout' => array(),
	'items' => array(
		0 => array(
			'code' => '35.2.footer_dark',
			'cards' => array(
				'.landing-block-card-contact' => 2,
				'.landing-block-card-list1-item' => 3,
				'.landing-block-card-list2-item' => 3,
				'.landing-block-card-list3-item' => 2,
			),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FOOTER--TEXT_1"),
					1 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FOOTER--TEXT_2"),
					2 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FOOTER--TEXT_3"),
					3 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FOOTER--TEXT_4"),
				),
				'.landing-block-node-text' => array(
					0 => '<a href="#landing@landing[clothes/personal]" target="_self">'. Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FOOTER--TEXT_11").'</a>',
				),
				'.landing-block-node-card-contact-icon' => array(
					0 => 'landing-block-node-card-contact-icon fa fa-home',
					1 => 'landing-block-node-card-contact-icon fa fa-phone',
				),
				'.landing-block-node-card-contact-text' => array(
					0 => Loc::getMessage("NOTETRANSLATE--LANDING_DEMO_STORE_CLOTHES-FOOTER--TEXT_1"),
					1 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES_FOOTER_TEXT_12"),
				),
				'.landing-block-node-card-contact-link' => array(
					0 => [
						'text' => '1-800-643-4500',
						'href' => 'tel:1-800-643-4500',
					],
				),
				'.landing-block-node-list-item' => array(
					3 => array(
						'text' => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FOOTER--TEXT_5"),
						'href' => '#landing@landing[clothes/faq]',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => NULL,
							'data-url' => NULL,
						),
					),
					4 => array(
						'text' => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FOOTER--TEXT_6"),
						'href' => '#landing@landing[clothes/delivery]',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => NULL,
							'data-url' => NULL,
						),
					),
					5 => array(
						'text' => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FOOTER--TEXT_7"),
						'href' => '#landing@landing[clothes/guarantee]',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => NULL,
							'data-url' => NULL,
						),
					),
					6 => array(
						'text' => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FOOTER--TEXT_8"),
						'href' => '#landing@landing[clothes/about]',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => NULL,
							'data-url' => NULL,
						),
					),
					7 => array(
						'text' => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FOOTER--TEXT_9"),
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
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-700 g-mb-20 g-color-primary g-text-transform-none g-font-size-20',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-color-gray-light-v1 g-mb-20 g-font-size-14',
				),
				'.landing-block-node-card-contact-text' => array(
					0 => 'landing-block-node-card-contact-text g-color-gray-light-v1 g-font-size-14',
				),
				'.landing-block-node-list-item' => array(
					0 => 'landing-block-node-list-item g-color-gray-dark-v5 g-font-size-14',
				),
				'.landing-block-node-card-contact-icon' => array(
					0 => 'landing-block-node-card-contact-icon fa fa-home',
					1 => 'landing-block-node-card-contact-icon fa fa-phone',
				),
				'#wrapper' => array(
					0 => 'g-pt-60 g-bg-black g-pb-0',
				),
			),
			'attrs' => array(),
		),
		1 => array(
			'code' => '17.copyright',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-text' => array(
					0 => '<p>'. Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FOOTER--TEXT_10").'</p>',
				),
			),
			'style' => array(
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-font-size-12 g-color-white js-animation animation-none',
				),
				'#wrapper' => array(
					0 => 'landing-block g-bg-black js-animation animation-none',
				),
			),
			'attrs' => array(),
		),
	),
);