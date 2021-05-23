<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

return array(
	'old_id' => '703',
//	'code' => 'holiday.halloween',
	'name' => Loc::getMessage("LANDING_DEMO___HALLOWEEN-TITLE"),
	'description' => Loc::getMessage("LANDING_DEMO___HALLOWEEN-DESCRIPTION"),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'Y',
	'type' => 'page',
	'version' => 2,
	'fields' => array(
		'TITLE' => Loc::getMessage("LANDING_DEMO___HALLOWEEN-TITLE"),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => 'shipping',

			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'SETTINGS_HIDE_NOT_AVAILABLE' => 'L',
			'SETTINGS_HIDE_NOT_AVAILABLE_OFFERS' => 'N',
			'SETTINGS_PRODUCT_SUBSCRIPTION' => 'Y',
			'SETTINGS_USE_PRODUCT_QUANTITY' => 'Y',
			'SETTINGS_DISPLAY_COMPARE' => 'Y',
			'SETTINGS_PRICE_CODE' => array(
				0 => 'BASE',
			),
			'SETTINGS_USE_PRICE_COUNT' => 'N',
			'SETTINGS_SHOW_PRICE_COUNT' => 1,
			'SETTINGS_PRICE_VAT_INCLUDE' => 'Y',
			'SETTINGS_SHOW_OLD_PRICE' => 'Y',
			'SETTINGS_SHOW_DISCOUNT_PERCENT' => 'Y',
			'SETTINGS_USE_ENHANCED_ECOMMERCE' => 'Y',
			'METAOG_TITLE' => Loc::getMessage("LANDING_DEMO___HALLOWEEN-TITLE"),
			'METAOG_DESCRIPTION' => Loc::getMessage("LANDING_DEMO___HALLOWEEN-DESCRIPTION"),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/holidays/holiday.halloween/preview.jpg',
			'GTM_USE' => 'N',
			'GACOUNTER_USE' => 'N',
			'GACOUNTER_SEND_CLICK' => 'N',
			'GACOUNTER_SEND_SHOW' => 'N',
			'METAROBOTS_INDEX' => 'Y',
			'BACKGROUND_USE' => 'N',
			'BACKGROUND_POSITION' => 'center',
			'YACOUNTER_USE' => 'N',
			'METAMAIN_USE' => 'N',
			'METAMAIN_TITLE' => Loc::getMessage("LANDING_DEMO___HALLOWEEN-TITLE"),
			'METAMAIN_DESCRIPTION' => Loc::getMessage("LANDING_DEMO___HALLOWEEN-DESCRIPTION"),
			'HEADBLOCK_USE' => 'N',
		),
	),
	'layout' => array(),
	'items' => array(
		'#block8624' => array(
			'old_id' => 8624,
			'code' => '01.big_with_text_3',
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1900x1069/img1.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
				'.landing-block-node-title' => array(
					0 => 'WE WITCH YOU <br />A HAPPY HALLOWEEN',
				),
				'.landing-block-node-text' => array(
					0 => 'Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio. Praesent egestas ac arcu ac convallis.',
				),
				'.landing-block-node-button' => array(
					0 => array(
						'href' => '#',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
						'text' => 'Learn more',
					),
				),
			),
			'style' => array(
				'.landing-block-node-container' => array(
					0 => 'landing-block-node-container container g-max-width-800 js-animation fadeInDown text-center u-bg-overlay__inner g-mx-1',
				),
				'.landing-block-node-button-container' => array(
					0 => 'landing-block-node-button-container',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-mb-20 g-font-size-55 g-line-height-1_4',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-mb-35 g-color-white',
				),
				'.landing-block-node-button' => array(
					0 => 'landing-block-node-button btn g-btn-primary g-btn-type-solid g-btn-px-l g-btn-size-md text-uppercase g-rounded-50 g-py-15',
				),
				'#wrapper' => array(
					0 => 'landing-block landing-block-node-img u-bg-overlay g-flex-centered g-min-height-100vh g-bg-img-hero g-bg-black-opacity-0_5--after g-pt-80 g-pb-80 g-bg-attachment-scroll',
				),
			),
		),
		'#block8607' => array(
			'old_id' => 8607,
			'code' => '04.1.one_col_fix_with_title',
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => 'Our services',
				),
				'.landing-block-node-title' => array(
					0 => 'Halloween Gifts',
				),
			),
			'style' => array(
				'.landing-block-node-subtitle' => array(
					0 => 'landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-40',
				),
				'.landing-block-node-inner' => array(
					0 => 'landing-block-node-inner text-uppercase text-center u-heading-v2-4--bottom g-brd-primary',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-40 g-pb-40',
				),
			),
		),
		'#block8608' => array(
			'old_id' => 8608,
			'code' => '20.2.three_cols_fix_img_title_text',
			'cards' => array(
				'.landing-block-card' => 3,
			),
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/800x466/img5.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					1 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/800x466/img6.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
					2 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/800x466/img7.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
				'.landing-block-node-title' => array(
					0 => 'Halloween decorations',
					1 => 'Halloween boxes',
					2 => 'halloween sweets',
				),
				'.landing-block-node-text' => array(
					0 => '<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi.</p>',
					1 => '<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi.</p>',
					2 => '<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi.</p>',
				),
			),
			'style' => array(
				'.landing-block-card' => array(
					0 => 'landing-block-card js-animation fadeIn landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 ',
					1 => 'landing-block-card js-animation fadeIn landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 ',
					2 => 'landing-block-card js-animation fadeIn landing-block-node-block col-md-4 g-mb-30 g-mb-0--md g-pt-10 ',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-color-black g-mb-20',
					1 => 'landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-color-black g-mb-20',
					2 => 'landing-block-node-title text-uppercase g-font-weight-700 g-font-size-18 g-color-black g-mb-20',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-color-gray-da',
					1 => 'landing-block-node-text g-color-gray-da',
					2 => 'landing-block-node-text g-color-gray-dark-v5',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-10 g-pb-20',
				),
			),
		),
		'#block8609' => array(
			'old_id' => 8609,
			'code' => '03.3.one_col_big_with_text_and_title',
			'cards' => array(
				'.landing-block-card' => 1,
			),
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => 'About us',
				),
				'.landing-block-node-title' => array(
					0 => 'Howl-O-Ween!<br />',
				),
				'.landing-block-node-text' => array(
					0 => '
						<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci
							viverra eros, fringilla porttitor lorem eros vel odio. Praesent egestas ac arcu ac convallis. Donec ut diam risus purus.</p>
					',
				),
			),
			'style' => array(
				'.landing-block-inner-container' => array(
					0 => 'landing-block-inner-container row no-gutters align-items-start',
				),
				'.landing-block-card' => array(
					0 => 'landing-block-card js-animation fadeIn col-md-12 col-lg-12 g-flex-centered ',
				),
				'.landing-block-node-subtitle' => array(
					0 => 'landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-mb-20 g-color-white',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-color-white g-mb-minus-10',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-color-white',
				),
				'.landing-block-node-card-header' => array(
					0 => 'landing-block-node-card-header text-uppercase u-heading-v2-4--bottom g-mb-40 g-brd-white',
				),
				'#wrapper' => array(
					0 => 'landing-block container-fluid px-0 g-bg-lightred',
				),
			),
		),
		'#block8625' => array(
			'old_id' => 8625,
			'code' => '01.big_with_text_3',
			'nodes' => array(
				'.landing-block-node-img' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1900x927/img1.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
				'.landing-block-node-title' => array(
					0 => 'NOW WELCOMING <br />CANDY COLLECTORS',
				),
				'.landing-block-node-text' => array(
					0 => 'Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio. Praesent egestas ac arcu ac convallis.',
				),
				'.landing-block-node-button' => array(
					0 => array(
						'href' => '#',
						'target' => '_self',
						'attrs' => array(
							'data-embed' => null,
							'data-url' => null,
						),
						'text' => 'Learn more',
					),
				),
			),
			'style' => array(
				'.landing-block-node-container' => array(
					0 => 'landing-block-node-container container g-max-width-800 js-animation text-center u-bg-overlay__inner g-mx-1 fadeIn',
				),
				'.landing-block-node-button-container' => array(
					0 => 'landing-block-node-button-container',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title text-uppercase g-font-weight-700 g-color-white g-mb-20 g-font-size-55 g-line-height-1_4',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-mb-35 g-color-white',
				),
				'.landing-block-node-button' => array(
					0 => 'landing-block-node-button btn g-btn-primary g-btn-type-solid g-btn-px-l g-btn-size-md text-uppercase g-rounded-50 g-py-15',
				),
				'#wrapper' => array(
					0 => 'landing-block landing-block-node-img u-bg-overlay g-flex-centered g-min-height-100vh g-bg-img-hero g-bg-black-opacity-0_5--after g-pt-80 g-pb-80 g-bg-attachment-fixed',
				),
			),
		),
		'#block8611' => array(
			'old_id' => 8611,
			'code' => '02.three_cols_big_3',
			'cards' => array(
				'.landing-block-card-left' => 1,
			),
			'nodes' => array(
				'.landing-block-node-left-img' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1000x667/img9.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
				'.landing-block-node-left-title' => array(
					0 => 'Eat, Drink, And Be Scary',
				),
				'.landing-block-node-left-text' => array(
					0 => '
								<p>Etiam consectetur placerat gravida. Pellentesque ultricies mattis est, quis elementum neque pulvinar at.</p>
								<p>Aenean odio ante, varius vel tempor sed Ut condimentum ex ac enim ullamcorper volutpat. Integer arcu nisl, finibus vitae sodales vitae, malesuada ultricies sapien.</p>
							',
				),
				'.landing-block-node-center-subtitle' => array(
					0 => 'About us',
				),
				'.landing-block-node-center-title' => array(
					0 => 'trick <br />or <br />treat',
				),
				'.landing-block-node-center-text' => array(
					0 => '
						<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci
                        	viverra eros, fringilla porttitor lorem eros vel odio. Praesent egestas ac arcu ac convallis. Donec ut diam risus purus.</p>
					',
				),
				'.landing-block-node-right-img' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1600x1920/img4.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
			),
			'style' => array(
				'.landing-block-node-left' => array(
					0 => 'landing-block-node-left col-md-6 col-lg-4 order-2 order-md-1 g-theme-photography-bg-gray-dark-v4',
				),
				'.landing-block-node-left-title' => array(
					0 => 'landing-block-node-left-title js-animation fadeIn text-uppercase g-font-weight-700 g-font-size-20 g-color-white g-mb-10',
				),
				'.landing-block-node-left-text' => array(
					0 => 'landing-block-node-left-text js-animation fadeIn g-color-gray-light-v2',
				),
				'.landing-block-node-center' => array(
					0 => 'landing-block-node-center col-md-6 col-lg-4 order-1 order-md-2 d-flex justify-content-center flex-column g-bg-black g-pa-30',
				),
				'.landing-block-node-center-subtitle' => array(
					0 => 'landing-block-node-center-subtitle js-animation fadeIn h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20',
				),
				'.landing-block-node-center-title' => array(
					0 => 'landing-block-node-center-title js-animation fadeIn h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-color-white g-mb-minus-10',
				),
				'.landing-block-node-center-text' => array(
					0 => 'landing-block-node-center-text js-animation fadeIn g-color-gray-light-v2',
				),
				'.landing-block-node-header' => array(
					0 => 'landing-block-node-header text-center text-uppercase u-heading-v2-4--bottom g-brd-primary g-mb-40',
				),
				'#wrapper' => array(
					0 => 'landing-block container-fluid px-0',
				),
			),
		),
		'#block8612' => array(
			'old_id' => 8612,
			'code' => '33.3.form_1_transparent_black_no_text',
			'nodes' => array(
				'.landing-block-node-bgimg' => array(
					0 => array(
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1080/img11.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					),
				),
			),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block g-pos-rel g-bg-primary-dark-v1 g-pt-120 g-pb-120 landing-block-node-bgimg g-bg-size-cover g-bg-img-hero g-bg-cover g-bg-black-opacity-0_5--after g-bg-attachment-scroll',
				),
			),
		),
		'#block8606' => array(
			'old_id' => 8606,
			'code' => '17.1.copyright_with_social',
			'nodes' => array(
				'.landing-block-node-text' => array(
					0 => '
					&copy; 2018 All rights reserved.
				',
				),
			),
			'style' => array(
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text mr-1 js-animation animation-none animated',
				),
				'#wrapper' => array(
					0 => 'landing-block g-brd-top g-brd-gray-dark-v2 js-animation animation-none animated g-bg-black',
				),
			),
		),
	),
);