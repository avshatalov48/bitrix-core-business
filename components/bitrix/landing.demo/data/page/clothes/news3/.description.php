<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'code' => 'clothes/news3',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CLOTHES-NEWS3--NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'RULE' => NULL,
		'ADDITIONAL_FIELDS' => array(
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/clothes/news3/preview.jpg',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => 'travel',
		),
	),
	'layout' => array(),
	'items' => array(
		0 => array(
			'code' => '04.7.one_col_fix_with_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-NEWS3--TEXT_1"),
				),
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-NEWS3--TEXT_2"),
				),
				'.landing-block-node-text' => array(
					0 => ' ',
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-color-black g-mb-minus-10 g-text-transform-none',
				),
				'.landing-block-node-subtitle' => array(
					0 => 'landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-black',
				),
				'.landing-block-node-inner' => array(
					0 => 'landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp animated g-bg-main g-pt-40 g-pb-10',
				),
			),
			'attrs' => array(),
		),
		1 => array(
			'code' => '31.3.two_cols_text_img_fix',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-NEWS3--TEXT_3"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-NEWS3--TEXT_4"),
				),
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => 'Image description',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/eshop/930x580/img3.jpg',
					),
				),
			),
			'style' => array(
				'.landing-block-node-text-container' => array(
					0 => 'landing-block-node-text-container js-animation slideInLeft col-md-6 g-pb-20 g-pb-0--md',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-700 mb-0 g-mb-15 g-font-size-30 g-text-transform-none',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-color-gray-dark-v4',
				),
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation slideInRight img-fluid',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-10 g-pb-20',
				),
			),
			'attrs' => array(),
		),
		2 => array(
			'code' => '04.4.one_col_big_with_img',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => ' ',
				),
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-NEWS3--TEXT_5"),
				),
				'.landing-block-node-mainimg' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1073/img1.jpg',
					),
				),
			),
			'style' => array(
				'.landing-block-node-subtitle' => array(
					0 => 'landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-white g-mb-20',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-color-white g-mb-minus-10 g-text-transform-none g-font-size-40',
				),
				'.landing-block-node-inner' => array(
					0 => 'landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-white',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation landing-block-node-mainimg u-bg-overlay g-bg-img-hero animated animation-none g-bg-primary-opacity-0_9--after g-pb-0 g-pt-20',
				),
			),
			'attrs' => array(),
		),
		3 => array(
			'code' => '34.3.four_cols_countdown',
			'cards' => array(
				'.landing-block-node-card' => 2,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => array(
					0 => 'landing-block-node-card-icon icon-clothes-070 u-line-icon-pro',
					1 => 'landing-block-node-card-icon icon-clothes-054 u-line-icon-pro',
				),
				'.landing-block-node-card-number' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-NEWS3--TEXT_6"),
					1 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-NEWS3--TEXT_7"),
				),
				'.landing-block-node-card-number-title' => array(
					0 => ' ',
					1 => ' ',
				),
				'.landing-block-node-card-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-NEWS3--TEXT_8"),
					1 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-NEWS3--TEXT_9"),
				),
			),
			'style' => array(
				'.landing-block-node-card' => array(
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 text-center g-mb-40 g-mb-0--lg col-lg-6',
				),
				'.landing-block-node-card-number' => array(
					0 => 'landing-block-node-card-number g-color-white mb-0 g-font-size-26',
				),
				'.landing-block-node-card-number-title' => array(
					0 => 'landing-block-node-card-number-title text-uppercase g-font-weight-700 g-font-size-11 g-color-white g-mb-20',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-color-white-opacity-0_6 mb-0 g-font-size-14',
				),
				'#wrapper' => array(
					0 => 'landing-block g-bg-primary g-pt-0 g-pb-20',
				),
			),
			'attrs' => array(),
		),
		4 => array(
			'code' => '31.4.two_cols_img_text_fix',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-NEWS3--TEXT_10"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-NEWS3--TEXT_11"),
				),
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => 'Image description',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/eshop/802x550/img1.jpg',
					),
				),
			),
			'style' => array(
				'.landing-block-node-text-container' => array(
					0 => 'landing-block-node-text-container js-animation slideInRight col-md-6',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-700 mb-0 g-mb-15 g-font-size-30 g-text-transform-none',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-color-gray-dark-v4',
				),
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation slideInLeft img-fluid',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-45 g-pb-55',
				),
			),
			'attrs' => array(),
		),
	),
);