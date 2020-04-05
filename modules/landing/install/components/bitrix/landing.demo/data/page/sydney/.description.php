<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

return array(
	'active' => false,	// dbg: not actual, remove if all OK
	'parent' => 'sydney',
	'code' => 'sydney',
	'name' => Loc::getMessage('LANDING_DEMO_B24SYD_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_B24SYD_DESCRIPTION'),
	'version' => 2,
	'sort' => 2,
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => '3corporate',
			'THEME_CODE_TYPO' => 'app',
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/sydney/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_B24SYD_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_B24SYD_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_B24SYD_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_B24SYD_DESCRIPTION'),
		),
	),
	'replace' => array(
		'#partner_id#' => \Bitrix\Main\Config\Option::get('bitrix24', 'partner_id', 0),
	),
	'layout' => array(),
	'items' => array(
		0 => array(
			'code' => '0.menu_14_music',
			'cards' => array(
				'.landing-block-node-menu-list-item' => 8,
			),
			'nodes' => array(
				'.landing-block-node-menu-list-item-link' => array(
					0 => array(
						'text' => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT1"),
						'href' => '#contactcenter',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
					1 => array(
						'text' => 'CRM',
						'href' => '#crm',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
					2 => array(
						'text' => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT2"),
						'href' => '#marketing',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
					3 => array(
						'text' => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT3"),
						'href' => '#analytics',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
					4 => array(
						'text' => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT4"),
						'href' => '#channels',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
					5 => array(
						'text' => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT5"),
						'href' => '#tasks',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
					6 => array(
						'text' => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT6"),
						'href' => '#sites',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
					7 => array(
						'text' => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT7"),
						'href' => '#lab',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
				),
				'.landing-block-node-menu-logo-link' => array(
					0 => array(
						'text' => '
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180" src="https://cdn.bitrix24.ru/b5391009/landing/09b/09bf1f78935efdaa64b93d5fc955a1c5/214124.png" alt="" data-fileid="8175" />
				',
						'href' => '#block28873',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
				),
				'.landing-block-node-menu-logo' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/09b/09bf1f78935efdaa64b93d5fc955a1c5/214124.png',
					),
				),
			),
			'style' => array(
				'.landing-block-node-menu-list-item-link' => array(
					0 => 'landing-block-node-menu-list-item-link nav-link p-0 g-font-open-sans g-font-size-11',
				),
				'#wrapper' => array(
					0 => 'landing-block landing-block-menu g-bg-gray-dark-v1 u-header u-header--floating u-header--floating-relative',
				),
			),
		),
		1 => array(
			'code' => '48.slider_with_video_on_bgimg',
			'cards' => array(
				'.landing-block-node-card' => 1,
			),
			'nodes' => array(
				'.landing-block-node-bgimg' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/96b/96b66d029a52b7959a11e62991b565a0/Depositphotos_70091959_l-2015+_2_-min.jpg',
					),
				),
				'.landing-block-node-card-button' => array(
					0 => array(
						'text' => '
						<img class="landing-block-node-card-icon d-block g-relative-centered--y mr-auto g-ml-18 g-height-14" src="https://cdn.bitrix24.site/bitrix/images/landing/play.png" />
					',
						'href' => 'https://www.youtube.com/watch?v=13ILnv1HPVw',
						'target' => '_popup',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => '//www.youtube.com/embed/13ILnv1HPVw?autoplay=0&controls=1&loop=0&rel=0&start=0&html5=1&v=q4d8g9Dn3ww',
						),
					),
				),
				'.landing-block-node-card-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT8"),
				),
				'.landing-block-node-card-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT9"),
				),
				'.landing-block-node-card-link' => array(
					0 => array(
						'text' => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT10"),
						'href' => 'https://www.youtube.com/watch?v=13ILnv1HPVw',
						'target' => '_popup',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => '//www.youtube.com/embed/13ILnv1HPVw?autoplay=1&controls=1&loop=0&rel=0&start=0&html5=1&v=13ILnv1HPVw',
						),
					),
				),
			),
			'style' => array(
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title text-uppercase g-font-weight-700 g-mb-20 g-color-white g-font-open-sans custom-text-shadow-1 g-font-size-50',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-mb-30 g-color-white g-font-size-30 g-font-open-sans custom-text-shadow-1',
				),
				'.landing-block-node-card-link' => array(
					0 => 'landing-block-node-card-link text-uppercase g-font-weight-700 g-color-primary g-font-size-16 g-font-open-sans custom-text-shadow-1',
				),
				'.landing-block-node-card-button' => array(
					0 => 'landing-block-node-card-button u-icon-v2 g-text-underline--none--hover
					u-block-hover--scale g-overflow-inherit g-bg-primary--hover rounded-circle
					g-cursor-pointer g-brd-around g-brd-5 g-brd-primary mb-3',
				),
				'.landing-block-node-card-button-container' => array(
					0 => 'landing-block-node-card-button-container',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation slideInRight landing-block-node-bgimg u-bg-overlay g-bg-img-hero g-bg-attachment-fixed g-pt-100 g-bg-black-opacity-0_4--after g-pb-50',
				),
			),
			'attrs' => array(),
		),
		2 => array(
			'code' => '25.one_col_fix_texts_blocks_slider',
			'cards' => array(
				'.landing-block-card-slider-element' => 2,
			),
			'nodes' => array(
				'.landing-block-node_bgimage' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/6a1/6a149f46db39ae34f2c3278184858dd8/Screen+Shot+2018-09-24+at+10.06.59.png',
					),
				),
				'.landing-block-node-element-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT11"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT11"). ' 2',
				),
				'.landing-block-node-element-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT12"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT12"),
				),
				'.landing-block-node-element-button' => array(
					0 => array(
						'text' => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT13"),
						'href' => '#contacts',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
					1 => array(
						'text' => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT14"),
						'href' => '#contacts',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
				),
			),
			'style' => array(
				'.landing-block-card-slider-element' => array(
					0 => 'landing-block-card-slider-element js-slide  slick-slide slick-current slick-active',
				),
				'.landing-block-node-element-title' => array(
					0 => 'landing-block-node-element-title js-animation fadeIn text-uppercase g-font-weight-700 g-color-white g-mb-40 g-font-size-28',
				),
				'.landing-block-node-element-text' => array(
					0 => 'landing-block-node-element-text js-animation fadeIn g-color-white-opacity-0_8 g-font-size-24',
				),
				'.landing-block-node-element-button' => array(
					0 => 'landing-block-node-element-button js-animation fadeInUp btn btn-lg u-btn-inset u-btn-white g-brd-0',
				),
				'.landing-block-node_bgimage' => array(
					0 => 'landing-block-node_bgimage u-bg-overlay g-bg-img-hero g-py-60 g-bg-darkblue-opacity-0_7--after',
				),
				'.landing-block-node-button-container' => array(
					0 => 'landing-block-node-button-container',
				),
				'#wrapper' => array(
					0 => 'landing-block',
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
					0 => 'landing-block-node-card-icon icon-communication-033 u-line-icon-pro landing-block-node-card-icon',
					1 => 'landing-block-node-card-icon icon-communication-002 u-line-icon-pro landing-block-node-card-icon',
					2 => 'landing-block-node-card-icon icon-communication-148 u-line-icon-pro landing-block-node-card-icon',
					3 => 'landing-block-node-card-icon icon-communication-151 u-line-icon-pro landing-block-node-card-icon',
					4 => 'landing-block-node-card-icon icon-communication-119 u-line-icon-pro landing-block-node-card-icon',
					5 => 'landing-block-node-card-icon icon-heart landing-block-node-card-icon',
				),
				'.landing-block-node-card-number' => array(
					0 => '<a href="#block29013" target="_self">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT15").'</a>',
					1 => '<a href="#block29199" target="_self">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT16").'</a>',
					2 => '<a href="#block29265" target="_self">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT17").'</a>',
					3 => '<a href="#block29457" target="_self">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT18").'</a>',
					4 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT19"),
					5 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT20"),
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
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 text-center g-mb-40 g-mb-0--lg  col-lg-4',
				),
				'.landing-block-node-card-number' => array(
					0 => 'landing-block-node-card-number g-color-white mb-0 g-font-open-sans g-font-size-24',
				),
				'.landing-block-node-card-number-title' => array(
					0 => 'landing-block-node-card-number-title text-uppercase g-font-weight-700 g-font-size-11 g-color-white g-mb-20',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-font-size-default g-color-white-opacity-0_6 mb-0',
				),
				'.landing-block-node-card-icon-container' => array(
					0 => 'landing-block-node-card-icon-container u-icon-v1 u-icon-size--lg g-color-white-opacity-0_6 g-mb-15',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-70 g-pb-70 g-bg-black',
				),
			),
			'attrs' => array(),
		),
		4 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'anchor' => 'contactcenter',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="font-weight: bold;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT21").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT22"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-color-primary g-font-open-sans g-font-size-86 g-line-height-0_9',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28 g-line-height-2',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pb-0 g-pt-100',
				),
			),
			'attrs' => array(),
		),
		5 => array(
			'code' => '32.2.img_one_big',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/3b1/3b1c35923957c2c67566d4c53a8b6fe1/contactcenter-min.png',
					),
				),
			),
			'style' => array(
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation zoomIn img-fluid w-100',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-100',
				),
			),
			'attrs' => array(),
		),
		6 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT23").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT24"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-font-open-sans g-color-primary g-font-size-50',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pb-0 g-pt-100',
				),
			),
			'attrs' => array(),
		),
		7 => array(
			'code' => '32.2.img_one_big',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/sydney-tpl/calltr-min.png',
					),
				),
			),
			'style' => array(
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation zoomIn img-fluid w-100',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-100',
				),
			),
			'attrs' => array(),
		),
		8 => array(
			'code' => '34.4.two_cols_with_text_and_icons',
			'cards' => array(
				'.landing-block-node-card' => 3,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => array(
					0 => 'landing-block-node-card-icon icon-media-106 u-line-icon-pro',
					1 => 'landing-block-node-card-icon icon-finance-104 u-line-icon-pro',
					2 => 'landing-block-node-card-icon icon-finance-076 u-line-icon-pro',
				),
				'.landing-block-node-card-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT25"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT26"),
					2 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT27"),
				),
				'.landing-block-node-card-title' => array(
					0 => ' ',
					1 => ' ',
					2 => ' ',
				),
			),
			'style' => array(
				'.landing-block-node-card' => array(
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 g-mb-40  col-lg-4',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-font-size-default mb-0 g-color-white g-font-size-18',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title h5 text-uppercase g-font-weight-800 g-color-white',
				),
				'.landing-block-node-card-icon-container' => array(
					0 => 'landing-block-node-card-icon-container d-block g-font-size-48 g-line-height-1 g-color-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pb-0 g-pt-50',
				),
			),
			'attrs' => array(),
		),
		9 => array(
			'code' => '27.7.one_col_fix_text_on_bg',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT28"),
				),
			),
			'style' => array(
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pa-30 g-bg-primary-opacity-0_1 g-color-white g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-50 g-pb-50',
				),
			),
			'attrs' => array(),
		),
		10 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT29"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT30"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-font-open-sans g-color-primary g-line-height-1_3 g-font-size-48',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pb-0 g-pt-100',
				),
			),
			'attrs' => array(),
		),
		11 => array(
			'code' => '32.2.img_one_big',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/fec/feca28bdc72ed220e66f038588e4b21d/vkk-min.png',
					),
				),
			),
			'style' => array(
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation zoomIn img-fluid w-100',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-100',
				),
			),
			'attrs' => array(),
		),
		12 => array(
			'code' => '34.4.two_cols_with_text_and_icons',
			'cards' => array(
				'.landing-block-node-card' => 3,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => array(
					0 => 'landing-block-node-card-icon icon-media-106 u-line-icon-pro',
					1 => 'landing-block-node-card-icon icon-communication-159 u-line-icon-pro',
					2 => 'landing-block-node-card-icon icon-communication-176 u-line-icon-pro',
				),
				'.landing-block-node-card-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT31"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT32"),
					2 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT33"),
				),
				'.landing-block-node-card-title' => array(
					0 => ' ',
					1 => ' ',
					2 => ' ',
				),
			),
			'style' => array(
				'.landing-block-node-card' => array(
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 g-mb-40  col-lg-4',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-font-size-default mb-0 g-color-white g-font-size-18',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title h5 text-uppercase g-font-weight-800 g-color-white',
				),
				'.landing-block-node-card-icon-container' => array(
					0 => 'landing-block-node-card-icon-container d-block g-font-size-48 g-line-height-1 g-color-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-50 g-pb-50',
				),
			),
			'attrs' => array(),
		),
		13 => array(
			'code' => '20.3.four_cols_fix_img_title_text',
			'cards' => array(
				'.landing-block-card' => 3,
			),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/eef/eef334bf5f1dafc2467c3901871d2836/yandex-min.png',
					),
					1 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/19b/19bbb3ae79f3b84d75c352a215cfd8fb/facebook-min.png',
					),
					2 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/868/868d8632e0e26c9924499385a0a95060/bitrixmail-min.png',
					),
				),
				'.landing-block-node-title' => array(
					0 => '<span style="font-weight: 400;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT34").'</span>',
					1 => '<span style="font-weight: 400;">'.'FACEBOOK LEADS'.'</span>',
					2 => '<span style="font-weight: 400;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT35").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT36"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT37"),
					2 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT38"),
				),
			),
			'style' => array(
				'.landing-block-card' => array(
					0 => 'landing-block-card js-animation fadeInUp landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10  col-lg-4',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-700 g-mb-20 g-color-primary text-uppercase g-font-size-28',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-color-white g-font-size-18',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pb-20 g-pt-50',
				),
			),
			'attrs' => array(),
		),
		14 => array(
			'code' => '13.2.one_col_fix_button',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-button' => array(
					0 => array(
						'text' => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT39"),
						'href' => 'https://www.bitrix24.ru/register/reg.php',
						'target' => '_blank',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
				),
			),
			'style' => array(
				'.landing-block-node-button' => array(
					0 => 'landing-block-node-button btn btn-md text-uppercase u-btn-primary rounded-0 g-px-15 g-font-weight-700 g-brd-7',
				),
				'#wrapper' => array(
					0 => 'landing-block text-center g-pt-20 g-pb-20',
				),
			),
			'attrs' => array(),
		),
		15 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'anchor' => 'crm',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="font-weight: bold;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT40").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => ' ',
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-color-primary g-font-open-sans g-font-size-90',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pb-0 g-pt-60',
				),
			),
			'attrs' => array(),
		),
		16 => array(
			'code' => '32.2.img_one_big',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/cac/cac561d3462d44c5449991d7e35775fd/smartmockups_jm9mypib-min.png',
					),
				),
			),
			'style' => array(
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation zoomIn img-fluid w-100',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-100',
				),
			),
			'attrs' => array(),
		),
		17 => array(
			'code' => '34.4.two_cols_with_text_and_icons',
			'cards' => array(
				'.landing-block-node-card' => 6,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => array(
					0 => 'landing-block-node-card-icon icon-media-112 u-line-icon-pro',
					1 => 'landing-block-node-card-icon icon-media-119 u-line-icon-pro',
					2 => 'landing-block-node-card-icon icon-media-084 u-line-icon-pro',
					3 => 'landing-block-node-card-icon icon-media-117 u-line-icon-pro',
					4 => 'landing-block-node-card-icon icon-media-104 u-line-icon-pro',
					5 => 'landing-block-node-card-icon icon-media-110 u-line-icon-pro',
				),
				'.landing-block-node-card-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT41"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT42"),
					2 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT43"),
					3 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT44"),
					4 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT45"),
					5 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT46"),
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
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 g-mb-40  col-lg-4',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-font-size-default mb-0 g-color-white g-font-size-18',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title h5 text-uppercase g-font-weight-800 g-color-white',
				),
				'.landing-block-node-card-icon-container' => array(
					0 => 'landing-block-node-card-icon-container d-block g-font-size-48 g-line-height-1 g-color-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-55 g-pb-0',
				),
			),
			'attrs' => array(),
		),
		18 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT47").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT48"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-font-open-sans g-font-size-55 g-color-primary',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-90 g-pb-0',
				),
			),
			'attrs' => array(),
		),
		19 => array(
			'code' => '32.2.img_one_big',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/06c/06ce259da5e598e8c3d0046c09b2a5ad/8884-min.png',
					),
				),
			),
			'style' => array(
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation zoomIn img-fluid w-100',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-100',
				),
			),
			'attrs' => array(),
		),
		20 => array(
			'code' => '34.4.two_cols_with_text_and_icons',
			'cards' => array(
				'.landing-block-node-card' => 3,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => array(
					0 => 'landing-block-node-card-icon icon-media-091 u-line-icon-pro',
					1 => 'landing-block-node-card-icon icon-media-103 u-line-icon-pro',
					2 => 'landing-block-node-card-icon icon-media-104 u-line-icon-pro',
				),
				'.landing-block-node-card-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT49"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT50"),
					2 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT50a"),
				),
				'.landing-block-node-card-title' => array(
					0 => ' ',
					1 => ' ',
					2 => ' ',
				),
			),
			'style' => array(
				'.landing-block-node-card' => array(
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 g-mb-40  col-lg-4',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-font-size-default mb-0 g-color-white g-font-size-18',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title h5 text-uppercase g-font-weight-800 g-color-white',
				),
				'.landing-block-node-card-icon-container' => array(
					0 => 'landing-block-node-card-icon-container d-block g-font-size-48 g-line-height-1 g-color-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-55 g-pb-0',
				),
			),
			'attrs' => array(),
		),
		21 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT51"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT52"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-font-open-sans g-font-size-55 g-color-primary',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-90 g-pb-50',
				),
			),
			'attrs' => array(),
		),
		22 => array(
			'code' => '49.1.video_just_video',
			'nodes' => array(
				'.landing-block-node-embed' => array(
					0 => array(
						'src' => 'https://www.youtube.com/embed/7pRzLdIjWkE?autoplay=1&controls=1&loop=0&mute=0&rel=0&start=0&html5=1&enablejsapi=1&playerVars=[object%20Object]',
						'source' => 'https://www.youtube.com/embed/7pRzLdIjWkE',
					),
				),
			),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block g-pt-0 g-pb-0',
				),
			),
		),
		23 => array(
			'code' => '34.4.two_cols_with_text_and_icons',
			'cards' => array(
				'.landing-block-node-card' => 3,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => array(
					0 => 'landing-block-node-card-icon icon-communication-073 u-line-icon-pro',
					1 => 'landing-block-node-card-icon icon-travel-129 u-line-icon-pro',
					2 => 'landing-block-node-card-icon icon-media-066 u-line-icon-pro',
				),
				'.landing-block-node-card-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT53"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT54"),
					2 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT55"),
				),
				'.landing-block-node-card-title' => array(
					0 => ' ',
					1 => ' ',
					2 => ' ',
				),
			),
			'style' => array(
				'.landing-block-node-card' => array(
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 g-mb-40  col-lg-4',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-font-size-default mb-0 g-color-white g-font-size-18',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title h5 text-uppercase g-font-weight-800 g-color-white',
				),
				'.landing-block-node-card-icon-container' => array(
					0 => 'landing-block-node-card-icon-container d-block g-font-size-48 g-line-height-1 g-color-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-55 g-pb-0',
				),
			),
			'attrs' => array(),
		),
		24 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT56"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT57"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-font-open-sans g-font-size-55 g-color-primary g-line-height-1_2',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-90 g-pb-50',
				),
			),
			'attrs' => array(),
		),
		25 => array(
			'code' => '49.1.video_just_video',
			'nodes' => array(
				'.landing-block-node-embed' => array(
					0 => array(
						'src' => 'https://www.youtube.com/embed/5UtuHAjdIrM?autoplay=0&controls=1&loop=0&mute=0&rel=0&start=0&html5=1&enablejsapi=1&playerVars=[object%20Object]',
						'source' => 'https://www.youtube.com/embed/5UtuHAjdIrM',
					),
				),
			),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block g-pt-0 g-pb-0',
				),
			),
		),
		26 => array(
			'code' => '34.4.two_cols_with_text_and_icons',
			'cards' => array(
				'.landing-block-node-card' => 3,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => array(
					0 => 'landing-block-node-card-icon icon-christmas-053 u-line-icon-pro',
					1 => 'landing-block-node-card-icon icon-finance-221 u-line-icon-pro',
					2 => 'landing-block-node-card-icon icon-finance-205 u-line-icon-pro',
				),
				'.landing-block-node-card-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT58"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT59"),
					2 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT60"),
				),
				'.landing-block-node-card-title' => array(
					0 => ' ',
					1 => ' ',
					2 => ' ',
				),
			),
			'style' => array(
				'.landing-block-node-card' => array(
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 g-mb-40  col-lg-4',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-font-size-default mb-0 g-color-white g-font-size-18',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title h5 text-uppercase g-font-weight-800 g-color-white',
				),
				'.landing-block-node-card-icon-container' => array(
					0 => 'landing-block-node-card-icon-container d-block g-font-size-48 g-line-height-1 g-color-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-55 g-pb-0',
				),
			),
			'attrs' => array(),
		),
		27 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT61"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT62"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-font-open-sans g-font-size-55 g-color-primary',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-90 g-pb-80',
				),
			),
			'attrs' => array(),
		),
		28 => array(
			'code' => '20.1.two_cols_fix_img_title_text',
			'cards' => array(
				'.landing-block-card' => 2,
			),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/c6b/c6bd4adbf30dc6c1439b5b87ff3de480/Screen_Shot_2018-09-20_at_10.17.38-min.png',
					),
					1 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/b82/b828d18975eec58e95832791f70779b6/Screen_Shot_2018-09-20_at_18.00.16-min.png',
					),
				),
				'.landing-block-node-title' => array(
					0 => '<span style="color: rgb(79, 195, 247);font-weight: 400;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT63").'</span>',
					1 => '<span style="color: rgb(79, 195, 247);font-weight: 400;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT64").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT65"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT66"),
				),
			),
			'style' => array(
				'.landing-block-card' => array(
					0 => 'landing-block-card js-animation fadeIn landing-block-node-block col-md-6 g-mb-30 g-mb-0--md g-pt-10 ',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-700 g-color-black g-mb-20 g-font-size-28 text-uppercase',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-color-white',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-10 g-pb-50',
				),
			),
			'attrs' => array(),
		),
		29 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT67"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT68"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-font-open-sans g-font-size-55 g-color-primary',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-90 g-pb-0',
				),
			),
			'attrs' => array(),
		),
		30 => array(
			'code' => '34.4.two_cols_with_text_and_icons',
			'cards' => array(
				'.landing-block-node-card' => 6,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => array(
					0 => 'landing-block-node-card-icon icon-real-estate-071 u-line-icon-pro',
					1 => 'landing-block-node-card-icon icon-finance-256 u-line-icon-pro',
					2 => 'landing-block-node-card-icon icon-cloud-upload',
					3 => 'landing-block-node-card-icon icon-education-105 u-line-icon-pro',
					4 => 'landing-block-node-card-icon icon-education-176 u-line-icon-pro',
					5 => 'landing-block-node-card-icon icon-education-153 u-line-icon-pro',
				),
				'.landing-block-node-card-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT69"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT70"),
					2 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT71"),
					3 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT72"),
					4 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT73"),
					5 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT74"),
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
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 g-mb-40  col-lg-4',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-font-size-default mb-0 g-color-white g-font-size-18',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title h5 text-uppercase g-font-weight-800 g-color-white',
				),
				'.landing-block-node-card-icon-container' => array(
					0 => 'landing-block-node-card-icon-container d-block g-font-size-48 g-line-height-1 g-color-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pb-0 g-pt-100',
				),
			),
			'attrs' => array(),
		),
		31 => array(
			'code' => '13.2.one_col_fix_button',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-button' => array(
					0 => array(
						'text' => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT75"),
						'href' => 'https://www.bitrix24.ru/register/reg.php',
						'target' => '_blank',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
				),
			),
			'style' => array(
				'.landing-block-node-button' => array(
					0 => 'landing-block-node-button btn btn-md text-uppercase u-btn-primary rounded-0 g-px-15 g-font-weight-700 g-brd-7',
				),
				'#wrapper' => array(
					0 => 'landing-block text-center g-pt-20 g-pb-20',
				),
			),
			'attrs' => array(),
		),
		32 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'anchor' => 'marketing',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="font-weight: bold;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT76").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT77"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-color-primary g-font-open-sans g-font-size-90',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pb-0 g-pt-100',
				),
			),
			'attrs' => array(),
		),
		33 => array(
			'code' => '32.2.img_one_big',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/922/92267b4405cba5e590bea83c441da368/marketing.png',
					),
				),
			),
			'style' => array(
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation zoomIn img-fluid w-100',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-100',
				),
			),
			'attrs' => array(),
		),
		34 => array(
			'code' => '34.4.two_cols_with_text_and_icons',
			'cards' => array(
				'.landing-block-node-card' => 3,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => array(
					0 => 'landing-block-node-card-icon icon-media-106 u-line-icon-pro',
					1 => 'landing-block-node-card-icon icon-finance-104 u-line-icon-pro',
					2 => 'landing-block-node-card-icon icon-finance-076 u-line-icon-pro',
				),
				'.landing-block-node-card-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT78"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT79"),
					2 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT80"),
				),
				'.landing-block-node-card-title' => array(
					0 => ' ',
					1 => ' ',
					2 => ' ',
				),
			),
			'style' => array(
				'.landing-block-node-card' => array(
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 g-mb-40  col-lg-4',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-font-size-default mb-0 g-color-white g-font-size-18',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title h5 text-uppercase g-font-weight-800 g-color-white',
				),
				'.landing-block-node-card-icon-container' => array(
					0 => 'landing-block-node-card-icon-container d-block g-font-size-48 g-line-height-1 g-color-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-55 g-pb-0',
				),
			),
			'attrs' => array(),
		),
		35 => array(
			'code' => '27.4.one_col_fix_text',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT81"),
				),
			),
			'style' => array(
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-50 g-pb-50',
				),
			),
			'attrs' => array(),
		),
		36 => array(
			'code' => '13.2.one_col_fix_button',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-button' => array(
					0 => array(
						'text' => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT82"),
						'href' => 'https://www.bitrix24.ru/register/reg.php',
						'target' => '_blank',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
				),
			),
			'style' => array(
				'.landing-block-node-button' => array(
					0 => 'landing-block-node-button btn btn-md text-uppercase u-btn-primary rounded-0 g-px-15 g-font-weight-700 g-brd-7',
				),
				'#wrapper' => array(
					0 => 'landing-block text-center g-pt-20 g-pb-20',
				),
			),
			'attrs' => array(),
		),
		37 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'anchor' => 'analytics',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="font-weight: 700;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT83").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT84"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-color-primary g-font-open-sans g-font-size-90',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pb-0 g-pt-100',
				),
			),
			'attrs' => array(),
		),
		38 => array(
			'code' => '32.2.img_one_big',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/8cd/8cd36b3e68381935967b78635effc956/analyt.png',
					),
				),
			),
			'style' => array(
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation zoomIn img-fluid w-100',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-100',
				),
			),
			'attrs' => array(),
		),
		39 => array(
			'code' => '34.4.two_cols_with_text_and_icons',
			'cards' => array(
				'.landing-block-node-card' => 3,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => array(
					0 => 'landing-block-node-card-icon icon-finance-222 u-line-icon-pro',
					1 => 'landing-block-node-card-icon icon-finance-151 u-line-icon-pro',
					2 => 'landing-block-node-card-icon icon-finance-256 u-line-icon-pro',
				),
				'.landing-block-node-card-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT85"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT86"),
					2 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT87"),
				),
				'.landing-block-node-card-title' => array(
					0 => ' ',
					1 => ' ',
					2 => ' ',
				),
			),
			'style' => array(
				'.landing-block-node-card' => array(
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 g-mb-40  col-lg-4',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-font-size-default mb-0 g-color-white g-font-size-18',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title h5 text-uppercase g-font-weight-800 g-color-white',
				),
				'.landing-block-node-card-icon-container' => array(
					0 => 'landing-block-node-card-icon-container d-block g-font-size-48 g-line-height-1 g-color-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-55 g-pb-0',
				),
			),
			'attrs' => array(),
		),
		40 => array(
			'code' => '13.2.one_col_fix_button',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-button' => array(
					0 => array(
						'text' => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT88"),
						'href' => 'https://www.bitrix24.ru/register/reg.php',
						'target' => '_blank',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
				),
			),
			'style' => array(
				'.landing-block-node-button' => array(
					0 => 'landing-block-node-button btn btn-md text-uppercase u-btn-primary rounded-0 g-px-15 g-font-weight-700 g-brd-7',
				),
				'#wrapper' => array(
					0 => 'landing-block text-center g-pt-20 g-pb-20',
				),
			),
			'attrs' => array(),
		),
		41 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'anchor' => 'channels',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="font-weight: 700;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT89").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT90"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-color-primary g-font-open-sans g-line-height-0_9 g-font-size-86',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28 g-line-height-0',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pb-0 g-pt-100',
				),
			),
			'attrs' => array(),
		),
		42 => array(
			'code' => '32.2.img_one_big',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/408/408ec152b805fc986b82f7c3851f15d1/skvoz-min.png',
					),
				),
			),
			'style' => array(
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation zoomIn img-fluid w-100',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-100',
				),
			),
			'attrs' => array(),
		),
		43 => array(
			'code' => '34.4.two_cols_with_text_and_icons',
			'cards' => array(
				'.landing-block-node-card' => 6,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => array(
					0 => 'landing-block-node-card-icon icon-communication-003 u-line-icon-pro',
					1 => 'landing-block-node-card-icon icon-communication-019 u-line-icon-pro',
					2 => 'landing-block-node-card-icon icon-communication-043 u-line-icon-pro',
					3 => 'landing-block-node-card-icon icon-finance-076 u-line-icon-pro',
					4 => 'landing-block-node-card-icon icon-communication-038 u-line-icon-pro',
					5 => 'landing-block-node-card-icon icon-communication-039 u-line-icon-pro',
				),
				'.landing-block-node-card-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT91"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT92"),
					2 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT93"),
					3 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT94"),
					4 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT95"),
					5 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT96"),
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
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 g-mb-40  col-lg-4',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-font-size-default mb-0 g-color-white g-font-size-18',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title h5 text-uppercase g-font-weight-800 g-color-white',
				),
				'.landing-block-node-card-icon-container' => array(
					0 => 'landing-block-node-card-icon-container d-block g-font-size-48 g-line-height-1 g-color-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-55 g-pb-50',
				),
			),
			'attrs' => array(),
		),
		44 => array(
			'code' => '20.1.two_cols_fix_img_title_text',
			'cards' => array(
				'.landing-block-card' => 2,
			),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/sydney-tpl/adv1.png',
					),
					1 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/sydney-tpl/adv2.png',
					),
				),
				'.landing-block-node-title' => array(
					0 => '<span style="color: rgb(79, 195, 247);font-weight: 400;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT97").'</span>',
					1 => '<span style="color: rgb(79, 195, 247);font-weight: 400;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT98").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT99"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT100"),
				),
			),
			'style' => array(
				'.landing-block-card' => array(
					0 => 'landing-block-card js-animation fadeIn landing-block-node-block col-md-6 g-mb-30 g-mb-0--md g-pt-10 ',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-700 g-color-black g-mb-20 g-font-size-28 text-uppercase',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-color-white g-font-size-17',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-50 g-pb-30',
				),
			),
			'attrs' => array(),
		),
		45 => array(
			'code' => '27.7.one_col_fix_text_on_bg',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT101"),
				),
			),
			'style' => array(
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pa-30 g-bg-primary-opacity-0_1 g-color-white g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-50 g-pb-50',
				),
			),
			'attrs' => array(),
		),
		46 => array(
			'code' => '13.2.one_col_fix_button',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-button' => array(
					0 => array(
						'text' => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT102"),
						'href' => 'https://www.bitrix24.ru/register/reg.php',
						'target' => '_blank',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
				),
			),
			'style' => array(
				'.landing-block-node-button' => array(
					0 => 'landing-block-node-button btn btn-md text-uppercase u-btn-primary rounded-0 g-px-15 g-font-weight-700 g-brd-7',
				),
				'#wrapper' => array(
					0 => 'landing-block text-center g-pt-20 g-pb-20',
				),
			),
			'attrs' => array(),
		),
		47 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'anchor' => 'tasks',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="font-weight: 700;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT103").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT104"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-color-primary g-font-open-sans g-line-height-0_9 g-font-size-86',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28 g-line-height-0',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pb-0 g-pt-100',
				),
			),
			'attrs' => array(),
		),
		48 => array(
			'code' => '32.2.img_one_big',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/a34/a34bb2c5222a04941d77d34b5f027926/tasks-min.png',
					),
				),
			),
			'style' => array(
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation zoomIn img-fluid w-100',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-100',
				),
			),
			'attrs' => array(),
		),
		49 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT105"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT106"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-font-open-sans g-font-size-55 g-color-primary',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-90 g-pb-50',
				),
			),
			'attrs' => array(),
		),
		50 => array(
			'code' => '20.1.two_cols_fix_img_title_text',
			'cards' => array(
				'.landing-block-card' => 2,
			),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/320/320b9ed979c20892865f2d8c45d35e22/trig-min.png',
					),
					1 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/c94/c9423f39105de7c9b261431c4beeb64d/robot-min.png',
					),
				),
				'.landing-block-node-title' => array(
					0 => '<span style="color: rgb(79, 195, 247); font-weight: 400;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT107").'</span>',
					1 => '<span style="color: rgb(79, 195, 247); font-weight: 400;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT108").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT109"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT110"),
				),
			),
			'style' => array(
				'.landing-block-card' => array(
					0 => 'landing-block-card js-animation fadeIn landing-block-node-block col-md-6 g-mb-30 g-mb-0--md g-pt-10 ',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title text-uppercase g-font-weight-700 g-color-black g-mb-20 g-font-size-28',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-color-white',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pb-50 g-pt-50',
				),
			),
			'attrs' => array(),
		),
		51 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT111"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT112"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-font-open-sans g-font-size-55 g-color-primary',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-90 g-pb-50',
				),
			),
			'attrs' => array(),
		),
		52 => array(
			'code' => '31.4.two_cols_img_text_fix',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="color: rgb(79, 195, 247);font-weight: normal;"><br /></span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT112a"),
				),
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => 'Image description',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/fcd/fcd6250a6c4860c810655e22911cbaf4/trashe-min.png',
					),
				),
			),
			'style' => array(
				'.landing-block-node-text-container' => array(
					0 => 'landing-block-node-text-container js-animation slideInRight col-md-6 order-1 order-md-2',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title text-uppercase g-font-weight-700 mb-0 g-mb-15 g-font-size-28',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-font-size-18 g-color-white',
				),
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation slideInLeft img-fluid',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-50 g-pb-100',
				),
			),
			'attrs' => array(),
		),
		53 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT113"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT114"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-font-open-sans g-font-size-55 g-color-primary',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-90 g-pb-50',
				),
			),
			'attrs' => array(),
		),
		54 => array(
			'code' => '45.2.gallery_app_with_slider',
			'cards' => array(
				'.landing-block-node-card' => 5,
			),
			'nodes' => array(
				'.landing-block-node-card-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/7d5/7d54a7ed19de76682a8cf9f47fcc336e/mtask1-min.png',
					),
					1 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/d5e/d5e91b6af59bf7d28b42675786672106/mtask2-min.png',
					),
					2 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/29c/29ca1ea9b72311ab599087ff7cb52506/mtask3-min.png',
					),
					3 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/32e/32ed04e037f4271b83e90672288c5d2b/mtask4-min.png',
					),
					4 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/b12/b123d024bfa931877c79631bec418d01/mtask5-min.png',
					),
				),
				'.landing-block-node-card-title' => array(
					0 => ' ',
					1 => ' ',
					2 => ' ',
					3 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT115"),
					4 => ' ',
				),
				'.landing-block-node-card-subtitle' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT116"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT117"),
					2 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT118"),
					3 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT119"),
					4 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT120"),
				),
			),
			'style' => array(
				'.landing-block-node-card-title-container' => array(
					0 => 'landing-block-node-card-title-container g-pos-abs g-bottom-0 g-left-0 g-flex-middle w-100 g-bg-primary-opacity-0_9 opacity-0 g-opacity-1--parent-hover g-pa-20 g-transition-0_2 g-transition--ease-in',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title h3 g-color-white',
				),
				'.landing-block-node-card-subtitle' => array(
					0 => 'landing-block-node-card-subtitle g-color-white',
				),
				'.landing-block-node-card' => array(
					0 => 'landing-block-node-card js-animation slideInUp text-center g-mb-30 g-min-width-300  slick-slide slick-current slick-active',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pb-0 g-pt-50',
				),
			),
			'attrs' => array(),
		),
		55 => array(
			'code' => '34.4.two_cols_with_text_and_icons',
			'cards' => array(
				'.landing-block-node-card' => 6,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => array(
					0 => 'landing-block-node-card-icon icon-communication-129 u-line-icon-pro',
					1 => 'landing-block-node-card-icon icon-communication-096 u-line-icon-pro',
					2 => 'landing-block-node-card-icon icon-finance-076 u-line-icon-pro',
					3 => 'landing-block-node-card-icon icon-education-142 u-line-icon-pro',
					4 => 'landing-block-node-card-icon icon-communication-141 u-line-icon-pro',
					5 => 'landing-block-node-card-icon icon-communication-140 u-line-icon-pro',
				),
				'.landing-block-node-card-text' => [
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT121"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT122"),
					2 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT123"),
					3 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT124"),
					4 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT125"),
					5 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT126"),
				],
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
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 g-mb-40  col-lg-4',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-font-size-default mb-0 g-color-white g-font-size-18',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title h5 text-uppercase g-font-weight-800 g-color-white',
				),
				'.landing-block-node-card-icon-container' => array(
					0 => 'landing-block-node-card-icon-container d-block g-font-size-48 g-line-height-1 g-color-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-55 g-pb-50',
				),
			),
			'attrs' => array(),
		),
		56 => array(
			'code' => '13.2.one_col_fix_button',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-button' => array(
					0 => array(
						'text' => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT127"),
						'href' => 'https://www.bitrix24.ru/register/reg.php',
						'target' => '_blank',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
				),
			),
			'style' => array(
				'.landing-block-node-button' => array(
					0 => 'landing-block-node-button btn btn-md text-uppercase u-btn-primary rounded-0 g-px-15 g-font-weight-700 g-brd-7',
				),
				'#wrapper' => array(
					0 => 'landing-block text-center g-pt-20 g-pb-20',
				),
			),
			'attrs' => array(),
		),
		57 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="font-weight: 700;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT129").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT130"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-color-primary g-font-open-sans g-line-height-0_9 g-font-size-86',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28 g-line-height-0',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pb-0 g-pt-100',
				),
			),
			'attrs' => array(),
		),
		58 => array(
			'code' => '32.2.img_one_big',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/daa/daab1d7ede6135fc05e99f39f7a336a2/company-min.png',
					),
				),
			),
			'style' => array(
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation zoomIn img-fluid w-100',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pb-50 g-pt-100',
				),
			),
			'attrs' => array(),
		),
		59 => array(
			'code' => '31.3.two_cols_text_img_fix',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<br /><span style="color: rgb(79, 195, 247);font-weight: 400;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT131").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT132"),
				),
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => 'Image description',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/97b/97b4b5cec1cb0ade3fb232b44c6edce8/obzor-min.png',
					),
				),
			),
			'style' => array(
				'.landing-block-node-text-container' => array(
					0 => 'landing-block-node-text-container js-animation slideInLeft col-md-6 g-pb-20 g-pb-0--md',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-700 mb-0 g-mb-15 g-color-white g-font-size-28 text-uppercase',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-font-size-18 g-color-white',
				),
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation slideInRight img-fluid',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pb-50 g-pt-100',
				),
			),
			'attrs' => array(),
		),
		60 => array(
			'code' => '31.4.two_cols_img_text_fix',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="color: rgb(79, 195, 247);font-weight: normal;"><br />'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT133").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT134"),
				),
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => 'Image description',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/6cd/6cd8449c25ca8ec9217f1f900313ddc7/videocall-min.png',
					),
				),
			),
			'style' => array(
				'.landing-block-node-text-container' => array(
					0 => 'landing-block-node-text-container js-animation slideInRight col-md-6 order-1 order-md-2',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-700 mb-0 g-mb-15 g-font-size-28 text-uppercase',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-font-size-18 g-color-white',
				),
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation slideInLeft img-fluid',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-50 g-pb-50',
				),
			),
			'attrs' => array(),
		),
		61 => array(
			'code' => '13.2.one_col_fix_button',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-button' => array(
					0 => array(
						'text' => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT135"),
						'href' => 'https://www.bitrix24.ru/register/reg.php',
						'target' => '_blank',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
				),
			),
			'style' => array(
				'.landing-block-node-button' => array(
					0 => 'landing-block-node-button btn btn-md text-uppercase u-btn-primary rounded-0 g-px-15 g-font-weight-700 g-brd-7',
				),
				'#wrapper' => array(
					0 => 'landing-block text-center g-pt-20 g-pb-20',
				),
			),
			'attrs' => array(),
		),
		62 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="font-weight: 700;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT135").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT136"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-color-primary g-font-open-sans g-line-height-0_9 g-font-size-86',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28 g-line-height-0',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pb-0 g-pt-100',
				),
			),
			'attrs' => array(),
		),
		63 => array(
			'code' => '40.4.slider_blocks_with_img_and_text',
			'cards' => array(
				'.landing-block-node-card' => 2,
			),
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => ' ',
				),
				'.landing-block-node-title' => array(
					0 => ' ',
				),
				'.landing-block-node-text' => array(
					0 => ' ',
				),
				'.landing-block-node-card-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/617/617b3ef7e8e146bf4488babca53665f7/profile-min.png',
					),
					1 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/0cd/0cd2dc7f2872e886b2ddbfb5c3a92336/enter1-min.png',
					),
				),
				'.landing-block-node-card-img2' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/00e/00e91873768d08b28a633fad63ee5bd0/profile2-min.png',
					),
					1 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/42e/42e432c75c1af3db68ec2b38cb4d6abe/enter2-min.png',
					),
				),
				'.landing-block-node-card-title' => array(
					0 => '<span style="font-weight: normal;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT137").'</span>',
					1 => '<span style="font-weight: normal;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT138").'</span>',
				),
				'.landing-block-node-card-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT139"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT140"),
				),
				'.landing-block-node-card-button' => array(
					0 => array(
						'text' => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT141"),
						'href' => 'https://www.youtube.com/watch?v=13ILnv1HPVw',
						'target' => '_popup',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => '//www.youtube.com/embed/13ILnv1HPVw?autoplay=0&controls=1&loop=0&rel=0&start=0&html5=1&v=q4d8g9Dn3ww',
						),
					),
					1 => array(
						'text' => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT142"),
						'href' => 'https://www.youtube.com/watch?v=13ILnv1HPVw',
						'target' => '_popup',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => '//www.youtube.com/embed/13ILnv1HPVw?autoplay=0&controls=1&loop=0&rel=0&start=0&html5=1&v=q4d8g9Dn3ww',
						),
					),
				),
			),
			'style' => array(
				'.landing-block-node-subtitle' => array(
					0 => 'landing-block-node-subtitle g-font-weight-700 g-font-size-11 g-color-white g-mb-15',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title js-animation fadeInLeft g-line-height-1_3 g-font-size-36 g-color-white mb-0',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text js-animation fadeInLeft g-color-white mb-0',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title h6 text-uppercase g-font-weight-700 g-mb-15 g-font-size-28 g-color-primary',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text js-animation fadeInLeft g-font-size-default g-mb-30 g-color-white g-font-size-17',
				),
				'.landing-block-node-card-button' => array(
					0 => 'landing-block-node-card-button js-animation fadeInLeft btn btn-lg text-uppercase u-btn-white g-font-weight-700 g-font-size-12 g-rounded-10 g-px-25 g-py-12 mb-0',
				),
				'.landing-block-node-card-button-container' => array(
					0 => 'landing-block-node-card-button-container',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pb-90 g-pt-0 g-bg-black',
				),
			),
			'attrs' => array(),
		),
		64 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'anchor' => 'sites',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="font-weight: 700;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT143").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT144"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-color-primary g-font-open-sans g-line-height-0_9 g-font-size-86',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28 g-line-height-0',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pb-0 g-pt-100',
				),
			),
			'attrs' => array(),
		),
		65 => array(
			'code' => '32.2.img_one_big',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/6e7/6e76b51e3b965094aeb7ce3b32fafdfd/site-min.png',
					),
				),
			),
			'style' => array(
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation zoomIn img-fluid w-100',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pb-50 g-pt-100',
				),
			),
			'attrs' => array(),
		),
		66 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT145"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT146"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-font-open-sans g-font-size-55 g-color-primary',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-90 g-pb-0',
				),
			),
			'attrs' => array(),
		),
		67 => array(
			'code' => '32.2.img_one_big',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/bbf/bbfafcdd5075ef9d25371584e66a31dd/redactor-min.png',
					),
				),
			),
			'style' => array(
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation zoomIn img-fluid w-100',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pb-50 g-pt-100',
				),
			),
			'attrs' => array(),
		),
		68 => array(
			'code' => '34.4.two_cols_with_text_and_icons',
			'cards' => array(
				'.landing-block-node-card' => 6,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => array(
					0 => 'landing-block-node-card-icon icon-media-106 u-line-icon-pro',
					1 => 'landing-block-node-card-icon icon-education-099 u-line-icon-pro',
					2 => 'landing-block-node-card-icon icon-travel-060 u-line-icon-pro',
					3 => 'landing-block-node-card-icon icon-travel-137 u-line-icon-pro',
					4 => 'landing-block-node-card-icon icon-science-093 u-line-icon-pro',
					5 => 'landing-block-node-card-icon icon-finance-076 u-line-icon-pro',
				),
				'.landing-block-node-card-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT147"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT148"),
					2 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT149"),
					3 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT150"),
					4 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT151"),
					5 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT152"),
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
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 g-mb-40  col-lg-4',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-font-size-default mb-0 g-color-white g-font-size-18',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title h5 text-uppercase g-font-weight-800 g-color-white',
				),
				'.landing-block-node-card-icon-container' => array(
					0 => 'landing-block-node-card-icon-container d-block g-font-size-48 g-line-height-1 g-color-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pb-50 g-pt-0 g-pl-0',
				),
			),
			'attrs' => array(),
		),
		69 => array(
			'code' => '20.3.four_cols_fix_img_title_text',
			'cards' => array(
				'.landing-block-card' => 3,
			),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/c1b/c1bcf84acbb855bec23a39028e61cebf/timer1-min.png',
					),
					1 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/cd4/cd49961726672f84addf2a4d1dd8230c/video2-min.png',
					),
					2 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/522/5220a5c8fddfdb09ee0c8e58cf914f30/map-min.png',
					),
				),
				'.landing-block-node-title' => array(
					0 => '<span style="font-weight: normal;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT153").'</span>',
					1 => '<span style="font-weight: normal;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT154").'</span>',
					2 => '<span style="font-weight: normal;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT155").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT156"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT157"),
					2 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT158"),
				),
			),
			'style' => array(
				'.landing-block-card' => array(
					0 => 'landing-block-card js-animation fadeInUp landing-block-node-block col-md-3 g-mb-30 g-mb-0--md g-pt-10  col-lg-4',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title text-uppercase g-font-weight-700 g-mb-20 g-color-primary g-font-size-28',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-color-white g-font-size-18',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pb-20 g-pt-50',
				),
			),
			'attrs' => array(),
		),
		70 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT159"),
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT160"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-font-open-sans g-font-size-55 g-color-primary g-line-height-1_1',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-90 g-pb-0',
				),
			),
			'attrs' => array(),
		),
		71 => array(
			'code' => '32.2.img_one_big',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/918/9180bfdc7b369f97adbb6d90d45739e2/insta-min.png',
					),
				),
			),
			'style' => array(
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation zoomIn img-fluid w-100',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pb-50 g-pt-100',
				),
			),
			'attrs' => array(),
		),
		72 => array(
			'code' => '27.4.one_col_fix_text',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT161"),
				),
			),
			'style' => array(
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-50 g-pb-50',
				),
			),
			'attrs' => array(),
		),
		73 => array(
			'code' => '34.4.two_cols_with_text_and_icons',
			'cards' => array(
				'.landing-block-node-card' => 3,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => array(
					0 => 'landing-block-node-card-icon icon-media-097 u-line-icon-pro',
					1 => 'landing-block-node-card-icon icon-media-084 u-line-icon-pro',
					2 => 'landing-block-node-card-icon icon-finance-076 u-line-icon-pro',
				),
				'.landing-block-node-card-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT162"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT163"),
					2 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT164"),
				),
				'.landing-block-node-card-title' => array(
					0 => ' ',
					1 => ' ',
					2 => ' ',
				),
			),
			'style' => array(
				'.landing-block-node-card' => array(
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 g-mb-40  col-lg-4',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-font-size-default mb-0 g-color-white g-font-size-18',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title h5 text-uppercase g-font-weight-800 g-color-white',
				),
				'.landing-block-node-card-icon-container' => array(
					0 => 'landing-block-node-card-icon-container d-block g-font-size-48 g-line-height-1 g-color-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pb-50 g-pt-0',
				),
			),
			'attrs' => array(),
		),
		74 => array(
			'code' => '27.7.one_col_fix_text_on_bg',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT165"),
				),
			),
			'style' => array(
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pa-30 g-bg-primary-opacity-0_1 g-color-white g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-50 g-pb-50',
				),
			),
			'attrs' => array(),
		),
		75 => array(
			'code' => '13.2.one_col_fix_button',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-button' => array(
					0 => array(
						'text' => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT166"),
						'href' => 'https://www.bitrix24.ru/register/reg.php',
						'target' => '_blank',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
					),
				),
			),
			'style' => array(
				'.landing-block-node-button' => array(
					0 => 'landing-block-node-button btn btn-md text-uppercase u-btn-primary rounded-0 g-px-15 g-font-weight-700 g-brd-7',
				),
				'#wrapper' => array(
					0 => 'landing-block text-center g-pt-20 g-pb-20',
				),
			),
			'attrs' => array(),
		),
		76 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'anchor' => 'lab',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="font-weight: 700;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT167").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT168"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-color-primary g-font-open-sans g-line-height-0_9 g-font-size-86',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28 g-line-height-0',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pb-0 g-pt-100',
				),
			),
			'attrs' => array(),
		),
		77 => array(
			'code' => '32.2.img_one_big',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/a61/a61cbe620a78db86b76acf6262e1aa93/kolonka11-min.png',
					),
				),
			),
			'style' => array(
				'.landing-block-node-img' => array(
					0 => 'landing-block-node-img js-animation zoomIn img-fluid w-100',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pb-50 g-pt-100',
				),
			),
			'attrs' => array(),
		),
		78 => array(
			'code' => '27.4.one_col_fix_text',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT169"),
				),
			),
			'style' => array(
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-size-28',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-50 g-pb-50',
				),
			),
			'attrs' => array(),
		),
		79 => array(
			'code' => '34.4.two_cols_with_text_and_icons',
			'cards' => array(
				'.landing-block-node-card' => 3,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => array(
					0 => 'landing-block-node-card-icon icon-education-143 u-line-icon-pro',
					1 => 'landing-block-node-card-icon icon-christmas-053 u-line-icon-pro',
					2 => 'landing-block-node-card-icon icon-communication-073 u-line-icon-pro',
				),
				'.landing-block-node-card-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT170"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT171"),
					2 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT172"),
				),
				'.landing-block-node-card-title' => array(
					0 => ' ',
					1 => ' ',
					2 => ' ',
				),
			),
			'style' => array(
				'.landing-block-node-card' => array(
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 g-mb-40  col-lg-4',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-font-size-default mb-0 g-color-white g-font-size-18',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title h5 text-uppercase g-font-weight-800 g-color-white',
				),
				'.landing-block-node-card-icon-container' => array(
					0 => 'landing-block-node-card-icon-container d-block g-font-size-48 g-line-height-1 g-color-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-0 g-pb-100',
				),
			),
			'attrs' => array(),
		),
		80 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="font-weight: 700;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT173").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT174"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-color-primary g-font-open-sans g-line-height-0_9 g-font-size-86',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-color-white g-font-open-sans g-font-size-28 g-line-height-0',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-100 g-pb-50',
				),
			),
			'attrs' => array(),
		),
		81 => array(
			'code' => '34.4.two_cols_with_text_and_icons',
			'cards' => array(
				'.landing-block-node-card' => 2,
			),
			'nodes' => array(
				'.landing-block-node-card-icon' => array(
					0 => 'landing-block-node-card-icon icon-finance-221 u-line-icon-pro',
					1 => 'landing-block-node-card-icon icon-finance-044 u-line-icon-pro',
				),
				'.landing-block-node-card-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT175"),
					1 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT176"),
				),
				'.landing-block-node-card-title' => array(
					0 => '<span style="font-weight: normal;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT177").'</span>',
					1 => '<span style="font-weight: normal;">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT178").'</span>',
				),
			),
			'style' => array(
				'.landing-block-node-card' => array(
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 g-mb-40  col-lg-6',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-font-size-default mb-0 g-color-white g-font-size-18',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title h5 text-uppercase g-font-weight-800 g-color-primary g-font-size-28',
				),
				'.landing-block-node-card-icon-container' => array(
					0 => 'landing-block-node-card-icon-container g-color-primary d-block g-font-size-48 g-line-height-1',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-100 g-pb-50',
				),
			),
			'attrs' => array(),
		),
		82 => array(
			'code' => '33.1.form_1_transparent_black_left_text',
			'anchor' => 'contacts',
			'cards' => array(
				'.landing-block-node-card-contact' => 3,
			),
			'nodes' => array(
				'.landing-block-node-bgimg' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.ru/b5391009/landing/633/63309acbd25f6ca3fccbf82ad81c69aa/Depositphotos_67891271_l-2015-min.jpg',
					),
				),
				'.landing-block-node-main-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT179"),
				),
				'.landing-block-node-text' => array(
					0 => '<p><span style="color: rgb(245, 245, 245);">'. Loc::getMessage("LANDING_DEMO_B24SYD__TEXT180").'</span></p>',
				),
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT181"),
				),
				'.landing-block-card-contact-icon' => array(
					0 => 'landing-block-card-contact-icon icon-hotel-restaurant-235 u-line-icon-pro',
					1 => 'landing-block-card-contact-icon icon-communication-033 u-line-icon-pro',
					2 => 'landing-block-card-contact-icon icon-communication-062 u-line-icon-pro',
				),
				'.landing-block-node-contact-text' => array(
					0 => Loc::getMessage("LANDING_DEMO_B24SYD__TEXT182"),
					1 => '<a href="tel:+32(0)333444555">+32 (0) 333 444 555</a>',
					2 => '<a href="mailto:info@company24.com">info@company24.com</a>',
				),
				'.landing-block-node-contact-link' => array(),
			),
			'style' => array(
				'.landing-block-node-main-title' => array(
					0 => 'landing-block-node-main-title js-animation fadeInUp h1 g-color-white mb-4 g-font-size-28',
				),
				'.landing-block-card-contact-icon-container' => array(
					0 => 'landing-block-card-contact-icon-container u-icon-v1 u-icon-size--sm g-color-white mr-2',
				),
				'.landing-block-node-title' => array(
					0 => 'h4 g-color-white mb-4 landing-block-node-title',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text js-animation fadeInUp g-line-height-1_5 text-left g-mb-40 g-color-white-opacity-0_6 g-font-size-18',
				),
				'.landing-block-node-contact-text' => array(
					0 => 'landing-block-node-contact-text g-color-white-opacity-0_6 mb-0',
					1 => 'landing-block-node-contact-text g-color-white-opacity-0_6',
				),
				'.landing-block-node-form' => array(
					0 => 'bitrix24forms landing-block-node-form js-animation fadeInUp g-brd-none g-brd-around--sm g-brd-white-opacity-0_6 g-px-0 g-px-20--sm g-px-45--lg g-py-0 g-py-30--sm g-py-60--lg u-form-alert-v1',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pos-rel g-pt-120 g-pb-120 landing-block-node-bgimg g-bg-size-cover g-bg-img-hero g-bg-cover g-bg-black-opacity-0_8--after',
				),
			),
		),
	),
);