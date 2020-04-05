<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(dirname(__FILE__).'/.description-nottranslate.php');

return array(
	'parent' => 'store-mini-catalog',
	'code' => 'store-mini-catalog/handmade',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE_TXT_NEW'),
	'description' => Loc::getMessage('LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE_DESC'),
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'RULE' => null,
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => 'event',
			'THEME_CODE_TYPO' => 'event',
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/store-mini-catalog/preview.jpg',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
		),
	),
	'layout' => array(),
	'items' => array(
		0 => array(
			'code' => '46.9.cover_bgimg_vertical_slider',
			'cards' => array(
				'.landing-block-node-card' => 1,
			),
			'nodes' => array(
				'.landing-block-node-card-img' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1620x1080/artem-bali-623185-unsplash.jpg',
					),
				),
				'.landing-block-node-card-subtitle' => array(
					0 => '<span style="font-weight: bold;font-style: italic;">'. Loc::getMessage("LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE_TXT_2").'</span>',
				),
				'.landing-block-node-card-title' => array(
					0 => '<span style="font-weight: normal;">'. Loc::getMessage("LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE_TXT_3").'</span>',
				),
				'.landing-block-node-card-button' => array(
					0 => array(
						'text' => Loc::getMessage("LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE_TXT_4"),
						'href' => '#block@block[store.catalog.list]',
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
					0 => 'landing-block-node-card-subtitle h6 g-color-white g-mb-10 g-mb-25--md g-font-cormorant-infant g-font-size-60',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title g-line-height-1_2 g-font-weight-700 g-color-white mb-0 g-mb-35--md g-text-transform-none g-font-open-sans g-font-size-18',
				),
				'.landing-block-node-card-button' => array(
					0 => 'landing-block-node-card-button btn btn-lg g-mt-20 g-mt-0--md text-uppercase u-btn-primary g-font-weight-700 g-font-size-12 g-py-15 g-px-40',
				),
				'.landing-block-node-card-img' => array(
					0 => 'landing-block-node-card-img g-flex-centered g-bg-cover g-bg-pos-center g-bg-img-hero g-min-height-70vh g-bg-black-opacity-0_5--after',
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
			'code' => '47.1.title_with_icon',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE_TXT_5"),
				),
				'.landing-block-node-icon' => array(
					0 => 'landing-block-node-icon fa fa-heart g-font-size-8',
					1 => 'landing-block-node-icon fa fa-heart g-font-size-11',
					2 => 'landing-block-node-icon fa fa-heart g-font-size-default',
					3 => 'landing-block-node-icon fa fa-heart g-font-size-11',
					4 => 'landing-block-node-icon fa fa-heart g-font-size-8',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE__TXT_1"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title js-animation fadeInUp u-heading-v7__title g-font-size-60 g-font-cormorant-infant font-italic g-font-weight-600 g-mb-20 animated g-color-black-opacity-0_9',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text js-animation fadeInUp mb-0 g-pb-1 animated g-font-open-sans g-color-gray-dark-v4',
				),
				'.landing-block-node-icon' => array(
					0 => 'landing-block-node-icon fa fa-heart g-font-size-8',
					1 => 'landing-block-node-icon fa fa-heart g-font-size-11',
					2 => 'landing-block-node-icon fa fa-heart g-font-size-default',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pb-20 g-pt-30',
				),
			),
			'attrs' => array(),
		),
		2 => array(
			'code' => '34.4.two_cols_with_text_and_icons',
			'cards' => array(
				'.landing-block-node-card' => 3,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => array(
					0 => 'landing-block-node-card-icon icon-diamond',
					1 => 'landing-block-node-card-icon icon-emotsmile',
					2 => 'landing-block-node-card-icon icon-star',
				),
				'.landing-block-node-card-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE__TXT_2"),
					1 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE__TXT_3"),
					2 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE__TXT_4"),
				),
				'.landing-block-node-card-title' => array(
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE__TXT_5"),
					1 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE__TXT_6"),
					2 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE__TXT_7"),
				),
			),
			'style' => array(
				'.landing-block-node-card' => array(
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 g-mb-40 animated g-min-height-$1vh col-lg-4',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-font-size-default g-color-gray-dark-v2 mb-0 g-font-open-sans g-font-size-14',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title h5 g-font-weight-800 g-text-transform-none g-font-cormorant-infant g-font-size-25',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-0 g-pb-5',
				),
			),
			'attrs' => array(),
		),
		3 => array(
			'code' => '47.1.title_with_icon',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE_TXT_6"),
				),
				'.landing-block-node-icon' => array(
					0 => 'landing-block-node-icon fa fa-heart g-font-size-8',
					1 => 'landing-block-node-icon fa fa-heart g-font-size-11',
					2 => 'landing-block-node-icon fa fa-heart g-font-size-default',
					3 => 'landing-block-node-icon fa fa-heart g-font-size-11',
					4 => 'landing-block-node-icon fa fa-heart g-font-size-8',
				),
				'.landing-block-node-text' => array(
					0 => ' ',
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title js-animation fadeInUp u-heading-v7__title g-font-size-60 g-font-cormorant-infant font-italic g-font-weight-600 g-mb-20 animated g-color-gray-light-v5',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text js-animation fadeInUp g-color-gray-dark-v5 mb-0 g-pb-1 animated',
				),
				'.landing-block-node-icon' => array(
					0 => 'landing-block-node-icon fa fa-heart g-font-size-8',
					1 => 'landing-block-node-icon fa fa-heart g-font-size-11',
					2 => 'landing-block-node-icon fa fa-heart g-font-size-default',
				),
				'#wrapper' => array(
					0 => 'landing-block g-theme-business-bg-blue-dark-v1-opacity-0_9 g-pt-35 g-pb-5',
				),
			),
			'attrs' => array(),
		),
		4 => array(
			'code' => 'store.catalog.list',
			'cards' => array(),
			'nodes' => array(
				'bitrix:catalog.section' => array(
					'PAGE_ELEMENT_COUNT' => 8,
					'PRODUCT_ROW_VARIANTS' => [3, 3],//[{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false}]
				),
			),
			'style' => array(
				'.landing-component' => array(
					0 => 'landing-component',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-0 g-pb-35',
				),
			),
			'attrs' => array(
				'bitrix:landing.blocks.cmpfilter' => array(),
				'bitrix:catalog.section' => array(),
			),
		),
		5 => array(
			'code' => '47.1.title_with_icon',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE__TXT_8"),
				),
				'.landing-block-node-icon' => array(
					0 => 'landing-block-node-icon fa fa-heart g-font-size-8',
					1 => 'landing-block-node-icon fa fa-heart g-font-size-11',
					2 => 'landing-block-node-icon fa fa-heart g-font-size-default',
					3 => 'landing-block-node-icon fa fa-heart g-font-size-11',
					4 => 'landing-block-node-icon fa fa-heart g-font-size-8',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE__TXT_9"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title js-animation fadeInUp u-heading-v7__title g-font-size-60 g-font-cormorant-infant font-italic g-font-weight-600 g-mb-20 animated g-color-lightblue',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text js-animation fadeInUp mb-0 g-pb-1 animated g-font-open-sans g-color-lightblue-v1',
				),
				'.landing-block-node-icon' => array(
					0 => 'landing-block-node-icon fa fa-heart g-font-size-8',
					1 => 'landing-block-node-icon fa fa-heart g-font-size-11',
					2 => 'landing-block-node-icon fa fa-heart g-font-size-default',
				),
				'#wrapper' => array(
					0 => 'landing-block g-theme-business-bg-blue-dark-v1-opacity-0_9 g-pr-0 g-pt-35 g-pb-25',
				),
			),
			'attrs' => array(),
		),
		6 => array(
			'code' => '31.4.two_cols_img_text_fix',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="font-style: italic;">'. Loc::getMessage("LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE_TXT_7").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE__TXT_10"),
				),
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => 'Image description',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/540x360/grace-p-197524-unsplash.jpg',
					),
				),
			),
			'style' => array(
				'.landing-block-node-text-container' => array(
					0 => 'landing-block-node-text-container js-animation slideInRight col-md-6 animated',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-700 mb-0 g-mb-15 g-font-cormorant-infant g-text-transform-none g-font-size-40',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-color-gray-dark-v4 g-font-open-sans g-font-size-16',
				),
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation slideInLeft img-fluid animated',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-45 g-pb-45',
				),
			),
			'attrs' => array(),
		),
		7 => array(
			'code' => '47.1.title_with_icon',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE_TXT_8"),
				),
				'.landing-block-node-icon' => array(
					0 => 'landing-block-node-icon fa fa-heart g-font-size-8',
					1 => 'landing-block-node-icon fa fa-heart g-font-size-11',
					2 => 'landing-block-node-icon fa fa-heart g-font-size-default',
					3 => 'landing-block-node-icon fa fa-heart g-font-size-11',
					4 => 'landing-block-node-icon fa fa-heart g-font-size-8',
				),
				'.landing-block-node-text' => array(
					0 => ' ',
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title js-animation fadeInUp u-heading-v7__title g-font-size-60 g-font-cormorant-infant font-italic g-font-weight-600 g-mb-20 animated g-color-gray-light-v5 g-line-height-1_1',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text js-animation fadeInUp g-color-gray-dark-v5 mb-0 g-pb-1 animated',
				),
				'.landing-block-node-icon' => array(
					0 => 'landing-block-node-icon fa fa-heart g-font-size-8',
					1 => 'landing-block-node-icon fa fa-heart g-font-size-11',
					2 => 'landing-block-node-icon fa fa-heart g-font-size-default',
				),
				'#wrapper' => array(
					0 => 'landing-block g-theme-business-bg-blue-dark-v1-opacity-0_9 g-pt-35 g-pb-7',
				),
			),
			'attrs' => array(),
		),
		8 => array(
			'code' => '32.6.img_grid_4cols_1',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/960x960/image-2018-05-14-10-38-15.jpg',
					),
					1 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/960x960/image-2018-05-14-10-40-56.jpg',
					),
					2 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/960x960/image-2018-05-14-10-42-00.jpg',
					),
					3 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/960x960/image-2018-05-14-10-41-29.jpg',
					),
				),
				'.landing-block-node-img-title' => array(
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE__TXT_11"),
					1 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE__TXT_12"),
					2 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE__TXT_13"),
					3 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE__TXT_14"),
				),
			),
			'style' => array(
				'.landing-block-node-img-title' => array(
					0 => 'landing-block-node-img-title g-flex-middle-item text-center h3 g-color-white g-line-height-1_4 g-font-size-20 g-font-open-sans g-letter-spacing-1 g-text-transform-none',
				),
				'.landing-block-node-img-container-leftleft' => array(
					0 => 'landing-block-node-img-container landing-block-node-img-container-leftleft js-animation fadeInLeft h-100 g-pos-rel g-parent u-block-hover animated',
				),
				'.landing-block-node-img-container-left' => array(
					0 => 'landing-block-node-img-container landing-block-node-img-container-left js-animation fadeInLeft h-100 g-pos-rel g-parent u-block-hover animated',
				),
				'.landing-block-node-img-container-right' => array(
					0 => 'landing-block-node-img-container landing-block-node-img-container-right js-animation fadeInRight h-100 g-pos-rel g-parent u-block-hover animated',
				),
				'.landing-block-node-img-container-rightright' => array(
					0 => 'landing-block-node-img-container landing-block-node-img-container-rightright js-animation fadeInRight h-100 g-pos-rel g-parent u-block-hover animated',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-45 g-pb-35',
				),
			),
			'attrs' => array(),
		),
		9 => array(
			'code' => '47.1.title_with_icon',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE_TXT_9"),
				),
				'.landing-block-node-icon' => array(
					0 => 'landing-block-node-icon fa fa-heart g-font-size-8',
					1 => 'landing-block-node-icon fa fa-heart g-font-size-11',
					2 => 'landing-block-node-icon fa fa-heart g-font-size-default',
					3 => 'landing-block-node-icon fa fa-heart g-font-size-11',
					4 => 'landing-block-node-icon fa fa-heart g-font-size-8',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_STORE_MINI_CATALOG_HANDMADE__TXT_15"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title js-animation fadeInUp u-heading-v7__title g-font-size-60 g-font-cormorant-infant font-italic g-font-weight-600 g-mb-20 animated g-color-gray-light-v5',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text js-animation fadeInUp mb-0 g-pb-1 animated g-color-lightblue-v1 g-font-open-sans',
				),
				'.landing-block-node-icon' => array(
					0 => 'landing-block-node-icon fa fa-heart g-font-size-8',
					1 => 'landing-block-node-icon fa fa-heart g-font-size-11',
					2 => 'landing-block-node-icon fa fa-heart g-font-size-default',
				),
				'#wrapper' => array(
					0 => 'landing-block g-theme-business-bg-blue-dark-v1-opacity-0_9 g-pt-40 g-pb-0',
				),
			),
			'attrs' => array(),
		),
		10 => array(
			'code' => '14.2contacts_3_cols',
			'anchor' => '',
			'repo_block' => array(),
			'cards' => array(
				'.landing-block-card' => 3,
			),
			'nodes' => array(
				'.landing-block-node-linkcontact-icon' => array(
					0 => 'landing-block-node-linkcontact-icon icon-call-in',
					1 => 'landing-block-node-linkcontact-icon icon-envelope',
				),
				'.landing-block-node-linkcontact-link' => array(
					0 => array(
						'href' => 'tel:1-800-643-4500',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
					1 => array(
						'href' => 'mailto:info@company24.com',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
				),
				'.landing-block-node-linkcontact-title' => array(
					0 => 'Phone number',
					1 => 'Email',
				),
				'.landing-block-node-linkcontact-text' => array(
					0 => '1-800-643-4500',
					1 => 'info@company24.com',
				),
				'.landing-block-node-contact-icon' => array(
					0 => 'landing-block-node-contact-icon icon-earphones-alt d-inline-block',
				),
				'.landing-block-node-contact-title' => array(
					0 => 'Toll free',
				),
				'.landing-block-node-contact-text' => array(
					0 => '@company24',
				),
				'.landing-block-node-contact-img' => array(),
			),
			'style' => array(
				'.landing-block-card' => array(
					0 => 'landing-block-card js-animation fadeIn landing-block-node-contact g-brd-between-cols col-sm-6 col-md-6 col-lg-4 g-brd-primary g-px-15 g-py-30 g-py-0--md g-mb-15 g-min-height-$1vh',
					1 => 'landing-block-card js-animation fadeIn landing-block-node-contact g-brd-between-cols col-sm-6 col-md-6 col-lg-4 g-brd-primary g-px-15 g-py-30 g-py-0--md g-mb-15 g-min-height-$1vh',
					2 => 'landing-block-card js-animation fadeIn landing-block-node-contact g-brd-between-cols col-sm-6 col-md-6 col-lg-4 g-brd-primary g-px-15 g-py-30 g-py-0--md g-mb-15 g-min-height-$1vh',
				),
				'.landing-block-node-contact-title' => array(
					0 => 'landing-block-node-contact-title d-block text-uppercase g-font-size-14 g-color-main g-mb-5',
				),
				'.landing-block-node-contact-text' => array(
					0 => 'landing-block-node-contact-text g-font-size-14 g-font-weight-700 ',
				),
				'.landing-block-node-linkcontact-title' => array(
					0 => 'landing-block-node-linkcontact-title d-block text-uppercase g-font-size-14 g-color-main g-mb-5',
					1 => 'landing-block-node-linkcontact-title d-block text-uppercase g-font-size-14 g-color-main g-mb-5',
				),
				'.landing-block-node-linkcontact-text' => array(
					0 => 'landing-block-node-linkcontact-text g-text-decoration-none g-text-underline--hover g-font-size-14 g-font-weight-700 ',
					1 => 'landing-block-node-linkcontact-text g-text-decoration-none g-text-underline--hover g-font-size-14 g-font-weight-700 ',
				),
				'.landing-block-node-contact-icon-container' => array(
					0 => 'landing-block-node-contact-icon-container d-block g-color-primary g-font-size-50 g-line-height-1 g-mb-20',
					1 => 'landing-block-node-contact-icon-container d-block g-color-primary g-font-size-50 g-line-height-1 g-mb-20',
					2 => 'landing-block-node-contact-icon-container d-block g-color-primary g-font-size-50 g-line-height-1 g-mb-20',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-25 g-pb-25 text-center g-theme-business-bg-blue-dark-v1-opacity-0_9',
				),
			),
			'attrs' => array(),
		),
	),
);