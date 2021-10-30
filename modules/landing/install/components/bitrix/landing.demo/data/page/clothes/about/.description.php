<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'code' => 'clothes/about',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CLOTHES-ABOUT--NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'RULE' => NULL,
		'ADDITIONAL_FIELDS' => array(
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/clothes/about/preview.jpg',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => 'travel',
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
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-ABOUT--TEXT_1"),
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
					0 => 'landing-block js-animation g-pt-20 g-pb-20 animated g-bg-main animation-none',
				),
			),
			'attrs' => array(),
		),
		1 => array(
			'code' => '31.4.two_cols_img_text_fix',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => ' ',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-ABOUT--TEXT_1"),
				),
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => 'Shop photo',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/eshop/630x450/img1.jpg',
					),
				),
			),
			'style' => array(
				'.landing-block-node-text-container' => array(
					0 => 'landing-block-node-text-container js-animation slideInRight col-md-6 animated',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title text-uppercase g-font-weight-700 g-font-size-26 mb-0 g-mb-15',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-color-black-opacity-0_9 g-font-size-14',
				),
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation slideInLeft img-fluid animated',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-20 g-pb-60',
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
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-ABOUT--TEXT_2"),
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
					0 => 'landing-block js-animation landing-block-node-mainimg u-bg-overlay g-bg-img-hero g-pt-40 animated animation-none g-bg-primary-opacity-0_9--after g-pb-0',
				),
			),
			'attrs' => array(),
		),
		3 => array(
			'code' => '34.3.four_cols_countdown',
			'cards' => array(
				'.landing-block-node-card' => 6,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => array(
					0 => 'landing-block-node-card-icon icon-hotel-restaurant-006 u-line-icon-pro',
					1 => 'landing-block-node-card-icon icon-finance-236 u-line-icon-pro',
					2 => 'landing-block-node-card-icon icon-clothes-094 u-line-icon-pro',
					3 => 'landing-block-node-card-icon icon-christmas-059 u-line-icon-pro',
					4 => 'landing-block-node-card-icon icon-finance-080 u-line-icon-pro',
					5 => 'landing-block-node-card-icon icon-finance-237 u-line-icon-pro',
				),
				'.landing-block-node-card-number' => array(
					0 => '<span style="font-weight: bold;">'.Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-ABOUT--TEXT_3").'</span>',
					1 => '<span style="font-weight: bold;">'.Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-ABOUT--TEXT_4").'</span>',
					2 => '<span style="font-weight: bold;">'.Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-ABOUT--TEXT_5").'</span>',
					3 => '<span style="font-weight: bold;">'.Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-ABOUT--TEXT_6").'</span>',
					4 => '<span style="font-weight: bold;">'.Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-ABOUT--TEXT_7").'</span>',
					5 => '<span style="font-weight: bold;">'.Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-ABOUT--TEXT_8").'</span>',
				),
				'.landing-block-node-card-number-title' => array(
					0 => ' ',
					1 => ' ',
					2 => ' ',
					3 => ' ',
					4 => ' ',
					5 => ' ',
				),
				'.landing-block-node-card-text' => array(
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
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 text-center g-mb-40 g-mb-0--lg animated col-lg-2',
				),
				'.landing-block-node-card-number' => array(
					0 => 'landing-block-node-card-number g-color-white mb-0 g-font-size-20',
				),
				'.landing-block-node-card-number-title' => array(
					0 => 'landing-block-node-card-number-title text-uppercase g-font-weight-700 g-font-size-11 g-color-white g-mb-20',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-color-white-opacity-0_6 mb-0',
				),
				'.landing-block-node-card-icon-container' => array(
					0 => 'landing-block-node-card-icon-container m-auto u-icon-v1 u-icon-size--lg g-color-white-opacity-0_7 g-mb-15',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pb-70 g-bg-primary g-pt-35',
				),
			),
			'attrs' => array(),
		),
		4 => array(
			'code' => '04.7.one_col_fix_with_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => ' ',
				),
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-ABOUT--TEXT_9"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-ABOUT--TEXT_2")
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-color-black g-mb-minus-10 g-text-transform-none',
				),
				'.landing-block-node-subtitle' => array(
					0 => 'landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15 g-text-transform-none',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-black-opacity-0_9',
				),
				'.landing-block-node-inner' => array(
					0 => 'landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation animated g-bg-main g-pb-0 animation-none g-pt-40',
				),
			),
			'attrs' => array(),
		),
		5 => array(
			'code' => '24.3.image_gallery_6_cols_fix_3',
			'cards' => array(
				'.landing-block-node-card' => 6,
			),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img1.png',
					),
					1 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img2.png',
					),
					2 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img3.png',
					),
					3 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img4.png',
					),
					4 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img4.png',
					),
					5 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img6.png',
					),
				),
				'.landing-block-card-logo-link' => array(
					0 => array(
						'text' => '
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img1.png" alt="" data-fileid="-1" />
				',
						'href' => 'https://bitrix24.ru',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => NULL,
							'data-url' => NULL,
						),
					),
					1 => array(
						'text' => '
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img2.png" alt="" data-fileid="-1" />
				',
						'href' => 'https://bitrix24.ru',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => NULL,
							'data-url' => NULL,
						),
					),
					2 => array(
						'text' => '
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img3.png" alt="" data-fileid="-1" />
				',
						'href' => 'https://bitrix24.ru',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => NULL,
							'data-url' => NULL,
						),
					),
					3 => array(
						'text' => '
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img4.png" alt="" data-fileid="-1" />
				',
						'href' => 'https://bitrix24.ru',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => NULL,
							'data-url' => NULL,
						),
					),
					4 => array(
						'text' => '
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img4.png" alt="" data-fileid="-1" />
				',
						'href' => 'https://bitrix24.ru',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => NULL,
							'data-url' => NULL,
						),
					),
					5 => array(
						'text' => '
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img6.png" alt="" data-fileid="-1" />
				',
						'href' => 'https://bitrix24.ru',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => NULL,
							'data-url' => NULL,
						),
					),
				),
			),
			'style' => array(
				'.landing-block-node-card' => array(
					0 => 'landing-block-node-card col-md-4 col-lg-2 d-flex flex-column align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation text-center animated g-pt-0 g-pb-20 fadeInLeft',
				),
			),
			'attrs' => array(),
		),
	),
);