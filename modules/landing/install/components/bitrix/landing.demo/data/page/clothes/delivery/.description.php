<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'code' => 'clothes/delivery',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CLOTHES-DELIVERY--NAME'),
	'description' => null,
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'RULE' => null,
		'ADDITIONAL_FIELDS' => array(
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/clothes/delivery/preview.jpg',
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
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-DELIVERY--TEXT_1"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-DELIVERY--TEXT_1"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-mb-minus-10 g-color-black g-font-size-40 g-font-montserrat g-text-transform-none',
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
					0 => 'landing-block js-animation animated g-pt-40 g-pb-10 g-bg-gray-light-v5 animation-none',
				),
			),
			'attrs' => array(),
		),
		1 => array(
			'code' => '34.4.two_cols_with_text_and_icons',
			'cards' => array(
				'.landing-block-node-card' => 2,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => array(
					0 => 'landing-block-node-card-icon icon-transport-021 u-line-icon-pro',
					1 => 'landing-block-node-card-icon icon-hotel-restaurant-112 u-line-icon-pro',
				),
				'.landing-block-node-card-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-DELIVERY--TEXT_2"),
					1 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-DELIVERY--TEXT_3"),
				),
				'.landing-block-node-card-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-DELIVERY--TEXT_2"),
					1 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-DELIVERY--TEXT_3"),
				),
			),
			'style' => array(
				'.landing-block-node-card' => array(
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 col-lg-6 g-mb-40 animated',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-font-size-default g-color-gray-dark-v2 mb-0 g-font-open-sans g-font-size-14',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title h5 g-font-weight-800 g-font-size-22 g-text-transform-none g-font-open-sans',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-20 g-bg-gray-light-v5 g-pb-10',
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
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-DELIVERY--TEXT_4"),
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
					0 => 'landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-color-white g-mb-minus-10 g-font-size-40 g-font-montserrat g-text-transform-none',
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
		3 => array(
			'code' => '31.3.two_cols_text_img_fix',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-DELIVERY--TEXT_5"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-DELIVERY--TEXT_4"),
				),
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => 'Image description',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/eshop/476x399/img1.jpg',
					),
				),
			),
			'style' => array(
				'.landing-block-node-text-container' => array(
					0 => 'landing-block-node-text-container js-animation slideInLeft col-md-6 g-pb-20 g-pb-0--md animated',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-700 g-font-size-26 mb-0 g-mb-15 g-text-transform-none g-font-montserrat',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-font-open-sans g-font-size-14 g-color-black-opacity-0_9',
				),
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation slideInRight img-fluid animated',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-60 g-pb-55',
				),
			),
			'attrs' => array(),
		),
		4 => array(
			'code' => '04.4.one_col_big_with_img',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => ' ',
				),
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-DELIVERY--TEXT_6"),
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
					0 => 'landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-color-white g-mb-minus-10 g-font-size-40 g-text-transform-none g-font-montserrat',
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
		5 => array(
			'code' => '31.4.two_cols_img_text_fix',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-DELIVERY--TEXT_7"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-DELIVERY--TEXT_5"),
				),
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => 'Image description',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/eshop/476x399/img2.jpg',
					),
				),
			),
			'style' => array(
				'.landing-block-node-text-container' => array(
					0 => 'landing-block-node-text-container js-animation slideInRight col-md-6 animated',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-700 g-font-size-26 mb-0 g-mb-15 g-text-transform-none g-font-montserrat',
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
		6 => array(
			'code' => '27.4.one_col_fix_text',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-DELIVERY--TEXT_6"),
				),
			),
			'style' => array(
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-font-size-14 g-color-gray-dark-v3',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation animated g-pb-0 g-pt-20 fadeIn',
				),
			),
			'attrs' => array(),
		),
		7 => array(
			'code' => '14.2contacts_3_cols',
			'cards' => array(
				'.landing-block-card' => 3,
			),
			'nodes' => array(
				'.landing-block-node-linkcontact-icon' => array(
					0 => 'icon-call-in',
					1 => 'icon-envelope',
				),
				'.landing-block-node-contact-icon' => array(
					0 => 'fa fa-skype',
				),
				'.landing-block-node-linkcontact-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-DELIVERY--TEXT_8"),
					1 => 'Email',
				),
				'.landing-block-node-contact-title' => array(
					0 => 'Skype',
				),
				'.landing-block-node-linkcontact-link' => array(
					0 => array(
						'href' => 'tel:+74952128506',
					),
					1 => array(
						'href' => 'mailto:info@company24.com',
					),
				),
				'.landing-block-node-linkcontact-text' => array(
					0 => '+7 (495) 212 85 06',
					1 => 'info@company24.com',
				),
				'.landing-block-node-contact-text' => array(
					0 => '@company24',
				),
			),
			'style' => array(
				'.landing-block-card' => array(
					0 => 'landing-block-card js-animation fadeIn landing-block-node-contact g-brd-between-cols col-sm-6 col-md-6 col-lg-4 g-brd-primary g-px-15 g-py-30 g-py-0--md g-mb-15',
				),
				'.landing-block-node-contact-title' => array(
					0 => 'landing-block-node-contact-title d-block text-uppercase g-font-size-14 g-color-main g-mb-5',
				),
				'.landing-block-node-contact-text' => array(
					0 => 'landing-block-node-contact-text g-font-size-14 g-font-weight-700 ',
				),
				'.landing-block-node-linkcontact-title' => array(
					0 => 'landing-block-node-linkcontact-title d-block text-uppercase g-font-size-14 g-color-main g-mb-5',
				),
				'.landing-block-node-linkcontact-text' => array(
					0 => 'landing-block-node-linkcontact-text g-text-decoration-none g-text-underline--hover g-font-size-14 g-font-weight-700 ',
				),
				'.landing-block-node-contact-icon-container' => array(
					0 => 'landing-block-node-contact-icon-container d-block g-color-primary g-font-size-50 g-line-height-1 g-mb-20',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-40 g-pb-25 text-center',
				),
			),
			'attrs' => array(),
		),
	),
);