<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'code' => 'clothes/catalog',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CLOTHES-CATALOG--NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'RULE' => '(.*?)',
		'ADDITIONAL_FIELDS' => array(
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/clothes/catalog/preview.jpg',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => 'travel',
			'THEME_CODE_TYPO' => 'travel',
		),
	),
	'layout' => array(
		'code' => 'without_left',
		'ref' => array(
			2 => 'clothes/filter',
			3 => 'clothes/footer',
			1 => 'clothes/header',
		),
	),
	'items' => array(
		0 => array(
			'code' => 'store.catalog.list',
			'cards' => array(),
			'nodes' => array(),
			'style' => array(
				'.landing-component' => array(
					0 => 'landing-component',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-30 g-pb-20',
				),
			),
			'attrs' => array(
				'bitrix:catalog.section' => array(),
			),
		),
		1 => array(
			'code' => '04.7.one_col_fix_with_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-CATALOG--TEXT_1"),
				),
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-CATALOG--TEXT_2"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-CATALOG--TEXT_1"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-color-black g-mb-minus-10 g-font-montserrat g-text-transform-none',
				),
				'.landing-block-node-subtitle' => array(
					0 => 'landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-primary g-mb-15 g-font-open-sans g-text-transform-none',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-font-size-16 g-color-black-opacity-0_9 g-font-open-sans',
				),
				'.landing-block-node-inner' => array(
					0 => 'landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation g-pt-20 animated g-bg-main g-pb-0 animation-none',
				),
			),
			'attrs' => array(),
		),
		2 => array(
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
					0 => 'landing-block-node-card col-md-4 col-lg-2 g-flex-centered g-brd-bottom g-brd-right g-brd-gray-light-v4 g-py-50',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation text-center animated g-pt-0 fadeInUp g-pb-20',
				),
			),
			'attrs' => array(),
		),
		3 => array(
			'code' => '43.2.three_tiles_with_img_zoom',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-subtitle1' => array(
					0 => '<span style="font-weight: normal;">'. Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-CATALOG--TEXT_4").'</span>',
				),
				'.landing-block-node-title1' => array(
					0 => '<span style="font-weight: bold;">'. Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-CATALOG--TEXT_5").'</span>',
				),
				'.landing-block-node-button1' => array(
					0 => array(
						'text' => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-CATALOG--TEXT_6"),
						'href' => '#landing@landing[clothes/mainpage]',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => NULL,
							'data-url' => NULL,
						),
					),
				),
				'.landing-block-node-img1' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/eshop/570x670/img2.jpg',
					),
				),
				'.landing-block-node-subtitle2' => array(
					0 => '<span style="font-weight: normal;">'. Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-CATALOG--TEXT_7").'</span>',
				),
				'.landing-block-node-title2' => array(
					0 => '<span style="font-weight: bold;">'. Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-CATALOG--TEXT_8").'</span>',
				),
				'.landing-block-node-text2' => array(
					0 => '<span style="font-weight: normal;">'. Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-CATALOG--TEXT_9").'</span>',
				),
				'.landing-block-node-img2' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/eshop/570x670/img1.jpg',
					),
				),
				'.landing-block-node-title-mini' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-CATALOG--TEXT_10"),
					1 => '
								1+1=1',
				),
				'.landing-block-node-text-mini' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-CATALOG--TEXT_2"),
					1 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-CATALOG--TEXT_3"),
				),
				'.landing-block-node-img-mini' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/eshop/390x190/img1.jpg',
					),
				),
			),
			'style' => array(
				'.landing-block-node-block' => array(
					0 => 'landing-block-node-block js-animation fadeInUp text-center u-block-hover u-bg-overlay g-color-white g-bg-img-hero g-bg-black-opacity-0_3--after animated',
				),
				'.landing-block-node-subtitle1' => array(
					0 => 'landing-block-node-subtitle1 g-font-weight-700 g-color-white g-brd-bottom g-brd-2 g-mb-20 g-font-open-sans g-font-size-16 g-text-transform-none g-brd-transparent',
				),
				'.landing-block-node-title1' => array(
					0 => 'landing-block-node-title1 g-line-height-1 g-font-weight-700 g-mb-30 g-font-montserrat g-font-size-30 g-text-transform-none',
				),
				'.landing-block-node-subtitle2' => array(
					0 => 'landing-block-node-subtitle2 g-font-weight-700 g-font-size-16 g-color-white g-mb-5 g-font-open-sans g-text-transform-none',
				),
				'.landing-block-node-title2' => array(
					0 => 'landing-block-node-title2 g-line-height-1 g-font-weight-700 g-mb-10 g-font-montserrat g-text-transform-none g-font-size-30',
				),
				'.landing-block-node-button1' => array(
					0 => 'landing-block-node-button1 btn btn-md text-uppercase u-btn-primary g-font-weight-700 g-font-size-11 g-brd-none rounded-0 g-py-10 g-px-25',
				),
				'.landing-block-node-text2' => array(
					0 => 'landing-block-node-text2 g-font-weight-700 g-font-size-16 g-color-white mb-0 g-font-open-sans g-text-transform-none',
				),
				'.landing-block-node-title-mini' => array(
					0 => 'landing-block-node-title-mini g-font-weight-700 g-color-white g-mb-10 g-font-montserrat g-text-transform-none g-font-size-20',
				),
				'.landing-block-node-text-mini' => array(
					0 => 'landing-block-node-text-mini g-color-white mb-0 g-font-open-sans g-font-size-14',
				),
				'.landing-block-node-img-mini' => array(
					0 => 'landing-block-node-img-mini w-100 u-block-hover__main--zoom-v1',
				),
				'.landing-block-node-bg-mini' => array(
					0 => 'landing-block-node-bg-mini js-animation fadeInUp text-center u-block-hover g-color-white g-bg-primary g-mb-30 animated',
				),
				'.landing-block-node-button1-container' => array(
					0 => 'landing-block-node-button1-container',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-20 g-pb-20',
				),
			),
			'attrs' => array(),
		),
	),
);