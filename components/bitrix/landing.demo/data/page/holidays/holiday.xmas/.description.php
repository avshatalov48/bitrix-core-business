<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

return array(
	'old_id' => '805',
	'name' => Loc::getMessage("LANDING_DEMO___XMAS-TITLE"),
	'description' => Loc::getMessage("LANDING_DEMO___XMAS-DESCRIPTION"),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'Y',
	'type' => 'page',
	'version' => 2,
	'fields' => array(
		'TITLE' => Loc::getMessage("LANDING_DEMO___XMAS-TITLE"),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => 'travel',

			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'FONTS_CODE' => '<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Marmelad" data-font="g-font-marmelad" as="style"></noscript>
<link rel="preload" href="https://fonts.googleapis.com/css?family=Marmelad" data-font="g-font-marmelad" as="style" onload="this.removeAttribute(\'onload\');this.rel=\'stylesheet\'">
<style data-id="g-font-marmelad">.g-font-marmelad { font-family: "Marmelad", sans-serif; }</style>',
			'METAOG_TITLE' => Loc::getMessage("LANDING_DEMO___XMAS-TITLE"),
			'METAOG_DESCRIPTION' => Loc::getMessage("LANDING_DEMO___XMAS-DESCRIPTION"),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/holidays/holiday.xmas/preview.jpg',
			'GTM_USE' => 'N',
			'GACOUNTER_USE' => 'N',
			'GACOUNTER_SEND_CLICK' => 'N',
			'GACOUNTER_SEND_SHOW' => 'N',
			'METAROBOTS_INDEX' => 'Y',
			'BACKGROUND_USE' => 'N',
			'BACKGROUND_POSITION' => 'center',
			'YACOUNTER_USE' => 'N',
			'METAMAIN_USE' => 'N',
			'METAMAIN_TITLE' => Loc::getMessage("LANDING_DEMO___XMAS-TITLE"),
			'METAMAIN_DESCRIPTION' => Loc::getMessage("LANDING_DEMO___XMAS-DESCRIPTION"),
			'HEADBLOCK_USE' => 'N',
		),
	),
	'layout' => array(),
	'items' => array(
		'#block10046' => array(
			'old_id' => 10046,
			'code' => '50.1.ny_block',
			'access' => 'X',
			'nodes' => array(
				'.landing-block-node-card-img' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1080/img15.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
				'.landing-block-node-card-title' => array(
					0 => 'We wish you',
				),
				'.landing-block-node-card-text' => array(
					0 => '<p>a Merry Christmas!</p>',
				),
				'.landing-block-node-card-button' => array(
					0 => array(
						'href' => '#',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
						'text' => 'Our gifts',
					),
				),
			),
			'style' => array(
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title g-max-width-800 mx-auto g-font-weight-700 g-mb-20 js-animation fadeInUp g-font-marmelad g-text-transform-none g-font-size-55 g-color-white-opacity-0_8 g-line-height-1',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-max-width-800 mx-auto g-mb-35 js-animation fadeInUp g-font-marmelad g-font-size-90 g-font-weight-700 g-letter-spacing-minus-2 g-color-white-opacity-0_9 g-line-height-1',
				),
				'.landing-block-node-card-img' => array(
					0 => 'landing-block-node-card-img g-min-height-100vh g-flex-centered g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_3--after',
				),
				'.landing-block-node-card-button' => array(
					0 => 'landing-block-node-card-button button-new-year btn btn-lg g-font-weight-700 g-font-size-12 text-uppercase g-rounded-50 g-px-45 g-py-15 g-height-45 g-color-white js-animation fadeInUp g-bg-blue',
				),
				'.landing-block-node-card-button-container' => array(
					0 => 'landing-block-node-card-button-container g-max-width-800 mx-auto',
				),
				'#wrapper' => array(
					0 => 'landing-block g-overflow-hidden',
				),
			),
		),
		'#block10040' => array(
			'old_id' => 10040,
			'code' => '43.1.big_tiles_with_slider',
			'access' => 'X',
			'cards' => array(
				'.landing-block-node-card' => 1,
			),
			'nodes' => array(
				'.landing-block-node-img1' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/960x625/img3.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
				'.landing-block-node-subtitle' => array(
					0 => 'Christmas',
				),
				'.landing-block-node-title' => array(
					0 => 'Family holiday',
				),
				'.landing-block-node-text' => array(
					0 => '<p>Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim
							justo, rhoncus ut, imperdiet a, venenatis vitae, justo.</p>',
				),
				'.landing-block-node-button' => array(
					0 => array(
						'href' => '#',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
						'text' => 'View more',
					),
				),
				'.landing-block-node-img2' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/900x561/img1.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
				'.landing-block-node-card-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/900x561/img2.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
			),
			'style' => array(
				'.landing-block-node-block-bottom' => array(
					0 => 'landing-block-node-block-bottom js-animation fadeInUp col-md-6 d-flex align-items-center g-max-height-300--md g-max-height-625--lg text-center g-overflow-hidden',
					1 => 'landing-block-node-block-bottom js-animation fadeInUp col-md-6',
				),
				'.landing-block-node-block-top' => array(
					0 => 'landing-block-node-block-top js-animation fadeInRight col-md-6 d-flex align-items-center text-center g-pa-50',
				),
				'.landing-block-node-img1' => array(
					0 => 'landing-block-node-img1 col-md-6 g-bg-img-hero g-min-height-400 js-animation fadeInLeft',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-600 mb-0 g-text-transform-none g-font-marmelad g-font-size-26 g-font-weight-700 g-line-height-0_7 g-color-gray-light-v1',
				),
				'.landing-block-node-subtitle' => array(
					0 => 'landing-block-node-subtitle g-font-weight-700 g-mb-25 g-font-marmelad font-weight-normal g-font-size-40 g-color-main g-line-height-0_7',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-mb-35',
				),
				'.landing-block-node-button' => array(
					0 => 'landing-block-node-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-py-10 g-rounded-50',
				),
				'.landing-block-node-button-container' => array(
					0 => 'landing-block-node-button-container',
				),
				'#wrapper' => array(
					0 => 'landing-block',
				),
			),
		),
		'#block10047' => array(
			'old_id' => 10047,
			'code' => '27.2.one_col_full_title',
			'access' => 'X',
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="font-weight: bold;">Christmas gifts</span>',
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 g-font-size-50 g-font-marmelad container g-max-width-100x g-pl-0 g-pr-0',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-55 g-pb-7 text-center',
				),
			),
		),
		'#block10041' => array(
			'old_id' => 10041,
			'code' => '06.2.features_4_cols',
			'access' => 'X',
			'cards' => array(
				'.landing-block-card' => 4,
			),
			'nodes' => array(
				'.landing-block-node-element-icon' => array(
					0 => array(
						'classList' => array(
							0 => 'landing-block-node-element-icon fa fa-star-o',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					1 => array(
						'classList' => array(
							0 => 'landing-block-node-element-icon fa fa-envelope-o',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					2 => array(
						'classList' => array(
							0 => 'landing-block-node-element-icon fa fa-snowflake-o',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					3 => array(
						'classList' => array(
							0 => 'landing-block-node-element-icon fa fa-smile-o',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
				'.landing-block-node-element-title' => array(
					0 => 'Gift set',
					1 => 'Gift set',
					2 => 'Gift set',
					3 => 'Gift set',
				),
				'.landing-block-node-element-text' => array(
					0 => '<p>Donec id elit non mi porta gravida at eget metus id elit mi egetine. </p>',
					1 => '<p>Donec id elit non mi porta gravida at eget metus id elit mi egetine. </p>',
					2 => '<p>Donec id elit non mi porta gravida at eget metus id elit mi egetine. </p>',
					3 => '<p>Donec id elit non mi porta gravida at eget metus id elit mi egetine. </p>',
				),
				'.landing-block-node-element-list' => array(
					0 => '<li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">Candy set<br /></li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">flowers</li>
                            <li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">plush </li>',
					1 => '<li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">CANDY SET</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">FLOWERS</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">PLUSH </li>',
					2 => '<li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">CANDY SET</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">FLOWERS</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">PLUSH </li>',
					3 => '<li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">CANDY SET</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">FLOWERS</li><li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10">PLUSH </li>',
				),
			),
			'style' => array(
				'.landing-block-node-element' => array(
					0 => 'landing-block-node-element landing-block-card col-md-6 col-lg-3 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in js-animation fadeInLeft landing-card',
					1 => 'landing-block-node-element landing-block-card col-md-6 col-lg-3 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in js-animation fadeInLeft landing-card',
					2 => 'landing-block-node-element landing-block-card col-md-6 col-lg-3 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in js-animation fadeInLeft landing-card',
					3 => 'landing-block-node-element landing-block-card col-md-6 col-lg-3 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in js-animation fadeInLeft landing-card',
				),
				'.landing-block-node-element-title' => array(
					0 => 'landing-block-node-element-title h5 g-mb-10 g-text-transform-none g-font-size-25 g-font-marmelad g-color-gray-dark-v4',
					1 => 'landing-block-node-element-title h5 g-mb-10 g-text-transform-none g-font-size-25 g-font-marmelad g-color-gray-dark-v4',
					2 => 'landing-block-node-element-title h5 g-mb-10 g-text-transform-none g-font-size-25 g-font-marmelad g-color-gray-dark-v4',
					3 => 'landing-block-node-element-title h5 g-mb-10 g-text-transform-none g-font-size-25 g-font-marmelad g-color-gray-dark-v4',
				),
				'.landing-block-node-element-text' => array(
					0 => 'landing-block-node-element-text g-color-gray-dark-v4',
					1 => 'landing-block-node-element-text g-color-gray-dark-v4',
					2 => 'landing-block-node-element-text g-color-gray-dark-v4',
					3 => 'landing-block-node-element-text g-color-gray-dark-v4',
				),
				'.landing-block-node-element-list-item' => array(
					0 => 'landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10',
					1 => 'landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10',
					2 => 'landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10',
					3 => 'landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10',
					4 => 'landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10',
					5 => 'landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10',
					6 => 'landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10',
					7 => 'landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10',
					8 => 'landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10',
					9 => 'landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10',
					10 => 'landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10',
					11 => 'landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10',
				),
				'.landing-block-node-element-icon-container' => array(
					0 => 'landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15',
					1 => 'landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15',
					2 => 'landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15',
					3 => 'landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15',
				),
				'.landing-block-node-separator' => array(
					0 => 'landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15',
					1 => 'landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15',
					2 => 'landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15',
					3 => 'landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pb-80 g-pt-20',
				),
			),
		),
		'#block10042' => array(
			'old_id' => 10042,
			'code' => '01.big_with_text_3',
			'access' => 'X',
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1400x780/img1.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
				'.landing-block-node-title' => array(
					0 => '<span style="color: rgb(245, 245, 245);">Family holiday</span>',
				),
				'.landing-block-node-text' => array(
					0 => 'Morbi a suscipit ipsum. Suspendisse mollis libero ante.
			Pellentesque finibus convallis nulla vel placerat.',
				),
				'.landing-block-node-button' => array(
					0 => array(
						'href' => '#',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
						'text' => 'View more',
					),
				),
			),
			'style' => array(
				'.landing-block-node-container' => array(
					0 => 'container g-max-width-800 text-center u-bg-overlay__inner g-mx-1 js-animation landing-block-node-container fadeInDown',
				),
				'.landing-block-node-button-container' => array(
					0 => 'landing-block-node-button-container',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-line-height-1 g-font-weight-700 g-mb-20 g-text-transform-none g-color-primary g-font-marmelad g-font-size-76',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-mb-35 g-color-white',
				),
				'.landing-block-node-button' => array(
					0 => 'landing-block-node-button btn g-btn-primary g-btn-type-solid g-btn-px-l g-btn-size-md text-uppercase g-rounded-50 g-py-15 g-mb-15',
				),
				'#wrapper' => array(
					0 => 'landing-block landing-block-node-img u-bg-overlay g-flex-centered g-bg-img-hero g-pt-80 g-pb-80 g-min-height-100vh g-bg-black-opacity-0_4--after',
				),
			),
		),
		'#block10043' => array(
			'old_id' => 10043,
			'code' => '47.1.title_with_icon',
			'access' => 'X',
			'cards' => array(
				'.landing-block-node-icon-element' => 5,
			),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => 'Christmas gifts',
				),
				'.landing-block-node-icon' => array(
					0 => array('classList' => array('landing-block-node-icon fa fa-snowflake-o')),
					1 => array('classList' => array('landing-block-node-icon fa fa-snowflake-o')),
					2 => array('classList' => array('landing-block-node-icon fa fa-snowflake-o')),
					3 => array('classList' => array('landing-block-node-icon fa fa-snowflake-o')),
					4 => array('classList' => array('landing-block-node-icon fa fa-snowflake-o')),
				),
				'.landing-block-node-text' => array(
					0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
				Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis
				elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.
			</p>',
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title u-heading-v7__title g-font-weight-600 g-mb-20 js-animation fadeInUp font-weight-normal g-color-main g-font-marmelad g-font-size-65',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-color-gray-dark-v5 mb-0 js-animation fadeInUp',
				),
				'.landing-block-node-icon-element@0' => array(
					0 => 'landing-block-node-icon-element g-color-primary d-inline g-font-size-8'
				),
				'.landing-block-node-icon-element@1' => array(
					0 => 'landing-block-node-icon-element g-color-primary d-inline g-font-size-11'
				),
				'.landing-block-node-icon-element@2' => array(
					0 => 'landing-block-node-icon-element g-color-primary d-inline g-font-size-14'
				),
				'.landing-block-node-icon-element@3' => array(
					0 => 'landing-block-node-icon-element g-color-primary d-inline g-font-size-11'
				),
				'.landing-block-node-icon-element@4' => array(
					0 => 'landing-block-node-icon-element g-color-primary d-inline g-font-size-8'
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-45 g-pb-0',
				),
			),
		),
		'#block10044' => array(
			'old_id' => 10044,
			'code' => '44.7.three_columns_with_img_and_price',
			'access' => 'X',
			'cards' => array(
				'.landing-block-node-card' => 3,
			),
			'nodes' => array(
				'.landing-block-node-card-title' => array(
					0 => '<span style="font-style: normal;">Christmas toys</span>',
					1 => '<span style="font-style: normal;">Surprises</span>',
					2 => '<span style="font-style: normal;">Funny socks</span>',
				),
				'.landing-block-node-card-subtitle' => array(
					0 => '
							Fringilla
							porttitor
						',
					1 => '
							Fringilla
							porttitor
						',
					2 => '
							Fringilla
							porttitor
						',
				),
				'.landing-block-node-card-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/740x442/img1.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					1 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/740x442/img2.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					2 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/740x442/img3.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
				'.landing-block-node-card-price-subtitle' => array(
					0 => '
								From
							',
					1 => '
								From
							',
					2 => '
								From
							',
				),
				'.landing-block-node-card-price' => array(
					0 => '
								$350.00
							',
					1 => '
								$600.00
							',
					2 => '
								$200.00
							',
				),
				'.landing-block-node-card-text' => array(
					0 => '
							<p>
								Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
							</p>
						',
					1 => '
							<p>
								Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
							</p>
						',
					2 => '
							<p>
								Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
							</p>
						',
				),
				'.landing-block-node-card-button' => array(
					0 => array(
						'href' => '#',
						'target' => null,
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
						'text' => 'Order Now',
					),
					1 => array(
						'href' => '#',
						'target' => null,
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
						'text' => 'Order Now',
					),
					2 => array(
						'href' => '#',
						'target' => null,
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
						'text' => 'Order Now',
					),
				),
			),
			'style' => array(
				'.landing-block-node-card' => array(
					0 => 'landing-block-node-card col-md-6 col-lg-4 g-mb-30 landing-card',
					1 => 'landing-block-node-card col-md-6 col-lg-4 g-mb-30 landing-card',
					2 => 'landing-block-node-card col-md-6 col-lg-4 g-mb-30 landing-card',
				),
				'.landing-block-node-card-container-top' => array(
					0 => 'landing-block-node-card-container-top g-bg-primary g-pa-20',
					1 => 'landing-block-node-card-container-top g-bg-primary g-pa-20',
					2 => 'landing-block-node-card-container-top g-bg-primary g-pa-20',
				),
				'.landing-block-node-card-container-bottom' => array(
					0 => 'landing-block-node-card-container-bottom h-100 g-pa-40 d-flex flex-column',
					1 => 'landing-block-node-card-container-bottom h-100 g-pa-40 d-flex flex-column',
					2 => 'landing-block-node-card-container-bottom h-100 g-pa-40 d-flex flex-column',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title font-italic g-font-weight-700 g-color-white mb-0 g-font-marmelad g-font-size-24',
					1 => 'landing-block-node-card-title font-italic g-font-weight-700 g-color-white mb-0 g-font-marmelad g-font-size-24',
					2 => 'landing-block-node-card-title font-italic g-font-weight-700 g-color-white mb-0 g-font-marmelad g-font-size-24',
				),
				'.landing-block-node-card-subtitle' => array(
					0 => 'landing-block-node-card-subtitle g-color-white-opacity-0_6',
					1 => 'landing-block-node-card-subtitle g-color-white-opacity-0_6',
					2 => 'landing-block-node-card-subtitle g-color-white-opacity-0_6',
				),
				'.landing-block-node-card-price-subtitle' => array(
					0 => 'landing-block-node-card-price-subtitle g-color-gray-light-v1',
					1 => 'landing-block-node-card-price-subtitle g-color-gray-light-v1',
					2 => 'landing-block-node-card-price-subtitle g-color-gray-light-v1',
				),
				'.landing-block-node-card-price' => array(
					0 => 'landing-block-node-card-price g-font-weight-700 g-color-primary g-font-size-24 g-mt-10',
					1 => 'landing-block-node-card-price g-font-weight-700 g-color-primary g-font-size-24 g-mt-10',
					2 => 'landing-block-node-card-price g-font-weight-700 g-color-primary g-font-size-24 g-mt-10',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-color-gray-light-v1 g-mb-40',
					1 => 'landing-block-node-card-text g-color-gray-light-v1 g-mb-40',
					2 => 'landing-block-node-card-text g-color-gray-light-v1 g-mb-40',
				),
				'.landing-block-node-card-button' => array(
					0 => 'landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-py-15 g-rounded-50',
					1 => 'landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-py-15 g-rounded-50',
					2 => 'landing-block-node-card-button btn g-btn-type-solid g-btn-size-sm g-btn-px-l text-uppercase g-btn-primary g-py-15 g-rounded-50',
				),
				'.landing-block-node-card-button-container' => array(
					0 => 'landing-block-node-card-button-container mt-auto',
					1 => 'landing-block-node-card-button-container mt-auto',
					2 => 'landing-block-node-card-button-container mt-auto',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-65 g-pb-65',
				),
			),
		),
		'#block10048' => array(
			'old_id' => 10048,
			'code' => '46.3.cover_with_blocks_slider',
			'access' => 'X',
			'cards' => array(
				'.landing-block-node-card' => 2,
			),
			'nodes' => array(
				'.landing-block-node-bgimg' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img51.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
				'.landing-block-node-card-photo' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/600x462/img1.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					1 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/600x462/img2.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
				'.landing-block-node-card-title' => array(
					0 => '<span style="font-style: normal;">Special gift set 1</span>',
					1 => '<span style="font-style: normal;">Special gift set 2</span>',
				),
				'.landing-block-node-card-subtitle' => array(
					0 => 'from $1000',
					1 => 'from $800',
				),
				'.landing-block-node-card-text' => array(
					0 => '<p>Curabitur eget
								tortor sed urna faucibus iaculis id et nulla. Aliquam erat volutpat. Donec sed fringilla
								quam. Sed tincidunt volutpat iaculis. Pellentesque maximus ut eros eget congue. Fusce ac
								auctor urna, ac tempus orci.
							</p>',
					1 => '<p>Curabitur eget
								tortor sed urna faucibus iaculis id et nulla. Aliquam erat volutpat. Donec sed fringilla
								quam. Sed tincidunt volutpat iaculis. Pellentesque maximus ut eros eget congue. Fusce ac
								auctor urna, ac tempus orci.
							</p>',
				),
				'.landing-block-node-card-button' => array(
					0 => array(
						'href' => '#',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
						'text' => 'View more',
					),
					1 => array(
						'href' => '#',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
						'text' => 'View more',
					),
				),
			),
			'style' => array(
				'.landing-block-node-card-text-container' => array(
					0 => 'landing-block-node-card-text-container g-bg-white g-pa-40',
					1 => 'landing-block-node-card-text-container g-bg-white g-pa-40',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title js-animation fadeInRightBig g-font-size-30 font-italic g-font-weight-700 g-mb-20 g-font-marmelad',
					1 => 'landing-block-node-card-title js-animation fadeInRightBig g-font-size-30 font-italic g-font-weight-700 g-mb-20 g-font-marmelad',
				),
				'.landing-block-node-card-subtitle' => array(
					0 => 'landing-block-node-card-subtitle text-uppercase g-font-weight-700 g-font-size-12 g-color-primary g-mb-25',
					1 => 'landing-block-node-card-subtitle text-uppercase g-font-weight-700 g-font-size-12 g-color-primary g-mb-25',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text js-animation fadeInRightBig g-color-gray-light-v1 g-mb-40',
					1 => 'landing-block-node-card-text js-animation fadeInLeft g-color-gray-light-v1 g-mb-40',
				),
				'.landing-block-node-card-button' => array(
					0 => 'landing-block-node-card-button js-animation fadeInRightBig btn g-btn-primary g-btn-type-solid g-btn-size-sm g-btn-px-m text-uppercase g-pa-15 g-rounded-50',
					1 => 'landing-block-node-card-button js-animation fadeInRightBig btn g-btn-primary g-btn-type-solid g-btn-size-sm g-btn-px-m text-uppercase g-pa-15 g-rounded-50',
				),
				'.landing-block-node-card-button-container' => array(
					0 => 'landing-block-node-card-button-container',
					1 => 'landing-block-node-card-button-container',
				),
				'#wrapper' => array(
					0 => 'landing-block landing-block-node-bgimg u-bg-overlay g-bg-img-hero g-bg-black-opacity-0_5--after g-pt-100 g-pb-100',
				),
			),
		),
		'#block10049' => array(
			'old_id' => 10049,
			'code' => '47.1.title_with_icon',
			'access' => 'X',
			'cards' => array(
				'.landing-block-node-icon-element' => 5,
			),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => 'Get in touch',
				),
				'.landing-block-node-icon' => array(
					0 => array('classList' => array('landing-block-node-icon fa fa-snowflake-o')),
					1 => array('classList' => array('landing-block-node-icon fa fa-snowflake-o')),
					2 => array('classList' => array('landing-block-node-icon fa fa-snowflake-o')),
					3 => array('classList' => array('landing-block-node-icon fa fa-snowflake-o')),
					4 => array('classList' => array('landing-block-node-icon fa fa-snowflake-o')),
				),
				'.landing-block-node-text' => array(
					0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in.
				Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis
				elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.
			</p>',
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title u-heading-v7__title g-font-weight-600 g-mb-20 js-animation fadeInUp font-weight-normal g-color-main g-font-marmelad g-font-size-65',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-color-gray-dark-v5 mb-0 js-animation fadeInUp',
				),
				'.landing-block-node-icon-element@0' => array(
					0 => 'landing-block-node-icon-element g-color-primary d-inline g-font-size-8'
				),
				'.landing-block-node-icon-element@1' => array(
					0 => 'landing-block-node-icon-element g-color-primary d-inline g-font-size-11'
				),
				'.landing-block-node-icon-element@2' => array(
					0 => 'landing-block-node-icon-element g-color-primary d-inline g-font-size-14'
				),
				'.landing-block-node-icon-element@3' => array(
					0 => 'landing-block-node-icon-element g-color-primary d-inline g-font-size-11'
				),
				'.landing-block-node-icon-element@4' => array(
					0 => 'landing-block-node-icon-element g-color-primary d-inline g-font-size-8'
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-45 g-pb-0',
				),
			),
		),
		'#block10045' => array(
			'old_id' => 10045,
			'code' => '33.3.form_1_transparent_black_no_text',
			'access' => 'X',
			'nodes' => array(
				'.landing-block-node-bgimg' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img52.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
			),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block g-pos-rel g-bg-primary-dark-v1 g-pt-120 g-pb-120 landing-block-node-bgimg g-bg-size-cover g-bg-img-hero g-bg-cover g-bg-black-opacity-0_7--after',
				),
			),
		),
	),
);