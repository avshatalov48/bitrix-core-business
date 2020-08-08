<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'parent' => 'store-instagram',
	'code' => 'store-instagram/mainpage',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_INSTAGRAM--MAINPAGE--NAME'),
	'description' => Loc::getMessage('LANDING_DEMO_STORE_INSTAGRAM--MAINPAGE--DESC'),
	'active' => true,
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'N',
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'TITLE' => Loc::getMessage('LANDING_DEMO_STORE_INSTAGRAM--MAINPAGE--NAME'),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => array(
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => '1construction',

		),
	),
	'layout' => array(
		'code' => 'header_footer',
		'ref' => array(
			1 => 'store-instagram/header_main',
			2 => 'store-instagram/footer',
		),
	),
	'items' => array(
		'#block7742' => array(
			'code' => '01.big_with_text_3_1',
			'anchor' => 'home',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1400x700/img8.jpg',
					),
				),
				'.landing-block-node-title' => array(
					0 => 'WELCOME',
				),
				'.landing-block-node-text' => array(
					0 => 'TO OUR COMPANY',
				),
			),
			'style' => array(
				'.landing-block-node-container' => array(
					0 => 'landing-block-node-container container g-max-width-800 js-animation fadeInDown text-center u-bg-overlay__inner',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title text-uppercase g-line-height-1 g-font-weight-700 g-color-white g-mb-20 g-mt-20 g-font-size-60',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-mb-35 g-font-size-27 g-color-white-opacity-0_9',
				),
				'#wrapper' => array(
					0 => 'landing-block landing-block-node-img u-bg-overlay g-flex-centered g-bg-img-hero g-bg-black-opacity-0_5--after g-min-height-50vh g-pt-80 g-pb-80 g-mt-0',
				),
			),
		),
		'#block7578' => array(
			'code' => '04.1.one_col_fix_with_title',
			'anchor' => 'products',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => ' ',
				),
				'.landing-block-node-title' => array(
					0 => 'Make your Choice',
				),
			),
			'style' => array(
				'.landing-block-node-subtitle' => array(
					0 => 'landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 text-uppercase g-color-black-opacity-0_9 g-font-size-25',
				),
				'.landing-block-node-inner' => array(
					0 => 'landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pb-0 g-pt-10',
				),
			),
		),
		'#block7859' => array(
			'code' => 'store.catalog.list',
			'cards' => array(),
			'nodes' => array(
				'bitrix:catalog.section' => array(
					"PRODUCT_ROW_VARIANTS" => "[3]",
					"SECTION_CODE" => "IMPORT_INSTAGRAM",
				)
			),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block g-pt-0 g-pb-0',
				),
			),
			'attrs' => array(
				'bitrix:catalog.section' => array(),
			),
		),
		'#block7740' => array(
			'code' => '04.1.one_col_fix_with_title',
			'anchor' => 'contact',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => ' ',
				),
				'.landing-block-node-title' => array(
					0 => 'Contact us',
				),
			),
			'style' => array(
				'.landing-block-node-subtitle' => array(
					0 => 'landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 text-uppercase g-color-black-opacity-0_9 g-font-size-25',
				),
				'.landing-block-node-inner' => array(
					0 => 'landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-50 g-pb-40',
				),
			),
		),
		'#block7580' => array(
			'code' => '33.23.form_2_themecolor_no_text',
			'cards' => array(),
			'nodes' => array(),
			'style' => array(
				'#wrapper' => array(
					0 => 'g-pos-rel landing-block text-center g-py-80 g-bg-primary g-pb-40 g-pt-20',
				),
			),
		),
	),
);