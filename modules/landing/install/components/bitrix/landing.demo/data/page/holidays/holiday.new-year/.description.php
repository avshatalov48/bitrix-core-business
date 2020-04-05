<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

return array(
	'old_id' => '806',
	'name' => Loc::getMessage("LANDING_DEMO___NEWYEAR-TITLE"),
	'description' => Loc::getMessage("LANDING_DEMO___NEWYEAR-DESCRIPTION"),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'Y',
	'type' => 'page',
	'sort' => \LandingSiteDemoComponent::checkActivePeriod(12,8,12,31) ? 11 : -201,
	'version' => 2,
	'fields' => array(
		'TITLE' => Loc::getMessage("LANDING_DEMO___NEWYEAR-TITLE"),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => 'real-estate',
			'THEME_CODE_TYPO' => 'real-estate',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'METAOG_TITLE' => Loc::getMessage("LANDING_DEMO___NEWYEAR-TITLE"),
			'METAOG_DESCRIPTION' => Loc::getMessage("LANDING_DEMO___NEWYEAR-DESCRIPTION"),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/holidays/holiday.new-year/preview.jpg',
			'GTM_USE' => 'N',
			'GACOUNTER_USE' => 'N',
			'GACOUNTER_SEND_CLICK' => 'N',
			'GACOUNTER_SEND_SHOW' => 'N',
			'METAROBOTS_INDEX' => 'Y',
			'BACKGROUND_USE' => 'N',
			'BACKGROUND_POSITION' => 'center',
			'YACOUNTER_USE' => 'N',
			'METAMAIN_USE' => 'N',
			'METAMAIN_TITLE' => Loc::getMessage("LANDING_DEMO___NEWYEAR-TITLE"),
			'METAMAIN_DESCRIPTION' => Loc::getMessage("LANDING_DEMO___NEWYEAR-DESCRIPTION"),
			'HEADBLOCK_USE' => 'N',
		),
	),
	'layout' => array(),
	'items' => array(
		'#block10073' => array(
			'old_id' => 10073,
			'code' => '50.1.ny_block',
			'nodes' => array(
				'.landing-block-node-card-img' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img50.jpg',
					),
				),
				'.landing-block-node-card-title' => array(
					0 => '2019<br />Happy<br />New year',
				),
				'.landing-block-node-card-text' => array(
					0 => '
						<p>Have a great holidays!</p>
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
						'text' => 'Thank you',
					),
				),
			),
			'style' => array(
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title g-font-roboto-slab g-max-width-800 mx-auto text-uppercase g-font-weight-700 g-color-white g-mb-20 js-animation fadeInUp g-font-size-86 custom-text-shadow-9 g-line-height-1_2',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text g-max-width-800 g-color-white-opacity-0_9 mx-auto g-mb-35 js-animation fadeInUp g-font-montserrat',
				),
				'.landing-block-node-card-img' => array(
					0 => 'landing-block-node-card-img g-flex-centered g-bg-cover g-bg-pos-center g-bg-img-hero g-bg-black-opacity-0_5--after g-min-height-100vh',
				),
				'.landing-block-node-card-button' => array(
					0 => 'landing-block-node-card-button button-new-year btn btn-lg g-font-weight-700 g-font-size-12 text-uppercase g-rounded-50 g-px-45 g-py-15 g-height-45 g-color-white js-animation fadeInUp g-bg-primary',
				),
				'.landing-block-node-card-button-container' => array(
					0 => 'landing-block-node-card-button-container g-max-width-800 mx-auto',
				),
				'#wrapper' => array(
					0 => 'landing-block g-overflow-hidden',
				),
			),
		),
		'#block10069' => array(
			'old_id' => 10069,
			'code' => '04.1.one_col_fix_with_title',
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => ' ',
				),
				'.landing-block-node-title' => array(
					0 => '<span style="color: rgb(78, 67, 83);">sweet gingerbreads</span>',
				),
			),
			'style' => array(
				'.landing-block-node-subtitle' => array(
					0 => 'landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10 text-uppercase g-font-roboto-slab g-color-main',
				),
				'.landing-block-node-inner' => array(
					0 => 'landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-55 g-pb-30 js-animation fadeInUp',
				),
			),
		),
		'#block10070' => array(
			'old_id' => 10070,
			'code' => '42.1.rest_menu',
			'cards' => array(
				'.landing-block-node-card' => 6,
			),
			'nodes' => array(
				'.landing-block-node-card-title' => array(
					0 => 'gingerbread',
					1 => 'GINGERBREAD',
					2 => 'GINGERBREAD',
					3 => 'GINGERBREAD',
					4 => 'GINGERBREAD',
					5 => 'GINGERBREAD',
				),
				'.landing-block-node-card-text' => array(
					0 => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>',
					1 => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>',
					2 => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>',
					3 => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>',
					4 => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>',
					5 => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>',
				),
				'.landing-block-node-card-price' => array(
					0 => '$12',
					1 => '$10',
					2 => '$11',
					3 => '$16',
					4 => '$14',
					5 => '$15',
				),
				'.landing-block-node-card-photo' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img12.jpg',
						'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/200x200/img12.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					1 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img13.jpg',
						'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/200x200/img13.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					2 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img14.jpg',
						'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/200x200/img14.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					3 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img15.jpg',
						'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/200x200/img15.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					4 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img16.jpg',
						'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/200x200/img16.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					5 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img17.jpg',
						'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/200x200/img17.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
			),
			'style' => array(
				'.landing-block-node-card' => array(
					0 => 'landing-block-node-card js-animation col-md-6 g-mb-50 fadeInUp landing-card',
					1 => 'landing-block-node-card js-animation col-md-6 g-mb-50 fadeInUp landing-card',
					2 => 'landing-block-node-card js-animation col-md-6 g-mb-50 fadeInUp landing-card',
					3 => 'landing-block-node-card js-animation col-md-6 g-mb-50 fadeInUp landing-card',
					4 => 'landing-block-node-card js-animation col-md-6 g-mb-50 fadeInUp landing-card',
					5 => 'landing-block-node-card js-animation col-md-6 g-mb-50 fadeInUp landing-card',
				),
				'.landing-block-node-card-title' => array(
					0 => 'landing-block-node-card-title align-self-center u-heading-v1__title g-color-black g-font-weight-700 g-font-size-13 text-uppercase mb-0 g-font-montserrat',
					1 => 'landing-block-node-card-title align-self-center u-heading-v1__title g-color-black g-font-weight-700 g-font-size-13 text-uppercase mb-0 g-font-montserrat',
					2 => 'landing-block-node-card-title align-self-center u-heading-v1__title g-color-black g-font-weight-700 g-font-size-13 text-uppercase mb-0 g-font-montserrat',
					3 => 'landing-block-node-card-title align-self-center u-heading-v1__title g-color-black g-font-weight-700 g-font-size-13 text-uppercase mb-0 g-font-montserrat',
					4 => 'landing-block-node-card-title align-self-center u-heading-v1__title g-color-black g-font-weight-700 g-font-size-13 text-uppercase mb-0 g-font-montserrat',
					5 => 'landing-block-node-card-title align-self-center u-heading-v1__title g-color-black g-font-weight-700 g-font-size-13 text-uppercase mb-0 g-font-montserrat',
				),
				'.landing-block-node-card-text' => array(
					0 => 'landing-block-node-card-text mb-0 g-font-montserrat',
					1 => 'landing-block-node-card-text mb-0 g-font-montserrat',
					2 => 'landing-block-node-card-text mb-0 g-font-montserrat',
					3 => 'landing-block-node-card-text mb-0 g-font-montserrat',
					4 => 'landing-block-node-card-text mb-0 g-font-montserrat',
					5 => 'landing-block-node-card-text mb-0 g-font-montserrat',
				),
				'.landing-block-node-card-price' => array(
					0 => 'landing-block-node-card-price g-font-weight-700 g-font-size-13 g-color-white g-bg-primary g-rounded-3 g-py-4 g-px-12',
					1 => 'landing-block-node-card-price g-font-weight-700 g-font-size-13 g-color-white g-bg-primary g-rounded-3 g-py-4 g-px-12',
					2 => 'landing-block-node-card-price g-font-weight-700 g-font-size-13 g-color-white g-bg-primary g-rounded-3 g-py-4 g-px-12',
					3 => 'landing-block-node-card-price g-font-weight-700 g-font-size-13 g-color-white g-bg-primary g-rounded-3 g-py-4 g-px-12',
					4 => 'landing-block-node-card-price g-font-weight-700 g-font-size-13 g-color-white g-bg-primary g-rounded-3 g-py-4 g-px-12',
					5 => 'landing-block-node-card-price g-font-weight-700 g-font-size-13 g-color-white g-bg-primary g-rounded-3 g-py-4 g-px-12',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-20 g-pb-20',
				),
			),
		),
		'#block10077' => array(
			'old_id' => 10077,
			'code' => '04.4.one_col_big_with_img',
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => 'happy new year',
				),
				'.landing-block-node-title' => array(
					0 => 'CONGRATULATIONS!',
				),
				'.landing-block-node-mainimg' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1080/img13.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
			),
			'style' => array(
				'.landing-block-node-subtitle' => array(
					0 => 'landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-color-white g-mb-20 g-font-montserrat',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-color-white g-mb-minus-10 g-font-roboto-slab',
				),
				'.landing-block-node-inner' => array(
					0 => 'landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-transparent',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInLeft landing-block-node-mainimg u-bg-overlay g-bg-img-hero g-bg-primary-opacity-0_8--after g-pb-60 g-pt-70',
				),
			),
		),
		'#block10075' => array(
			'old_id' => 10075,
			'code' => '01.big_with_text_3_1',
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1400x700/img11.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
				'.landing-block-node-title' => array(
					0 => 'We wish you a merry Christmas and <br />happy New year!',
				),
				'.landing-block-node-text' => array(
					0 => '
			Morbi a suscipit ipsum. Suspendisse mollis libero ante.
			Pellentesque finibus convallis nulla vel placerat.
		',
				),
			),
			'style' => array(
				'.landing-block-node-container' => array(
					0 => 'landing-block-node-container container g-max-width-800 js-animation fadeInDown text-center u-bg-overlay__inner g-mx-0',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-700 g-color-white g-mb-20 g-mt-20 g-font-roboto-slab g-text-transform-none g-font-size-65 g-line-height-1_2',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-color-white-opacity-0_7 g-mb-35 g-font-montserrat',
				),
				'#wrapper' => array(
					0 => 'landing-block landing-block-node-img u-bg-overlay g-flex-centered g-height-100vh g-bg-img-hero g-pt-80 g-pb-80 g-bg-black-opacity-0_4--after g-min-height-25vh',
				),
			),
		),
		'#block10071' => array(
			'old_id' => 10071,
			'code' => '05.features_4_cols_with_title',
			'cards' => array(
				'.landing-block-card' => 4,
			),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => 'candy shapes',
				),
				'.landing-block-node-subtitle' => array(
					0 => '<span style="color: rgb(78, 67, 83);">CHOOSE ANYTHING YOU LIKE</span>',
				),
				'.landing-block-node-element-icon' => array(
					0 => array(
						'classList' => array(
							0 => 'landing-block-node-element-icon fa fa-snowflake-o',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					1 => array(
						'classList' => array(
							0 => 'landing-block-node-element-icon fa fa-star-o',
						),
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					2 => array(
						'classList' => array(
							0 => 'landing-block-node-element-icon fa fa-heart-o',
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
					0 => 'snowflake',
					1 => 'star',
					2 => 'heart',
					3 => 'smiley',
				),
				'.landing-block-node-element-text' => array(
					0 => '<span style="color: rgb(167, 167, 167);"> Vivamus a fringilla lorem, vel faucibus ante.</span>',
					1 => '<span style="color: rgb(167, 167, 167);"> Vivamus a fringilla lorem, vel faucibus ante.</span>',
					2 => '<span style="color: rgb(167, 167, 167);"> Vivamus a fringilla lorem, vel faucibus ante.</span>',
					3 => '<span style="color: rgb(167, 167, 167);"> Vivamus a fringilla lorem, vel faucibus ante.</span>',
				),
				'.landing-block-node-element-list' => array(
					0 => '<br />',
					1 => '<br />',
					2 => '<br />',
					3 => '<br />',
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20 g-font-montserrat',
				),
				'.landing-block-node-subtitle' => array(
					0 => 'landing-block-node-subtitle h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10 g-font-roboto-slab',
				),
				'.landing-block-card' => array(
					0 => 'landing-block-node-element landing-block-card col-md-6 col-lg-3 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in js-animation fadeInLeft landing-card',
					1 => 'landing-block-node-element landing-block-card col-md-6 col-lg-3 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in js-animation fadeInLeft landing-card',
					2 => 'landing-block-node-element landing-block-card col-md-6 col-lg-3 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in js-animation fadeInLeft landing-card',
					3 => 'landing-block-node-element landing-block-card col-md-6 col-lg-3 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in js-animation fadeInLeft landing-card',
				),
				'.landing-block-node-element-title' => array(
					0 => 'landing-block-node-element-title h5 text-uppercase g-color-black g-mb-10 g-font-montserrat',
					1 => 'landing-block-node-element-title h5 text-uppercase g-color-black g-mb-10 g-font-montserrat',
					2 => 'landing-block-node-element-title h5 text-uppercase g-color-black g-mb-10 g-font-montserrat',
					3 => 'landing-block-node-element-title h5 text-uppercase g-color-black g-mb-10 g-font-montserrat',
				),
				'.landing-block-node-element-text' => array(
					0 => 'landing-block-node-element-text g-color-gray-dark-v4 g-font-size-13--md g-font-montserrat',
					1 => 'landing-block-node-element-text g-color-gray-dark-v4 g-font-size-13--md g-font-montserrat',
					2 => 'landing-block-node-element-text g-color-gray-dark-v4 g-font-size-13--md g-font-montserrat',
					3 => 'landing-block-node-element-text g-color-gray-dark-v4 g-font-size-13--md g-font-montserrat',
				),
				'.landing-block-node-element-icon-container' => array(
					0 => 'landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15',
					1 => 'landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15',
					2 => 'landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15',
					3 => 'landing-block-node-element-icon-container d-block g-color-primary g-font-size-40 g-mb-15',
				),
				'.landing-block-node-header' => array(
					0 => 'landing-block-node-header text-uppercase text-center u-heading-v2-4--bottom g-brd-primary g-mb-80 js-animation fadeIn',
				),
				'.landing-block-node-element-separator' => array(
					0 => 'landing-block-node-element-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15',
					1 => 'landing-block-node-element-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15',
					2 => 'landing-block-node-element-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15',
					3 => 'landing-block-node-element-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15',
				),
				'#wrapper' => array(
					0 => 'landing-block g-py-80',
				),
			),
		),
		'#block10072' => array(
			'old_id' => 10072,
			'code' => '04.7.one_col_fix_with_title_and_text_2',
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => ' ',
				),
				'.landing-block-node-title' => array(
					0 => '<span style="color: rgb(78, 67, 83);">Happy new year!</span>',
				),
				'.landing-block-node-text' => array(
					0 => ' ',
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-mb-minus-10 g-color-black-opacity-0_8 g-font-roboto-slab',
				),
				'.landing-block-node-subtitle' => array(
					0 => 'landing-block-node-subtitle g-font-weight-700 g-font-size-12 g-mb-15 g-color-primary',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-color-gray-dark-v5',
				),
				'.landing-block-node-inner' => array(
					0 => 'landing-block-node-inner text-uppercase u-heading-v2-4--bottom g-brd-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block g-py-20 g-bg-main js-animation fadeInUp g-pb-60 g-pt-30',
				),
			),
		),
		'#block10074' => array(
			'old_id' => 10074,
			'code' => '33.3.form_1_transparent_black_no_text',
			'nodes' => array(
				'.landing-block-node-bgimg' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1080/img14.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
			),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block g-pos-rel g-bg-primary-dark-v1 g-pt-120 g-pb-120 landing-block-node-bgimg g-bg-size-cover g-bg-img-hero g-bg-cover g-bg-black-opacity-0_6--after',
				),
			),
		),
	),
);