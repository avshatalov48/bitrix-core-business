<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'code' => 'clothes/faq',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CLOTHES-FAQ--NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'RULE' => NULL,
		'ADDITIONAL_FIELDS' => array(
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/clothes/faq/preview.jpg',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => 'travel',
			'THEME_CODE_TYPO' => 'travel',
		),
	),
	'layout' => array(
		'code' => 'header_footer',
		'ref' => array(
			1 => 'clothes/header',
			2 => 'clothes/footer',
		),
	),
	'items' => array(
		0 => array(
			'code' => '04.7.one_col_fix_with_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => ' ',
				),
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FAQ--TEXT_1"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-FAQ--TEXT_1"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-mb-minus-10 g-color-black g-font-montserrat g-text-transform-none g-font-size-40',
				),
				'.landing-block-node-subtitle' => array(
					0 => 'landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-font-open-sans g-font-size-16 g-color-black-opacity-0_9',
				),
				'.landing-block-node-inner' => array(
					0 => 'landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation animated g-pb-10 g-bg-gray-light-v5 g-pt-40 animation-none',
				),
			),
			'attrs' => array(),
		),
		1 => array(
			'code' => '31.3.two_cols_text_img_fix',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FAQ--TEXT_2"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-FAQ--TEXT_2"),
				),
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => 'Image description',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/eshop/600x408/img1.jpg',
					),
				),
			),
			'style' => array(
				'.landing-block-node-text-container' => array(
					0 => 'landing-block-node-text-container js-animation slideInLeft col-md-6 g-pb-20 g-pb-0--md animated',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-700 mb-0 g-mb-15 g-text-transform-none g-font-size-30 g-font-montserrat',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-font-open-sans g-font-size-14 g-color-black-opacity-0_9',
				),
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation slideInRight img-fluid animated',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-50 g-pb-50',
				),
			),
			'attrs' => array(),
		),
		2 => array(
			'code' => '31.4.two_cols_img_text_fix',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FAQ--TEXT_3"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-FAQ--TEXT_3"),
				),
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => 'Image description',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/eshop/600x412/img1.jpg',
					),
				),
			),
			'style' => array(
				'.landing-block-node-text-container' => array(
					0 => 'landing-block-node-text-container js-animation slideInRight col-md-6 animated',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-700 mb-0 g-mb-15 g-text-transform-none g-font-montserrat g-font-size-30',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-font-open-sans g-font-size-14 g-color-black-opacity-0_9',
				),
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation slideInLeft img-fluid animated',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-50 g-pb-50',
				),
			),
			'attrs' => array(),
		),
		3 => array(
			'code' => '31.3.two_cols_text_img_fix',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FAQ--TEXT_4"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-FAQ--TEXT_4"),
				),
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => 'Image description',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/eshop/600x468/img1.jpg',
					),
				),
			),
			'style' => array(
				'.landing-block-node-text-container' => array(
					0 => 'landing-block-node-text-container js-animation slideInLeft col-md-6 g-pb-20 g-pb-0--md animated',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-700 mb-0 g-mb-15 g-font-montserrat g-text-transform-none g-font-size-30',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-font-open-sans g-font-size-14 g-color-black-opacity-0_9',
				),
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation slideInRight img-fluid animated',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-50 g-pb-50',
				),
			),
			'attrs' => array(),
		),
	),
);