<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'parent' => 'clothes',
	'code' => 'clothes/mainpage',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_STORE_CLOTHES-MAINPAGE--DESC'),
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'RULE' => null,
		'ADDITIONAL_FIELDS' => array(
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/clothes/mainpage/preview.jpg',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => 'travel',
		),
	),
	'layout' => array(
		'code' => 'header_footer',
		'ref' => array(
			1 => 'clothes/header_main',
			2 => 'clothes/footer',
		),
	),
	'items' => array(
		0 => array(
			'code' => '46.9.cover_bgimg_vertical_slider',
			'cards' => array(
				'.landing-block-node-card' => 2,
			),
			'nodes' => array(
				'.landing-block-node-card-img' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/eshop/801x293/img1.jpg',
					),
					1 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/eshop/801x293/img2.jpg',
					),
				),
				'.landing-block-node-card-subtitle' => array(
					0 => '<span style="font-weight: 700;">' . Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_1") . '</span>',
					1 => '<span style="font-weight: bold;">' . Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_2") . '</span>',
				),
				
				'.landing-block-node-card-title' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_1"),
					1 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_2"),
				),
				
				'.landing-block-node-card-button' => array(
					0 => array(
						'text' => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_3"),
						'href' => '#block@block[04.7.one_col_fix_with_title_and_text_2]',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
					1 => array(
						'text' => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_3"),
						'href' => '#landing@landing[clothes/catalog]',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
				),
			),
			'style' => array(
				'.landing-block-node-text-container' => array(
					0 => 'landing-block-node-text-container js-animation fadeIn container text-center g-z-index-1 animated',
				),
				'.landing-block-node-card-subtitle' => array(
					0 => 'landing-block-node-card-subtitle h6 g-color-white g-mb-10 g-mb-25--md g-text-transform-none g-font-size-40',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title g-line-height-1_2 g-font-weight-700 g-color-white mb-0 g-mb-35--md g-text-transform-none g-font-size-16',
				),
				'.landing-block-node-card-button' => array(
					0 => 'landing-block-node-card-button btn g-btn-type-solid g-btn-size-md g-btn-px-l g-mt-20 g-mt-0--md text-uppercase g-btn-primary g-py-7 g-py-15--md rounded-0',
				),
				'.landing-block-node-card-img' => array(
					0 => 'landing-block-node-card-img g-flex-centered g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_4--after g-min-height-60vh h-100 g-pt-10 g-pb-30',
				),
				'.landing-block-node-card-button-container' => array(
					0 => 'landing-block-node-card-button-container',
				),
				'#wrapper' => array(
					0 => 'landing-block',
				),
			),
			'attrs' => array(),
		),
		1 => array(
			'code' => '04.7.one_col_fix_with_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => ' ',
				),
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_4"),
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
					0 => 'landing-block-node-text g-color-gray-dark-v5 g-pb-1',
				),
				'.landing-block-node-inner' => array(
					0 => 'landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block l-d-xs-none js-animation fadeInUp g-bg-gray-light-v5 animated g-pt-40 g-pb-10',
				),
			),
			'attrs' => array(),
		),
		2 => array(
			'code' => '34.4.two_cols_with_text_and_icons',
			'cards' => array(
				'.landing-block-node-card' => 6,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => array(
					0 => 'landing-block-node-card-icon icon-finance-080 u-line-icon-pro',
					1 => 'landing-block-node-card-icon icon-hotel-restaurant-003 u-line-icon-pro',
					2 => 'landing-block-node-card-icon icon-education-129 u-line-icon-pro',
					3 => 'landing-block-node-card-icon icon-finance-216 u-line-icon-pro',
					4 => 'landing-block-node-card-icon icon-clothes-041 u-line-icon-pro',
					5 => 'landing-block-node-card-icon icon-hotel-restaurant-249 u-line-icon-pro',
				),
				'.landing-block-node-card-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_3"),
					1 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_4"),
					2 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_5"),
					3 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_6"),
					4 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_7"),
					5 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_8"),
				),
				'.landing-block-node-card-title' => array(
					0 => ' ',
					1 => ' ',
					2 => ' ',
					3 => ' ',
					4 => ' ',
					5 => ' ',
				),
			),
			'style' => array(
				'.landing-block-node-card' => array(
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 g-mb-40 animated col-lg-4 g-mb-0--last',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-color-gray-dark-v2 mb-0',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title h5 text-uppercase g-font-weight-800',
				),
				'#wrapper' => array(
					0 => 'landing-block l-d-xs-none g-pt-20 g-bg-gray-light-v5 g-pb-20',
				),
			),
			'attrs' => array(),
		),
		3 => array(
			'code' => '04.7.one_col_fix_with_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => ' ',
				),
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_5"),
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
					0 => 'landing-block-node-text g-color-gray-dark-v5 g-pb-1',
				),
				'.landing-block-node-inner' => array(
					0 => 'landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp animated g-pt-40 g-bg-main g-pb-10',
				),
			),
			'attrs' => array(),
		),
		4 => array(
			'code' => 'store.catalog.list',
			'cards' => array(),
			'nodes' => array(),
			'style' => array(
				'.landing-component' => array(
					0 => 'landing-component g-bg-main',
				),
				'#wrapper' => array(
					0 => 'landing-block g-bg-main g-pt-0 g-pb-40',
				),
			),
			'attrs' => array(
				'bitrix:catalog.section' => array(),
			),
		),
		5 => array(
			'code' => '04.4.one_col_big_with_img',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_8"),
				),
				'.landing-block-node-title' => array(
					0 => Loc::getMessage('NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_9'),
				),
				'.landing-block-node-mainimg' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/eshop/1080x356/img1.jpg',
					),
				),
			),
			'style' => array(
				'.landing-block-node-subtitle' => array(
					0 => 'landing-block-node-subtitle g-font-weight-700 g-color-white g-mb-20 g-text-transform-none g-font-size-40',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-color-white g-mb-minus-10 g-text-transform-none g-font-size-16',
				),
				'.landing-block-node-inner' => array(
					0 => 'landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-white',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation landing-block-node-mainimg u-bg-overlay g-bg-img-hero g-pt-40 g-pb-40 animated g-bg-black-opacity-0_6--after animation-none',
				),
			),
			'attrs' => array(),
		),
		6 => array(
			'code' => '04.7.one_col_fix_with_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => ' ',
				),
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_6"),
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
					0 => 'landing-block-node-text g-color-gray-dark-v5 g-pb-1',
				),
				'.landing-block-node-inner' => array(
					0 => 'landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp animated g-pt-40 g-bg-main g-pb-10',
				),
			),
			'attrs' => array(),
		),
		7 => array(
			'code' => 'store.catalog.list',
			'cards' => array(),
			'nodes' => array(),
			'style' => array(
				'.landing-component' => array(
					0 => 'landing-component g-bg-main',
				),
				'#wrapper' => array(
					0 => 'landing-block g-bg-main g-pt-0 g-pb-40',
				),
			),
			'attrs' => array(
				'bitrix:catalog.section' => array(),
			),
		),
		8 => array(
			'code' => '04.4.one_col_big_with_img',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => '<span style="font-weight: normal;">' . Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_7") . '</span>',
				),
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_13"),
				),
				'.landing-block-node-mainimg' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img32.jpg',
					),
				),
			),
			'style' => array(
				'.landing-block-node-subtitle' => array(
					0 => 'landing-block-node-subtitle g-font-weight-700 g-color-white g-mb-20 g-text-transform-none g-font-size-16',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-color-white g-mb-minus-10 g-text-transform-none g-font-size-40',
				),
				'.landing-block-node-inner' => array(
					0 => 'landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-white',
				),
				'#wrapper' => array(
					0 => 'landing-block landing-block-node-mainimg u-bg-overlay g-bg-img-hero g-pb-40 g-bg-black-opacity-0_6--after g-pl-0 g-pt-40 js-animation animated animation-none',
				),
			),
			'attrs' => array(),
		),
		9 => array(
			'code' => '04.7.one_col_fix_with_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => ' ',
				),
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_9"),
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
					0 => 'landing-block-node-text g-color-gray-dark-v5 g-pb-1',
				),
				'.landing-block-node-inner' => array(
					0 => 'landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation animated g-pt-40 g-bg-main g-pb-10 fadeInUp',
				),
			),
			'attrs' => array(),
		),
		10 => array(
			'code' => '30.2.three_cols_fix_img_and_links',
			'cards' => array(
				'.landing-block-card' => 3,
			),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/eshop/290x181/img1.jpg',
					),
					1 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/eshop/290x181/img3.jpg',
					),
					2 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/eshop/290x181/img2.jpg',
					),
				),
				'.landing-block-node-link' => array(
					0 => array(
						'text' => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_10"),
						'href' => '#landing@landing[clothes/news1]',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
					1 => array(
						'text' => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_11"),
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
					2 => array(
						'text' => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_12"),
						'href' => '#landing@landing[clothes/news3]',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
				),
				'.landing-block-node-link-more' => array(
					0 => array(
						'text' => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_10"),
						'href' => '#landing@landing[clothes/news1]',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
					1 => array(
						'text' => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_10"),
						'href' => '#landing@landing[clothes/news2]',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
					2 => array(
						'text' => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-MAINPAGE--TEXT_10"),
						'href' => '#landing@landing[clothes/news3]',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
				),
			),
			'style' => array(
				'.landing-block-card' => array(
					0 => 'landing-block-card col-sm-6 col-md-4 js-animation animated fadeInUp',
				),
				'.landing-block-node-link' => array(
					0 => 'landing-block-node-link u-link-v5 g-color-main g-color-primary--hover',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-30 g-pb-20',
				),
			),
			'attrs' => array(),
		),
		11 => array(
			'code' => '33.4.form_1_transparent_black_no_text_simple',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-bgimg' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img32.jpg',
					),
				),
			),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block g-pos-rel g-bg-primary-dark-v1 g-pt-30 g-pb-30 landing-block-node-bgimg g-bg-img-hero g-bg-cover g-bg-black-opacity-0_7--after',
				),
			),
			'attrs' => array(
				'.bitrix24forms' => array(),
			),
		),
	),
);