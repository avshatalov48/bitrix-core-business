<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

return [
	'old_id' => '389',
	'code' => 'krayt-business',
	'name' => Loc::getMessage('LANDING_DEMO_KRAYT_BUSINESS_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_KRAYT_BUSINESS_DESCRIPTION'),
	'show_in_list' => 'Y',
	'type' => 'page',
	'version' => 3,
	'fields' => [
		'TITLE' =>  Loc::getMessage('LANDING_DEMO_KRAYT_BUSINESS_TITLE'),
		'RULE' => NULL,
		'ADDITIONAL_FIELDS' => [
			'METAMAIN_TITLE' =>  Loc::getMessage('LANDING_DEMO_KRAYT_BUSINESS_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_KRAYT_BUSINESS_DESCRIPTION'),
			'METAOG_TITLE' =>  Loc::getMessage('LANDING_DEMO_KRAYT_BUSINESS_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_KRAYT_BUSINESS_DESCRIPTION'),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/krayt-business/preview.jpg',
			'THEME_CODE' => '3corporate',
		],
	],
	'items' => [
		'#block4001' => [
				'old_id' => 228,
				'code' => '35.8.header_logo_and_slogan_row',
				'access' => 'X',
				'nodes' =>
					array (
						'.landing-block-node-logo' =>
							array (
								0 =>
									array (
										'alt' => 'Logo',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/logos/real-estate-logo.png',
									),
							),
						'.landing-block-node-text' =>
							array (
								0 => '<a href="tel:+469548521">+</a>7 999 876 54 32',
							),
					),
				'style' =>
					array (
						'.landing-block-node-row' =>
							array (
								0 => 'landing-block-node-row row no-gutters justify-content-between align-items-center text-center',
							),
						'.landing-block-node-text' =>
							array (
								0 => 'landing-block-node-text h5 g-font-size-12 mb-0 g-color-blue g-font-montserrat',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block landing-block-menu g-pt-20 g-pb-20',
							),
					),
			],
		'#block4002' => [
				'old_id' => 191,
				'code' => '43.4.cover_with_price_text_button_bgimg',
				'access' => 'X',
				'cards' =>
					array (
						'.landing-block-node-card' =>
							array (
								'source' =>
									array (
										0 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
									),
							),
					),
				'nodes' =>
					array (
						'.landing-block-node-card-price' =>
							array (
								0 => '<span bxstyle="color: rgb(3, 169, 244);">2000+</span> Projects',
							),
						'.landing-block-node-card-title' =>
							array (
								0 => '<span bxstyle="font-weight: normal;">Are you</span> Ready <p><span bxstyle="font-weight: normal;">to win in</span> Business?</p>',
							),
						'.landing-block-node-card-text' =>
							array (
								0 => '<p>
									Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
								</p>',
							),
						'.landing-block-node-card-button' =>
							array (
								0 =>
									array (
										'href' => '#block207',
										'target' => '_self',
										'attrs' =>
											array (
												'data-embed' => NULL,
												'data-url' => NULL,
											),
										'text' => 'Learn more',
									),
							),
						'.landing-block-node-card-bgimg' =>
							array (
								0 =>
									array (
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img53.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '934',
										'id2x' => '935',
									),
							),
					),
				'style' =>
					array (
						'.landing-block-node-card-price' =>
							array (
								0 => 'landing-block-node-card-price u-ribbon-v1 text-uppercase g-pos-rel g-line-height-1_2 g-font-weight-700 g-font-size-16 g-font-size-18 g-pb-10 g-pt-10 g-pr-10 g-pl-10 g-mb-10 g-font-montserrat g-color-white g-bg-black-opacity-0_5',
							),
						'.landing-block-node-card-title' =>
							array (
								0 => 'landing-block-node-card-title g-pos-rel g-font-weight-700 g-font-montserrat g-mb-20 g-text-transform-none g-font-size-80 g-color-white g-line-height-1',
							),
						'.landing-block-node-card-text' =>
							array (
								0 => 'landing-block-node-card-text g-mb-20 g-color-white-opacity-0_8 g-font-montserrat g-font-size-20 g-line-height-1_5',
							),
						'.landing-block-node-card-button' =>
							array (
								0 => 'landing-block-node-card-button btn btn-md text-uppercase g-font-weight-700 g-font-size-12 g-py-10 g-px-25 g-rounded-3 g-font-montserrat u-btn-twitter g-brd-10',
							),
						'.landing-block-node-card-bgimg' =>
							array (
								0 => 'landing-block-node-card-bgimg d-flex align-items-center justify-content-center g-bg-img-hero u-bg-overlay w-100 h-100 g-bg-none--after g-min-height-90vh',
							),
						'.landing-block-node-card-container' =>
							array (
								0 => 'landing-block-node-card-container js-animation fadeIn container g-mx-0 g-pa-0 g-max-width-800 u-bg-overlay__inner',
							),
						'.landing-block-node-card-button-container' =>
							array (
								0 => 'landing-block-node-card-button-container',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block l-d-xs-none',
							),
					),
			],
		'#block4003' => [
				'old_id' => 211,
				'code' => '43.4.cover_with_price_text_button_bgimg',
				'access' => 'X',
				'cards' =>
					array (
						'.landing-block-node-card' =>
							array (
								'source' =>
									array (
										0 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
									),
							),
					),
				'nodes' =>
					array (
						'.landing-block-node-card-price' =>
							array (
								0 => '<span bxstyle="color: rgb(3, 169, 244);">2 000+</span> Projects',
							),
						'.landing-block-node-card-title' =>
							array (
								0 => '<span bxstyle="font-weight: normal;">Are you</span> Ready <p><span bxstyle="font-weight: normal;">to win in</span> Business?</p>',
							),
						'.landing-block-node-card-text' =>
							array (
								0 => '<p>
									Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
								</p>',
							),
						'.landing-block-node-card-button' =>
							array (
								0 =>
									array (
										'href' => '#block207',
										'target' => '_self',
										'attrs' =>
											array (
												'data-embed' => NULL,
												'data-url' => NULL,
											),
										'text' => 'Learn more',
									),
							),
						'.landing-block-node-card-bgimg' =>
							array (
								0 =>
									array (
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img53.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '784',
										'id2x' => '785',
									),
							),
					),
				'style' =>
					array (
						'.landing-block-node-card-price' =>
							array (
								0 => 'landing-block-node-card-price u-ribbon-v1 text-uppercase g-pos-rel g-line-height-1_2 g-font-weight-700 g-pb-10 g-pt-10 g-pr-10 g-pl-10 g-mb-10 g-font-montserrat g-color-white g-bg-black-opacity-0_5 g-font-size-15',
							),
						'.landing-block-node-card-title' =>
							array (
								0 => 'landing-block-node-card-title g-pos-rel g-line-height-1 g-font-weight-700 g-color-white g-font-montserrat g-mb-20 g-text-transform-none g-font-size-36',
							),
						'.landing-block-node-card-text' =>
							array (
								0 => 'landing-block-node-card-text g-mb-20 g-color-white-opacity-0_8 g-font-montserrat g-line-height-1_5 g-font-size-14',
							),
						'.landing-block-node-card-button' =>
							array (
								0 => 'landing-block-node-card-button btn btn-md text-uppercase g-font-weight-700 g-font-size-12 g-py-10 g-px-25 g-rounded-3 g-font-montserrat u-btn-twitter g-brd-6',
							),
						'.landing-block-node-card-bgimg' =>
							array (
								0 => 'landing-block-node-card-bgimg d-flex align-items-center justify-content-center g-bg-img-hero u-bg-overlay w-100 h-100 g-bg-none--after g-min-height-90vh g-pl-10',
							),
						'.landing-block-node-card-container' =>
							array (
								0 => 'landing-block-node-card-container js-animation fadeIn container g-mx-0 g-pa-0 g-max-width-800 g-pb-auto g-pt-auto u-bg-overlay__inner',
							),
						'.landing-block-node-card-button-container' =>
							array (
								0 => 'landing-block-node-card-button-container',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block l-d-lg-none l-d-md-none',
							),
					),
			],
		'#block4004' => [
				'old_id' => 194,
				'code' => '27.one_col_fix_title_and_text_2',
				'access' => 'X',
				'nodes' =>
					array (
						'.landing-block-node-title' =>
							array (
								0 => '<span bxstyle="font-weight: bold;">About</span> Company',
							),
						'.landing-block-node-text' =>
							array (
								0 => '<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit</p>',
							),
					),
				'style' =>
					array (
						'.landing-block-node-title' =>
							array (
								0 => 'landing-block-node-title h2 g-font-montserrat g-font-size-50 g-mb-auto',
							),
						'.landing-block-node-text' =>
							array (
								0 => 'landing-block-node-text g-pb-1 g-font-montserrat g-font-size-20 g-color-gray-dark-v3',
							),
						'.landing-block-node-text-container' =>
							array (
								0 => 'landing-block-node-text-container container g-max-width-800',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block js-animation g-pb-5 g-pt-35 pulse l-d-xs-none',
							),
					),
			],
		'#block4005' => [
				'old_id' => 221,
				'code' => '27.one_col_fix_title_and_text_2',
				'access' => 'X',
				'nodes' =>
					array (
						'.landing-block-node-title' =>
							array (
								0 => '<span bxstyle="font-weight: bold;">About</span> Company',
							),
						'.landing-block-node-text' =>
							array (
								0 => '<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit</p>',
							),
					),
				'style' =>
					array (
						'.landing-block-node-title' =>
							array (
								0 => 'landing-block-node-title h2 g-font-montserrat g-mb-auto g-font-size-50',
							),
						'.landing-block-node-text' =>
							array (
								0 => 'landing-block-node-text g-pb-1 g-font-montserrat g-color-gray-dark-v3 g-font-size-15',
							),
						'.landing-block-node-text-container' =>
							array (
								0 => 'landing-block-node-text-container container g-max-width-800',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block js-animation g-pb-5 pulse l-d-lg-none l-d-md-none g-pt-auto',
							),
					),
			],
		'#block4006' => [
				'old_id' => 195,
				'code' => '57.text_with_factoid_right',
				'access' => 'X',
				'nodes' =>
					array (
						'.landing-block-node-title' =>
							array (
								0 => '- <span bxstyle="font-weight: normal;">ALEX BRAUN, CEO OF COMPANY</span>',
							),
						'.landing-block-node-text' =>
							array (
								0 => '<p>Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.</p>',
							),
						'.landing-block-node-number' =>
							array (
								0 => '5 311',
							),
						'.landing-block-node-number-text' =>
							array (
								0 => '<span bxstyle="color: rgb(3, 169, 244);">clients</span>',
							),
					),
				'style' =>
					array (
						'.landing-block-node-text-block' =>
							array (
								0 => 'g-mb-20 landing-block-node-text-block g-pl-20 g-pr-20 g-pb-20 g-pt-20 js-animation col-lg-8 animated',
							),
						'.landing-block-node-number-block' =>
							array (
								0 => 'landing-block-node-number-block d-flex js-animation col-lg-4 g-pl-30 animated',
							),
						'.landing-block-node-title' =>
							array (
								0 => 'landing-block-node-title g-font-weight-600 text-uppercase g-text-break-word g-font-montserrat g-font-size-20 g-color-gray-dark-v5 g-mb-15',
							),
						'.landing-block-node-text' =>
							array (
								0 => 'landing-block-node-text g-font-montserrat g-font-size-20 g-color-gray-dark-v3',
							),
						'.landing-block-node-number' =>
							array (
								0 => 'landing-block-node-number g-color-black g-font-weight-600 g-font-size-80 g-text-break-word g-font-montserrat g-line-height-1',
							),
						'.landing-block-node-number-text' =>
							array (
								0 => 'landing-block-node-number-text g-color-black g-font-size-30 g-text-break-word g-font-montserrat g-letter-spacing-12',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block row no-gutters g-pt-15 g-pb-50 g-pl-auto g-pr-auto',
							),
					),
			],
		'#block4007' => [
				'old_id' => 192,
				'code' => '38.1.text_with_bgimg_img_and_text_blocks',
				'access' => 'X',
				'cards' =>
					array (
						'.landing-block-node-card' =>
							array (
								'source' =>
									array (
										0 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										1 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										2 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										3 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
									),
							),
					),
				'nodes' =>
					array (
						'.landing-block-node-subtitle' =>
							array (
								0 => '<span bxstyle="font-weight: normal;color: rgb(33, 33, 33);">make world</span> better',
							),
						'.landing-block-node-title' =>
							array (
								0 => '<span bxstyle="font-weight: normal;">
							WE MAKE</span> <span bxstyle="font-weight: normal;">BUSINESS </span>EASIER',
							),
						'.landing-block-node-bgimg' =>
							array (
								0 =>
									array (
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1110/img1.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '786',
										'id2x' => '787',
									),
							),
						'.landing-block-node-text' =>
							array (
								0 => '<p class="mb-0">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis <span bxstyle="background-color: rgb(3, 169, 244); color: rgb(245, 245, 245);">nostrud exerci tation</span> ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.</p>',
							),
						'.landing-block-node-card-title' =>
							array (
								0 => 'COUNSELING',
								1 => 'TRAINING',
								2 => 'EDUCATION<br />',
								3 => 'SUPPORT',
							),
						'.landing-block-node-card-text' =>
							array (
								0 => '
								<p class="mb-0">Quisque rhoncus euismod pulvinar. Nulla non arcu at lectus. Vestibulum
									fringilla velit rhoncus euismod rhoncus turpis. Mauris molestie ullamcorper nisl
									eget
									hendrerit.</p>
							',
								1 => '
								<p class="mb-0">Integer accumsan maximus leo, et consectetur metus vestibulum in.
									Vestibulum
									viverra justo odio purus a libero luctus. Proin tempor dolor ac dolor feugiat,
									placerat
									malesuada.</p>
							',
								2 => '
								<p class="mb-0">Integer accumsan maximus leo, et consectetur metus vestibulum in.
									Vestibulum
									viverra justo odio purus a libero luctus. Proin tempor dolor ac dolor feugiat,
									placerat
									malesuada.</p>
							',
								3 => '
								<p class="mb-0">Integer accumsan maximus leo, et consectetur metus vestibulum in.
									Vestibulum
									viverra justo odio purus a libero luctus. Proin tempor dolor ac dolor feugiat,
									placerat
									malesuada.</p>
							',
							),
						'.landing-block-node-card-icon' =>
							array (
								0 =>
									array (
										'classList' =>
											array (
												0 => 'landing-block-node-card-icon fa fa-microphone',
											),
										'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
									),
								1 =>
									array (
										'classList' =>
											array (
												0 => 'landing-block-node-card-icon fa fa-book',
											),
										'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
									),
								2 =>
									array (
										'classList' =>
											array (
												0 => 'landing-block-node-card-icon fa fa-bank',
											),
										'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
									),
								3 =>
									array (
										'classList' =>
											array (
												0 => 'landing-block-node-card-icon fa fa-handshake-o',
											),
										'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
									),
							),
						'.landing-block-node-leftblock-img' =>
							array (
								0 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1200x811/img2.png',
										'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/2400x1622/img1.png',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '1012',
										'id2x' => '1013',
									),
							),
						'.landing-block-node-leftblock-subtitle' =>
							array (
								0 => '<span bxstyle="font-weight: normal;">
								OUR CUSTOMERS </span><p><span bxstyle="font-weight: normal;">RECEIVE UP TO 
							</span></p>',
							),
						'.landing-block-node-leftblock-title' =>
							array (
								0 => '$8 789 576',
							),
						'.landing-block-node-leftblock-text' =>
							array (
								0 => '<p>PER YEAR</p>',
							),
						'.landing-block-node-leftblock-button' =>
							array (
								0 =>
									array (
										'href' => '#block207',
										'target' => '_self',
										'attrs' =>
											array (
												'data-embed' => NULL,
												'data-url' => NULL,
											),
										'text' => 'Sign up',
									),
							),
						'.landing-block-node-label-left' =>
							array (
								0 => '<br />',
							),
						'.landing-block-node-label-right' =>
							array (
								0 => '       ',
							),
					),
				'style' =>
					array (
						'.landing-block-node-subtitle' =>
							array (
								0 => 'landing-block-node-subtitle text-uppercase g-font-weight-700 mb-0 g-font-montserrat g-font-size-30 g-color-black',
							),
						'.landing-block-node-title' =>
							array (
								0 => 'landing-block-node-title display-5 text-uppercase g-font-weight-700 g-mb-30 g-font-montserrat g-font-size-50 g-color-blue',
							),
						'.landing-block-node-text' =>
							array (
								0 => 'landing-block-node-text g-font-montserrat g-font-size-18 g-color-gray-dark-v3',
							),
						'.landing-block-node-card-title' =>
							array (
								0 => 'landing-block-node-card-title h6 text-uppercase g-font-weight-700 mb-0 g-font-montserrat g-font-size-20 g-color-gray-dark-v2',
								1 => 'landing-block-node-card-title h6 text-uppercase g-font-weight-700 mb-0 g-font-montserrat g-font-size-20 g-color-black',
								2 => 'landing-block-node-card-title h6 text-uppercase g-font-weight-700 mb-0 g-font-montserrat g-font-size-20 g-color-black',
								3 => 'landing-block-node-card-title h6 text-uppercase g-font-weight-700 mb-0 g-font-montserrat g-font-size-20 g-color-black',
							),
						'.landing-block-node-card-text' =>
							array (
								0 => 'landing-block-node-card-text g-font-montserrat g-font-size-14 g-color-gray-dark-v4',
								1 => 'landing-block-node-card-text g-font-montserrat g-font-size-14 g-color-gray-dark-v4',
								2 => 'landing-block-node-card-text g-font-montserrat g-font-size-14 g-color-gray-dark-v4',
								3 => 'landing-block-node-card-text g-font-montserrat g-font-size-14 g-color-gray-dark-v4',
							),
						'.landing-block-node-leftblock-subtitle' =>
							array (
								0 => 'landing-block-node-leftblock-subtitle h6 text-uppercase g-font-weight-700 g-font-montserrat g-color-gray-dark-v3 g-font-size-20 g-mb-auto',
							),
						'.landing-block-node-leftblock-title' =>
							array (
								0 => 'landing-block-node-leftblock-title d-block g-color-black g-line-height-1_2 g-letter-spacing-minus-2 g-font-size-46 g-font-montserrat g-mb-auto',
							),
						'.landing-block-node-leftblock-text' =>
							array (
								0 => 'landing-block-node-leftblock-text g-color-black-opacity-0_5 g-mb-35 g-font-montserrat g-font-size-20',
							),
						'.landing-block-node-leftblock-button' =>
							array (
								0 => 'landing-block-node-leftblock-button g-valign-middle btn btn-block text-uppercase g-font-size-11 g-font-weight-700 g-brd-none g-px-25 g-py-16 g-rounded-3 u-btn-twitter g-brd-12 g-font-montserrat',
							),
						'.landing-block-node-label-left' =>
							array (
								0 => 'landing-block-node-label-left',
							),
						'.landing-block-node-label-right' =>
							array (
								0 => 'landing-block-node-label-right float-right',
							),
						'.landing-block-node-leftblock' =>
							array (
								0 => 'landing-block-node-leftblock js-animation fadeInLeft g-bg-transparent g-box-shadow-none',
							),
						'.landing-block-node-rightblock' =>
							array (
								0 => 'landing-block-node-rightblock js-animation fadeInRight col-md-6 col-lg-8 g-py-20',
							),
						'.landing-block-node-card-icon-container' =>
							array (
								0 => 'landing-block-node-card-icon-container d-block g-color-primary g-font-size-38',
								1 => 'landing-block-node-card-icon-container d-block g-color-primary g-font-size-38',
								2 => 'landing-block-node-card-icon-container d-block g-color-primary g-font-size-38',
								3 => 'landing-block-node-card-icon-container d-block g-color-primary g-font-size-38',
							),
						'.landing-block-node-bgimg' =>
							array (
								0 => 'landing-block landing-block-node-bgimg u-bg-overlay g-bg-img-hero g-bg-attachment-fixed g-bg-none--after g-pb-auto g-pt-150',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block landing-block-node-bgimg u-bg-overlay g-bg-img-hero g-bg-attachment-fixed g-bg-none--after g-pb-auto g-pt-150',
							),
					),
			],
		'#block4008' => [
				'old_id' => 216,
				'code' => '31.5.two_cols_img_and_title_text_button',
				'access' => 'X',
				'cards' =>
					array (
						'.landing-block-card' =>
							array (
								'source' =>
									array (
										0 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
									),
							),
					),
				'nodes' =>
					array (
						'.landing-block-node-subtitle' =>
							array (
								0 => '<span bxstyle="font-weight: normal;">
					Alex Braun  —  5 May 2020
				</span>',
							),
						'.landing-block-node-title' =>
							array (
								0 => 'Exclusive <span bxstyle="font-weight: normal;">interview
for</span> <span bxstyle="color: rgb(3, 169, 244);">Esquire
				</span>',
							),
						'.landing-block-node-text' =>
							array (
								0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio. </p><p> In rutrum tellus vitae blandit lacinia. Phasellus eget sapien odio. Phasellus eget sapien odio. Vivamus at risus quis leo tincidunt scelerisque non et erat.<br /></p>',
							),
						'.landing-block-node-link' =>
							array (
								0 =>
									array (
										'href' => '#',
										'target' => '_self',
										'attrs' =>
											array (
												'data-embed' => NULL,
												'data-url' => NULL,
											),
										'text' => 'Read more',
									),
							),
						'.landing-block-node-img' =>
							array (
								0 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1080x810/img1.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '940',
										'id2x' => '941',
									),
							),
					),
				'style' =>
					array (
						'.landing-block-card' =>
							array (
								0 => 'landing-block-card row no-gutters align-items-center g-mb-30 g-mb-0--last landing-card',
							),
						'.landing-block-node-img' =>
							array (
								0 => 'landing-block-node-img img-fluid w-100 js-animation fadeInLeft',
							),
						'.landing-block-node-col-text' =>
							array (
								0 => 'landing-block-node-col-text col-md-6 g-py-30 g-px-15 g-px-30--md g-px-45--lg js-animation fadeInRight',
							),
						'.landing-block-node-subtitle' =>
							array (
								0 => 'landing-block-node-subtitle g-font-weight-600 g-font-montserrat g-font-size-14 g-color-gray-dark-v5',
							),
						'.landing-block-node-title' =>
							array (
								0 => 'landing-block-node-title h3 g-color-black g-font-weight-600 mb-4 g-font-montserrat g-font-size-42',
							),
						'.landing-block-node-text' =>
							array (
								0 => 'landing-block-node-text g-font-montserrat g-font-size-14 g-color-gray-dark-v2',
							),
						'.landing-block-node-link' =>
							array (
								0 => 'landing-block-node-link g-color-gray-dark-v2 g-color-primary--hover g-font-weight-600 g-text-underline--none--hover text-uppercase g-font-montserrat g-font-size-20',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block g-pt-50 g-pb-50',
							),
					),
			],
		'#block4009' => [
				'old_id' => 197,
				'code' => '51.4.countdown_music',
				'access' => 'X',
				'cards' =>
					array (
						'.landing-block-node-card' =>
							array (
								'source' =>
									array (
										0 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
									),
							),
					),
				'nodes' =>
					array (
						'.landing-block-node-img' =>
							array (
								0 =>
									array (
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x720/img1.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '818',
										'id2x' => '819',
									),
							),
						'.landing-block-node-title' =>
							array (
								0 => '<span bxstyle="font-weight: normal;">YOU HAVE A</span> CHANCE TO TAKE COURSE',
							),
						'.landing-block-node-text-title' =>
							array (
								0 => 'FOR THE BEST LECTURERS IN THE WORLDS',
							),
						'.landing-block-node-text' =>
							array (
								0 => 'Karl-Kleppe-Strabe 20, 40474 Dusseldorf, Germany',
							),
						'.landing-block-node-number-text-days' =>
							array (
								0 => 'Days',
							),
						'.landing-block-node-number-text-hours' =>
							array (
								0 => 'Hours',
							),
						'.landing-block-node-number-text-minutes' =>
							array (
								0 => 'Minutes',
							),
						'.landing-block-node-number-text-seconds' =>
							array (
								0 => 'Seconds',
							),
					),
				'style' =>
					array (
						'.landing-block-node-title-container' =>
							array (
								0 => 'landing-block-node-title-container container g-max-width-800 text-center g-mb-35 g-mb-65--sm u-heading-v2-2--bottom g-brd-primary',
							),
						'.landing-block-node-title' =>
							array (
								0 => 'landing-block-node-title text-uppercase g-line-height-1 g-font-weight-700 g-color-white g-mb-0 g-font-montserrat g-font-size-50',
							),
						'.landing-block-node-text-title' =>
							array (
								0 => 'landing-block-node-text-title text-uppercase g-font-weight-700 g-color-white g-mb-5 g-font-montserrat g-font-size-24',
							),
						'.landing-block-node-text' =>
							array (
								0 => 'landing-block-node-text g-color-white g-mb-60 g-mb-0--md g-font-montserrat g-font-size-20',
							),
						'.landing-block-node-number-number' =>
							array (
								0 => 'landing-block-node-number-number g-color-white g-font-size-22 g-font-size-40--sm g-font-weight-700 g-mb-0 g-mb-2--sm g-font-montserrat',
								1 => 'landing-block-node-number-number g-color-white g-font-size-22 g-font-size-40--sm g-font-weight-700 g-mb-0 g-mb-2--sm g-font-montserrat',
								2 => 'landing-block-node-number-number g-color-white g-font-size-22 g-font-size-40--sm g-font-weight-700 g-mb-0 g-mb-2--sm g-font-montserrat',
								3 => 'landing-block-node-number-number g-color-white g-font-size-22 g-font-size-40--sm g-font-weight-700 g-mb-0 g-mb-2--sm g-font-montserrat',
							),
						'.landing-block-node-number-text' =>
							array (
								0 => 'landing-block-node-number-text g-color-white-opacity-0_7 text-uppercase g-font-size-10 g-font-size-12--sm g-font-montserrat landing-block-node-number-text-days',
								1 => 'landing-block-node-number-text g-color-white-opacity-0_7 text-uppercase g-font-size-10 g-font-size-12--sm g-font-montserrat landing-block-node-number-text-hours',
								2 => 'landing-block-node-number-text g-color-white-opacity-0_7 text-uppercase g-font-size-10 g-font-size-12--sm g-font-montserrat landing-block-node-number-text-minutes',
								3 => 'landing-block-node-number-text g-color-white-opacity-0_7 text-uppercase g-font-size-10 g-font-size-12--sm g-font-montserrat landing-block-node-number-text-seconds',
							),
						'.landing-block-node-number' =>
							array (
								0 => 'landing-block-node-number align-top g-bg-white-opacity-0_05 g-brd-around g-brd-white-opacity-0_3 g-px-10 g-pt-5 g-pb-8 g-px-20--sm g-pt-15--sm g-pb-10--sm g-mx-3 g-px-10--sm g-mx-15--lg',
								1 => 'landing-block-node-number align-top g-bg-white-opacity-0_05 g-brd-around g-brd-white-opacity-0_3 g-px-10 g-pt-5 g-pb-8 g-px-20--sm g-pt-15--sm g-pb-10--sm g-mx-3 g-px-10--sm g-mx-15--lg',
								2 => 'landing-block-node-number align-top g-bg-white-opacity-0_05 g-brd-around g-brd-white-opacity-0_3 g-px-10 g-pt-5 g-pb-8 g-px-20--sm g-pt-15--sm g-pb-10--sm g-mx-3 g-px-10--sm g-mx-15--lg',
								3 => 'landing-block-node-number align-top g-bg-white-opacity-0_05 g-brd-around g-brd-white-opacity-0_3 g-px-10 g-pt-5 g-pb-8 g-px-20--sm g-pt-15--sm g-pb-10--sm g-mx-3 g-px-10--sm g-mx-15--lg',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block landing-block-node-img u-bg-overlay g-flex-centered g-bg-attachment-fixed g-bg-img-hero g-bg-black-opacity-0_3--after g-pt-135 g-pb-auto g-bg-size-cover l-d-xs-none',
							),
					),
				'attrs' =>
					array (
						'.landing-block-node-card' =>
							array (
								0 =>
									array (
										'data-end-date' => '1618214580000',
									),
							),
					),
			],
		'#block4010' => [
				'old_id' => 198,
				'code' => '48.slider_with_video_on_bgimg',
				'access' => 'X',
				'cards' =>
					array (
						'.landing-block-node-card' =>
							array (
								'source' =>
									array (
										0 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
									),
							),
					),
				'nodes' =>
					array (
						'.landing-block-node-bgimg' =>
							array (
								0 =>
									array (
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x720/img1.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '792',
										'id2x' => '793',
									),
							),
						'.landing-block-node-card-button' =>
							array (
								0 =>
									array (
										'href' => '//www.youtube.com/watch?v=q4d8g9Dn3ww',
										'target' => '_popup',
										'attrs' =>
											array (
												'data-embed' => NULL,
												'data-url' => '//www.youtube.com/embed/q4d8g9Dn3ww?autoplay=1&controls=1&loop=0&mute=0&rel=0&start=0&html5=1&v=q4d8g9Dn3ww',
											),
										'text' => '
						<img class="landing-block-node-card-icon d-block g-relative-centered--y mr-auto g-ml-18 g-height-14" src="https://cdn.bitrix24.site/bitrix/images/landing/play.png" />
					',
									),
							),
						'.landing-block-node-card-title' =>
							array (
								0 => 'OFFICIAL VIDEO FROM OUR LAST EVENT',
							),
						'.landing-block-node-card-text' =>
							array (
								0 => '<p>
						The largest event dedicated to business
					</p>',
							),
						'.landing-block-node-card-link' =>
							array (
								0 =>
									array (
										'href' => '#',
										'target' => '_self',
										'attrs' =>
											array (
												'data-embed' => NULL,
												'data-url' => NULL,
											),
										'text' => 'View on iTunes',
									),
							),
					),
				'style' =>
					array (
						'.landing-block-node-card-title' =>
							array (
								0 => 'landing-block-node-card-title text-uppercase g-font-weight-700 g-color-white g-mb-20 g-font-montserrat g-font-size-24',
							),
						'.landing-block-node-card-text' =>
							array (
								0 => 'landing-block-node-card-text g-font-montserrat g-font-size-20 g-line-height-1_5 g-color-white g-mb-auto',
							),
						'.landing-block-node-card-link' =>
							array (
								0 => 'landing-block-node-card-link text-uppercase g-font-weight-700 g-font-size-11 g-color-primary g-font-montserrat',
							),
						'.landing-block-node-card-button' =>
							array (
								0 => 'landing-block-node-card-button u-icon-v2 g-text-underline--none--hover
					u-block-hover--scale g-overflow-inherit g-bg-primary--hover rounded-circle
					g-cursor-pointer g-brd-around g-brd-5 g-brd-primary mb-3',
							),
						'.landing-block-node-card-button-container' =>
							array (
								0 => 'landing-block-node-card-button-container',
							),
						'.landing-block-node-card' =>
							array (
								0 => 'landing-block-node-card js-slide g-py-20 landing-card slick-slide slick-current slick-active',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block js-animation landing-block-node-bgimg u-bg-overlay g-bg-img-hero g-bg-attachment-fixed g-pb-auto g-pt-auto g-bg-black-opacity-0_3--after animation-none animated l-d-xs-none',
							),
					),
			],
		'#block4011' => [
				'old_id' => 224,
				'code' => '51.4.countdown_music',
				'access' => 'X',
				'cards' =>
					array (
						'.landing-block-node-card' =>
							array (
								'source' =>
									array (
										0 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
									),
							),
					),
				'nodes' =>
					array (
						'.landing-block-node-img' =>
							array (
								0 =>
									array (
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1281/img3.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '1014',
										'id2x' => '1015',
									),
							),
						'.landing-block-node-title' =>
							array (
								0 => '<span bxstyle="font-weight: normal;">YOU HAVE A</span> CHANCE TO TAKE COURSE',
							),
						'.landing-block-node-text-title' =>
							array (
								0 => 'FOR THE BEST LECTURERS IN THE WORLDS',
							),
						'.landing-block-node-text' =>
							array (
								0 => 'Karl-Kleppe-Strabe 20, 40474 Dusseldorf, Germany',
							),
						'.landing-block-node-number-text-days' =>
							array (
								0 => 'Days',
							),
						'.landing-block-node-number-text-hours' =>
							array (
								0 => 'Hours',
							),
						'.landing-block-node-number-text-minutes' =>
							array (
								0 => 'Minutes',
							),
						'.landing-block-node-number-text-seconds' =>
							array (
								0 => 'Seconds',
							),
					),
				'style' =>
					array (
						'.landing-block-node-title-container' =>
							array (
								0 => 'landing-block-node-title-container container g-max-width-800 text-center g-mb-35 g-mb-65--sm u-heading-v2-2--bottom g-brd-primary',
							),
						'.landing-block-node-title' =>
							array (
								0 => 'landing-block-node-title text-uppercase g-line-height-1 g-font-weight-700 g-color-white g-mb-0 g-font-montserrat g-font-size-50',
							),
						'.landing-block-node-text-title' =>
							array (
								0 => 'landing-block-node-text-title text-uppercase g-font-weight-700 g-color-white g-mb-5 g-font-montserrat g-font-size-24',
							),
						'.landing-block-node-text' =>
							array (
								0 => 'landing-block-node-text g-color-white g-mb-60 g-mb-0--md g-font-montserrat g-font-size-20',
							),
						'.landing-block-node-number-number' =>
							array (
								0 => 'landing-block-node-number-number g-color-white g-font-size-22 g-font-size-40--sm g-font-weight-700 g-mb-0 g-mb-2--sm g-font-montserrat',
								1 => 'landing-block-node-number-number g-color-white g-font-size-22 g-font-size-40--sm g-font-weight-700 g-mb-0 g-mb-2--sm g-font-montserrat',
								2 => 'landing-block-node-number-number g-color-white g-font-size-22 g-font-size-40--sm g-font-weight-700 g-mb-0 g-mb-2--sm g-font-montserrat',
								3 => 'landing-block-node-number-number g-color-white g-font-size-22 g-font-size-40--sm g-font-weight-700 g-mb-0 g-mb-2--sm g-font-montserrat',
							),
						'.landing-block-node-number-text' =>
							array (
								0 => 'landing-block-node-number-text g-color-white-opacity-0_7 text-uppercase g-font-size-10 g-font-size-12--sm g-font-montserrat landing-block-node-number-text-days',
								1 => 'landing-block-node-number-text g-color-white-opacity-0_7 text-uppercase g-font-size-10 g-font-size-12--sm g-font-montserrat landing-block-node-number-text-hours',
								2 => 'landing-block-node-number-text g-color-white-opacity-0_7 text-uppercase g-font-size-10 g-font-size-12--sm g-font-montserrat landing-block-node-number-text-minutes',
								3 => 'landing-block-node-number-text g-color-white-opacity-0_7 text-uppercase g-font-size-10 g-font-size-12--sm g-font-montserrat landing-block-node-number-text-seconds',
							),
						'.landing-block-node-number' =>
							array (
								0 => 'landing-block-node-number align-top g-bg-white-opacity-0_05 g-brd-around g-brd-white-opacity-0_3 g-px-10 g-pt-5 g-pb-8 g-px-20--sm g-pt-15--sm g-pb-10--sm g-mx-3 g-px-10--sm g-mx-15--lg',
								1 => 'landing-block-node-number align-top g-bg-white-opacity-0_05 g-brd-around g-brd-white-opacity-0_3 g-px-10 g-pt-5 g-pb-8 g-px-20--sm g-pt-15--sm g-pb-10--sm g-mx-3 g-px-10--sm g-mx-15--lg',
								2 => 'landing-block-node-number align-top g-bg-white-opacity-0_05 g-brd-around g-brd-white-opacity-0_3 g-px-10 g-pt-5 g-pb-8 g-px-20--sm g-pt-15--sm g-pb-10--sm g-mx-3 g-px-10--sm g-mx-15--lg',
								3 => 'landing-block-node-number align-top g-bg-white-opacity-0_05 g-brd-around g-brd-white-opacity-0_3 g-px-10 g-pt-5 g-pb-8 g-px-20--sm g-pt-15--sm g-pb-10--sm g-mx-3 g-px-10--sm g-mx-15--lg',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block landing-block-node-img u-bg-overlay g-flex-centered g-bg-img-hero g-pt-135 g-pb-auto l-d-lg-none l-d-md-none g-bg-size-cover g-bg-attachment-scroll g-bg-black-opacity-0_5--after',
							),
					),
				'attrs' =>
					array (
						'.landing-block-node-card' =>
							array (
								0 =>
									array (
										'data-end-date' => '1618214580000',
									),
							),
					),
			],
		'#block4012' => [
				'old_id' => 225,
				'code' => '48.slider_with_video_on_bgimg',
				'access' => 'X',
				'cards' =>
					array (
						'.landing-block-node-card' =>
							array (
								'source' =>
									array (
										0 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
									),
							),
					),
				'nodes' =>
					array (
						'.landing-block-node-bgimg' =>
							array (
								0 =>
									array (
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1281/img3.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '1016',
										'id2x' => '1017',
									),
							),
						'.landing-block-node-card-button' =>
							array (
								0 =>
									array (
										'href' => '//www.youtube.com/watch?v=q4d8g9Dn3ww',
										'target' => '_popup',
										'attrs' =>
											array (
												'data-embed' => NULL,
												'data-url' => '//www.youtube.com/embed/q4d8g9Dn3ww?autoplay=1&controls=1&loop=0&mute=0&rel=0&start=0&html5=1&v=q4d8g9Dn3ww',
											),
										'text' => '
						<img class="landing-block-node-card-icon d-block g-relative-centered--y mr-auto g-ml-18 g-height-14" src="https://cdn.bitrix24.site/bitrix/images/landing/play.png" />
					',
									),
							),
						'.landing-block-node-card-title' =>
							array (
								0 => 'OFFICIAL VIDEO FROM OUR LAST EVENT',
							),
						'.landing-block-node-card-text' =>
							array (
								0 => '<p>
						The largest event dedicated to business
					</p>',
							),
						'.landing-block-node-card-link' =>
							array (
								0 =>
									array (
										'href' => '#',
										'target' => '_self',
										'attrs' =>
											array (
												'data-embed' => NULL,
												'data-url' => NULL,
											),
										'text' => 'View on iTunes',
									),
							),
					),
				'style' =>
					array (
						'.landing-block-node-card-title' =>
							array (
								0 => 'landing-block-node-card-title text-uppercase g-font-weight-700 g-color-white g-mb-20 g-font-montserrat g-font-size-24',
							),
						'.landing-block-node-card-text' =>
							array (
								0 => 'landing-block-node-card-text g-font-montserrat g-font-size-20 g-line-height-1_5 g-color-white g-mb-auto',
							),
						'.landing-block-node-card-link' =>
							array (
								0 => 'landing-block-node-card-link text-uppercase g-font-weight-700 g-font-size-11 g-color-primary g-font-montserrat',
							),
						'.landing-block-node-card-button' =>
							array (
								0 => 'landing-block-node-card-button u-icon-v2 g-text-underline--none--hover
					u-block-hover--scale g-overflow-inherit g-bg-primary--hover rounded-circle
					g-cursor-pointer g-brd-around g-brd-5 g-brd-primary mb-3',
							),
						'.landing-block-node-card-button-container' =>
							array (
								0 => 'landing-block-node-card-button-container',
							),
						'.landing-block-node-card' =>
							array (
								0 => 'landing-block-node-card js-slide g-py-20 landing-card slick-slide slick-current slick-active',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block js-animation landing-block-node-bgimg u-bg-overlay g-bg-img-hero g-pt-auto animation-none animated l-d-lg-none l-d-md-none g-pb-auto g-bg-attachment-scroll g-bg-size-cover g-bg-black-opacity-0_5--after',
							),
					),
			],
		'#block4013' => [
				'old_id' => 199,
				'code' => '27.one_col_fix_title_and_text_2',
				'access' => 'X',
				'nodes' =>
					array (
						'.landing-block-node-title' =>
							array (
								0 => '<span bxstyle="font-weight: bold;">Participation</span> options',
							),
						'.landing-block-node-text' =>
							array (
								0 => '<p>You can choose a tariff convenient for you</p>',
							),
					),
				'style' =>
					array (
						'.landing-block-node-title' =>
							array (
								0 => 'landing-block-node-title h2 g-font-montserrat g-font-size-50',
							),
						'.landing-block-node-text' =>
							array (
								0 => 'landing-block-node-text g-pb-1 g-font-montserrat g-font-size-20',
							),
						'.landing-block-node-text-container' =>
							array (
								0 => 'landing-block-node-text-container container g-max-width-800',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block js-animation pulse g-pt-50 g-pb-auto l-d-xs-none',
							),
					),
			],
		'#block4014' => [
				'old_id' => 232,
				'code' => '27.one_col_fix_title_and_text_2',
				'access' => 'X',
				'nodes' =>
					array (
						'.landing-block-node-title' =>
							array (
								0 => '<span bxstyle="font-weight: bold;">Participation</span> options',
							),
						'.landing-block-node-text' =>
							array (
								0 => '<p>You can choose a tariff convenient for you</p>',
							),
					),
				'style' =>
					array (
						'.landing-block-node-title' =>
							array (
								0 => 'landing-block-node-title h2 g-font-montserrat g-font-size-48',
							),
						'.landing-block-node-text' =>
							array (
								0 => 'landing-block-node-text g-pb-1 g-font-montserrat g-font-size-15',
							),
						'.landing-block-node-text-container' =>
							array (
								0 => 'landing-block-node-text-container container g-max-width-800',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block js-animation pulse g-pt-50 g-pb-auto l-d-lg-none l-d-md-none',
							),
					),
			],
		'#block4015' => [
				'old_id' => 200,
				'code' => '44.7.three_columns_with_img_and_price',
				'access' => 'X',
				'cards' =>
					array (
						'.landing-block-node-card' =>
							array (
								'source' =>
									array (
										0 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										1 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										2 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
									),
							),
					),
				'nodes' =>
					array (
						'.landing-block-node-card-title' =>
							array (
								0 => '<span bxstyle="font-style: normal;font-weight: normal;">
							Basic</span>',
								1 => '<span bxstyle="font-style: normal;">
							Standart</span>',
								2 => '<span bxstyle="font-style: normal;font-weight: normal;">
							Pro</span>',
							),
						'.landing-block-node-card-subtitle' =>
							array (
								0 => '<br />',
								1 => '<span bxstyle="font-weight: bold;">
							BEST PRICE</span>',
								2 => '<br />',
							),
						'.landing-block-node-card-img' =>
							array (
								0 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/540x361/img1.jpg',
										'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1080x721/img1.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '796',
										'id2x' => '797',
									),
								1 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/540x361/img2.jpg',
										'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1080x721/img2.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '798',
										'id2x' => '799',
									),
								2 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/540x361/img3.jpg',
										'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1080x721/img3.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '801',
										'id2x' => '800',
									),
							),
						'.landing-block-node-card-price-subtitle' =>
							array (
								0 => '<span bxstyle="font-weight: bold;">
								18 places left
							</span>',
								1 => '<span bxstyle="font-weight: bold;">
								7 places left
							</span>',
								2 => '<span bxstyle="font-weight: bold;">
								3 places left
							</span>',
							),
						'.landing-block-node-card-price' =>
							array (
								0 => 'You are getting:',
								1 => 'You are getting:',
								2 => 'You are getting:',
							),
						'.landing-block-node-card-text' =>
							array (
								0 => '<p>
								24 lectures </p><p> Records of all lessons </p><p> for the whole year </p><p> Private chat access </p><p>x<br /></p><p>x<br /></p><p>x<br /></p><p>x<br /></p><p>x<br /></p><p>x</p>',
								1 => '<p>
								24 lectures </p><p> + 2 private online broadcasts </p><p> Records of all lessons </p><p> Private chat access </p><p> Prize draw </p><p> Check homework </p><p> Course certificate </p><p>x<br /></p><p>x<br /></p><p>x</p>',
								2 => '<p>
								24 lectures </p><p>+ 2 private online broadcasts </p><p>Records of all lessons </p><p> <span bxstyle="">Private chat access </span></p><p><span bxstyle=""> Prize draw </span></p><p><span bxstyle=""> Check homework </span></p><p><span bxstyle=""> Course certificate 
</span></p><p><span bxstyle="font-weight: bold;">Personal curator </span></p><p><span bxstyle=""> Curator support after the course</span></p><p><span bxstyle="">Book from business expert Alex Brown</span></p>',
							),
						'.landing-block-node-card-button' =>
							array (
								0 =>
									array (
										'href' => '#block207',
										'target' => '_self',
										'attrs' =>
											array (
												'data-embed' => NULL,
												'data-url' => NULL,
											),
										'text' => 'Buy Now',
									),
								1 =>
									array (
										'href' => '#block207',
										'target' => '_self',
										'attrs' =>
											array (
												'data-embed' => NULL,
												'data-url' => NULL,
											),
										'text' => 'Buy Now',
									),
								2 =>
									array (
										'href' => '#block207',
										'target' => '_self',
										'attrs' =>
											array (
												'data-embed' => NULL,
												'data-url' => NULL,
											),
										'text' => 'Buy Now',
									),
							),
					),
				'style' =>
					array (
						'.landing-block-node-card' =>
							array (
								0 => 'landing-block-node-card js-animation col-md-6 col-lg-4 g-mb-30 landing-card pulse',
								1 => 'landing-block-node-card js-animation col-md-6 col-lg-4 g-mb-30 landing-card pulse',
								2 => 'landing-block-node-card js-animation col-md-6 col-lg-4 g-mb-30 landing-card pulse',
							),
						'.landing-block-node-card-container-top' =>
							array (
								0 => 'landing-block-node-card-container-top g-pa-20 g-bg-secondary',
								1 => 'landing-block-node-card-container-top g-pa-20 g-bg-secondary',
								2 => 'landing-block-node-card-container-top g-pa-20 g-bg-secondary',
							),
						'.landing-block-node-card-container-bottom' =>
							array (
								0 => 'landing-block-node-card-container-bottom flex-grow-1 g-pa-40 d-flex flex-column g-bg-secondary',
								1 => 'landing-block-node-card-container-bottom flex-grow-1 g-pa-40 d-flex flex-column g-bg-secondary',
								2 => 'landing-block-node-card-container-bottom flex-grow-1 g-pa-40 d-flex flex-column g-bg-secondary',
							),
						'.landing-block-node-card-title' =>
							array (
								0 => 'landing-block-node-card-title g-font-size-30 font-italic g-font-weight-700 mb-0 g-font-montserrat g-color-black',
								1 => 'landing-block-node-card-title g-font-size-30 font-italic g-font-weight-700 mb-0 g-font-montserrat g-color-black',
								2 => 'landing-block-node-card-title g-font-size-30 font-italic g-font-weight-700 mb-0 g-font-montserrat g-color-black',
							),
						'.landing-block-node-card-subtitle' =>
							array (
								0 => 'landing-block-node-card-subtitle g-font-size-default g-font-montserrat g-color-black',
								1 => 'landing-block-node-card-subtitle g-font-size-default g-font-montserrat g-color-black',
								2 => 'landing-block-node-card-subtitle g-font-size-default g-font-montserrat g-color-black',
							),
						'.landing-block-node-card-price-subtitle' =>
							array (
								0 => 'landing-block-node-card-price-subtitle g-font-size-default g-font-montserrat g-color-black-opacity-0_9',
								1 => 'landing-block-node-card-price-subtitle g-font-size-default g-font-montserrat g-color-black-opacity-0_9',
								2 => 'landing-block-node-card-price-subtitle g-font-size-default g-font-montserrat g-color-black-opacity-0_9',
							),
						'.landing-block-node-card-price' =>
							array (
								0 => 'landing-block-node-card-price g-font-weight-700 g-font-size-24 g-mt-10 g-font-montserrat g-color-blue',
								1 => 'landing-block-node-card-price g-font-weight-700 g-font-size-24 g-mt-10 g-font-montserrat g-color-blue',
								2 => 'landing-block-node-card-price g-font-weight-700 g-font-size-24 g-mt-10 g-font-montserrat g-color-blue',
							),
						'.landing-block-node-card-text' =>
							array (
								0 => 'landing-block-node-card-text g-font-size-default g-font-montserrat g-color-black-opacity-0_9 g-font-size-0 g-line-height-1_3 g-letter-spacing-0_5 g-mb-10',
								1 => 'landing-block-node-card-text g-font-size-default g-font-montserrat g-color-black-opacity-0_9 g-font-size-0 g-line-height-1_3 g-letter-spacing-0_5 g-mb-10',
								2 => 'landing-block-node-card-text g-font-size-default g-font-montserrat g-color-black-opacity-0_9 g-font-size-0 g-line-height-1_3 g-letter-spacing-0_5 g-mb-10',
							),
						'.landing-block-node-card-button' =>
							array (
								0 => 'landing-block-node-card-button btn text-uppercase g-font-weight-700 g-font-size-12 g-py-15 g-rounded-3 g-font-montserrat u-btn-twitter g-brd-5',
								1 => 'landing-block-node-card-button btn text-uppercase g-font-weight-700 g-font-size-12 g-py-15 g-rounded-3 g-font-montserrat u-btn-twitter g-brd-5',
								2 => 'landing-block-node-card-button btn text-uppercase g-font-weight-700 g-font-size-12 g-py-15 g-rounded-3 g-font-montserrat u-btn-twitter g-brd-5',
							),
						'.landing-block-node-card-button-container' =>
							array (
								0 => 'landing-block-node-card-button-container mt-auto',
								1 => 'landing-block-node-card-button-container mt-auto',
								2 => 'landing-block-node-card-button-container mt-auto',
							),
						'.landing-block-inner' =>
							array (
								0 => 'row landing-block-inner',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block g-pb-65 g-pt-25',
							),
					),
			],
		'#block4016' => [
				'old_id' => 202,
				'code' => '27.one_col_fix_title_and_text_2',
				'access' => 'X',
				'nodes' =>
					array (
						'.landing-block-node-title' =>
							array (
								0 => 'Reviews of <span bxstyle="font-weight: bold;">successful </span>graduates',
							),
						'.landing-block-node-text' =>
							array (
								0 => '<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit</p>',
							),
					),
				'style' =>
					array (
						'.landing-block-node-title' =>
							array (
								0 => 'landing-block-node-title h2 g-font-montserrat g-font-size-50',
							),
						'.landing-block-node-text' =>
							array (
								0 => 'landing-block-node-text g-pb-1 g-font-montserrat g-font-size-20 g-color-gray-dark-v4',
							),
						'.landing-block-node-text-container' =>
							array (
								0 => 'landing-block-node-text-container container g-max-width-800',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block js-animation g-bg-secondary g-pb-auto g-pt-50 pulse l-d-xs-none',
							),
					),
			],
		'#block4017' => [
				'old_id' => 234,
				'code' => '27.one_col_fix_title_and_text_2',
				'access' => 'X',
				'nodes' =>
					array (
						'.landing-block-node-title' =>
							array (
								0 => 'Reviews of <span bxstyle="font-weight: bold;">successful </span>graduates',
							),
						'.landing-block-node-text' =>
							array (
								0 => '<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit</p>',
							),
					),
				'style' =>
					array (
						'.landing-block-node-title' =>
							array (
								0 => 'landing-block-node-title h2 g-font-montserrat g-font-size-50',
							),
						'.landing-block-node-text' =>
							array (
								0 => 'landing-block-node-text g-pb-1 g-font-montserrat g-color-gray-dark-v4 g-font-size-15',
							),
						'.landing-block-node-text-container' =>
							array (
								0 => 'landing-block-node-text-container container g-max-width-800',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block js-animation g-bg-secondary g-pb-auto g-pt-50 pulse l-d-lg-none l-d-md-none',
							),
					),
			],
		'#block4018' => [
				'old_id' => 203,
				'code' => '29.three_cols_texts_blocks_slider',
				'access' => 'X',
				'cards' =>
					array (
						'.landing-block-card-slider-element' =>
							array (
								'source' =>
									array (
										0 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										1 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										2 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										3 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										4 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
									),
							),
					),
				'nodes' =>
					array (
						'.landing-block-node-element-title' =>
							array (
								0 => 'Marina Vanga',
								1 => 'Brian Wolf',
								2 => 'Max Shady',
								3 => 'Mark
							Mcmanus',
								4 => 'Zuza
							Muszyska',
							),
						'.landing-block-node-element-subtitle' =>
							array (
								0 => '<span class="d-block"><br /></span>',
								1 => '<span class="d-block"><br /></span>',
								2 => '<span class="d-block"><br /></span>',
								3 => '<span class="d-block"><br /></span>',
								4 => '<span class="d-block"><br /></span>',
							),
						'.landing-block-node-element-text' =>
							array (
								0 => 'The company in 3 months gave me more useful information than I learned in 5 years of study at the university. Even during training, my income grew 3 times. This is an amazing indicator',
								1 => 'I am delighted with this school and recommend it to all my friends. Guys do a great job, devoting youth to the intricacies of doing business',
								2 => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.',
								3 => 'Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto',
								4 => 'Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam eaque ipsa, quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt, explicabo!',
							),
						'.landing-block-node-element-img' =>
							array (
								0 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img1.jpg',
									),
								1 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img5.jpg',
									),
								2 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img2.jpg',
									),
								3 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img4.jpg',
									),
								4 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img3.jpg',
									),
							),
					),
				'style' =>
					array (
						'.landing-block-node-element-text' =>
							array (
								0 => 'landing-block-node-element-text u-blockquote-v8 g-font-weight-300 g-font-size-15 rounded g-pa-25 g-mb-25 g-font-montserrat',
								1 => 'landing-block-node-element-text u-blockquote-v8 g-font-weight-300 g-font-size-15 rounded g-pa-25 g-mb-25 g-font-montserrat',
								2 => 'landing-block-node-element-text u-blockquote-v8 g-font-weight-300 g-font-size-15 rounded g-pa-25 g-mb-25 g-font-montserrat',
								3 => 'landing-block-node-element-text u-blockquote-v8 g-font-weight-300 g-font-size-15 rounded g-pa-25 g-mb-25 g-font-montserrat',
								4 => 'landing-block-node-element-text u-blockquote-v8 g-font-weight-300 g-font-size-15 rounded g-pa-25 g-mb-25 g-font-montserrat',
							),
						'.landing-block-node-element-title' =>
							array (
								0 => 'landing-block-node-element-title g-font-weight-400 g-font-size-15 g-color-main g-mb-0 g-font-montserrat',
								1 => 'landing-block-node-element-title g-font-weight-400 g-font-size-15 g-color-main g-mb-0 g-font-montserrat',
								2 => 'landing-block-node-element-title g-font-weight-400 g-font-size-15 g-color-main g-mb-0 g-font-montserrat',
								3 => 'landing-block-node-element-title g-font-weight-400 g-font-size-15 g-color-main g-mb-0 g-font-montserrat',
								4 => 'landing-block-node-element-title g-font-weight-400 g-font-size-15 g-color-main g-mb-0 g-font-montserrat',
							),
						'.landing-block-node-element-subtitle' =>
							array (
								0 => 'landing-block-node-element-subtitle g-color-main g-font-size-13 g-font-montserrat',
								1 => 'landing-block-node-element-subtitle g-color-main g-font-size-13 g-font-montserrat',
								2 => 'landing-block-node-element-subtitle g-color-main g-font-size-13 g-font-montserrat',
								3 => 'landing-block-node-element-subtitle g-color-main g-font-size-13 g-font-montserrat',
								4 => 'landing-block-node-element-subtitle g-color-main g-font-size-13 g-font-montserrat',
							),
						'.landing-block-node-element-img' =>
							array (
								0 => 'landing-block-node-element-img g-width-50 g-height-50 g-object-fit-cover d-flex align-self-center g-rounded-50x u-shadow-v19 g-brd-around g-brd-3 g-brd-white mr-3',
								1 => 'landing-block-node-element-img g-width-50 g-height-50 g-object-fit-cover d-flex align-self-center g-rounded-50x u-shadow-v19 g-brd-around g-brd-3 g-brd-white mr-3',
								2 => 'landing-block-node-element-img g-width-50 g-height-50 g-object-fit-cover d-flex align-self-center g-rounded-50x u-shadow-v19 g-brd-around g-brd-3 g-brd-white mr-3',
								3 => 'landing-block-node-element-img g-width-50 g-height-50 g-object-fit-cover d-flex align-self-center g-rounded-50x u-shadow-v19 g-brd-around g-brd-3 g-brd-white mr-3',
								4 => 'landing-block-node-element-img g-width-50 g-height-50 g-object-fit-cover d-flex align-self-center g-rounded-50x u-shadow-v19 g-brd-around g-brd-3 g-brd-white mr-3',
							),
						'.landing-block-card-slider-element' =>
							array (
								0 => 'landing-block-card-slider-element js-slide align-self-center g-px-15 mb-1 slick-slide landing-card',
								1 => 'landing-block-card-slider-element js-slide align-self-center g-px-15 mb-1 slick-slide landing-card',
								2 => 'landing-block-card-slider-element js-slide align-self-center g-px-15 mb-1 slick-slide landing-card',
								3 => 'landing-block-card-slider-element js-slide align-self-center g-px-15 mb-1 slick-slide landing-card',
								4 => 'landing-block-card-slider-element js-slide align-self-center g-px-15 mb-1 slick-slide landing-card',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block js-animation fadeIn g-bg-secondary',
							),
					),
			],
		'#block4019' => [
				'old_id' => 204,
				'code' => '27.one_col_fix_title_and_text_2',
				'access' => 'X',
				'nodes' =>
					array (
						'.landing-block-node-title' =>
							array (
								0 => 'Our <span bxstyle="font-weight: bold;">partners</span>',
							),
						'.landing-block-node-text' =>
							array (
								0 => '<p><br /></p>',
							),
					),
				'style' =>
					array (
						'.landing-block-node-title' =>
							array (
								0 => 'landing-block-node-title h2 g-font-montserrat g-font-size-50',
							),
						'.landing-block-node-text' =>
							array (
								0 => 'landing-block-node-text g-pb-1 g-font-montserrat g-font-size-5',
							),
						'.landing-block-node-text-container' =>
							array (
								0 => 'landing-block-node-text-container container g-max-width-800',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block js-animation g-bg-secondary g-pt-auto g-pb-auto pulse',
							),
					),
			],
		'#block4020' => [
				'old_id' => 205,
				'code' => '24.3.image_gallery_6_cols_fix_3',
				'access' => 'X',
				'cards' =>
					array (
						'.landing-block-node-card' =>
							array (
								'source' =>
									array (
										0 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										1 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										2 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										3 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										4 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										5 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
									),
							),
					),
				'nodes' =>
					array (
						'.landing-block-node-img' =>
							array (
								0 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img1.png',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
									),
								1 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img2.png',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
									),
								2 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img3.png',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
									),
								3 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img4.png',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
									),
								4 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img1.png',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
									),
								5 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img6.png',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
									),
							),
						'.landing-block-card-logo-link' =>
							array (
								0 =>
									array (
										'href' => '#',
										'target' => '_self',
										'attrs' =>
											array (
												'data-embed' => NULL,
												'data-url' => NULL,
											),
										'text' => '
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img1.png" alt="" data-pseudo-url="{"text":"","href":"","target":"_self","enabled":false}" />
				',
									),
								1 =>
									array (
										'href' => '#',
										'target' => '_self',
										'attrs' =>
											array (
												'data-embed' => NULL,
												'data-url' => NULL,
											),
										'text' => '
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img2.png" alt="" data-pseudo-url="{"text":"","href":"","target":"_self","enabled":false}" />
				',
									),
								2 =>
									array (
										'href' => '#',
										'target' => '_self',
										'attrs' =>
											array (
												'data-embed' => NULL,
												'data-url' => NULL,
											),
										'text' => '
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img3.png" alt="" data-pseudo-url="{"text":"","href":"","target":"_self","enabled":false}" />
				',
									),
								3 =>
									array (
										'href' => '#',
										'target' => '_self',
										'attrs' =>
											array (
												'data-embed' => NULL,
												'data-url' => NULL,
											),
										'text' => '
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img4.png" alt="" data-pseudo-url="{"text":"","href":"","target":"_self","enabled":false}" />
				',
									),
								4 =>
									array (
										'href' => '#',
										'target' => '_self',
										'attrs' =>
											array (
												'data-embed' => NULL,
												'data-url' => NULL,
											),
										'text' => '
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img1.png" alt="" data-pseudo-url="{"text":"","href":"","target":"_self","enabled":false}" />
				',
									),
								5 =>
									array (
										'href' => '#',
										'target' => '_self',
										'attrs' =>
											array (
												'data-embed' => NULL,
												'data-url' => NULL,
											),
										'text' => '
					<img class="landing-block-node-img g-width-120" src="https://cdn.bitrix24.site/bitrix/images/landing/business/x74/img6.png" alt="" data-pseudo-url="{"text":"","href":"","target":"_self","enabled":false}" />
				',
									),
							),
					),
				'style' =>
					array (
						'.landing-block-node-card' =>
							array (
								0 => 'landing-block-node-card col-md-4 col-lg-2 d-flex align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50 landing-card',
								1 => 'landing-block-node-card col-md-4 col-lg-2 d-flex align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50 landing-card',
								2 => 'landing-block-node-card col-md-4 col-lg-2 d-flex align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50 landing-card',
								3 => 'landing-block-node-card col-md-4 col-lg-2 d-flex align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50 landing-card',
								4 => 'landing-block-node-card col-md-4 col-lg-2 d-flex align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50 landing-card',
								5 => 'landing-block-node-card col-md-4 col-lg-2 d-flex align-items-center justify-content-center g-brd-bottom g-brd-right g-brd-color-inherit g-py-50 landing-card',
							),
						'.landing-block-node-container' =>
							array (
								0 => 'landing-block-node-container container g-brd-gray-light-v4',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block js-animation text-center g-bg-secondary g-pt-auto g-pb-55 animation-none',
							),
					),
			],
		'#block4021' => [
				'old_id' => 193,
				'code' => '41.3.announcement_with_slider',
				'access' => 'X',
				'cards' =>
					array (
						'.landing-block-node-card' =>
							array (
								'source' =>
									array (
										0 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										1 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										2 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										3 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										4 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
									),
							),
					),
				'nodes' =>
					array (
						'.landing-block-node-bgimg' =>
							array (
								0 =>
									array (
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1039/img1.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '942',
										'id2x' => '943',
									),
							),
						'.landing-block-node-title' =>
							array (
								0 => 'BUSINESS <span bxstyle="font-weight: normal;">PLANNING</span>',
							),
						'.landing-block-node-date-icon' =>
							array (
								0 =>
									array (
										'classList' =>
											array (
												0 => 'landing-block-node-date-icon fa fa-calendar',
											),
									),
							),
						'.landing-block-node-date-title' =>
							array (
								0 => '<span bxstyle="font-weight: bold;">When</span>',
							),
						'.landing-block-node-date-text' =>
							array (
								0 => '19:30, 13 JUN, 2020',
							),
						'.landing-block-node-place-icon' =>
							array (
								0 =>
									array (
										'classList' =>
											array (
												0 => 'landing-block-node-place-icon fa fa-map-marker',
											),
									),
							),
						'.landing-block-node-place-title' =>
							array (
								0 => '<span bxstyle="font-weight: bold;">Where</span>',
							),
						'.landing-block-node-place-text' =>
							array (
								0 => 'TOULOUSER ALLEE 5, 40211 DUSSELDORF, GERMANY',
							),
						'.landing-block-node-button' =>
							array (
								0 =>
									array (
										'href' => '#block207',
										'target' => '_self',
										'attrs' =>
											array (
												'data-embed' => NULL,
												'data-url' => NULL,
											),
										'text' => 'Register Now',
									),
							),
						'.landing-block-node-card-img' =>
							array (
								0 =>
									array (
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1000x1000/img9.jpg',
										'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/2000x2000/img1.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '962',
										'id2x' => '963',
									),
								1 =>
									array (
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1000x1000/img10.jpg',
										'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/2000x2000/img2.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '964',
										'id2x' => '965',
									),
								2 =>
									array (
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1000x1000/img11.jpg',
										'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/2000x2000/img3.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '966',
										'id2x' => '967',
									),
								3 =>
									array (
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1000x1000/img12.jpg',
										'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/2000x2000/img4.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '969',
										'id2x' => '968',
									),
								4 =>
									array (
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1000x1000/img13.jpg',
										'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/2000x2000/img5.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '971',
										'id2x' => '970',
									),
							),
						'.landing-block-node-block-title' =>
							array (
								0 => 'ABOUT BUSINESS <p><span bxstyle="font-weight: normal;">IN PLAIN LANGUAGE</span></p>',
							),
						'.landing-block-node-block-subtitle' =>
							array (
								0 => 'Fusce pretium
								augue quis sem consectetur',
							),
						'.landing-block-node-block-text' =>
							array (
								0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in
									blandit dolor. Vivamus a fringilla lorem, vel faucibus ante.</p>
								<p>Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros,
									fringilla porttitor lorem eros vel odio. In rutrum tellus vitae blandit lacinia.
									Phasellus
									eget
									sapien odio. Phasellus eget sapien odio. Vivamus at risus quis leo tincidunt. </p>',
							),
					),
				'style' =>
					array (
						'.landing-block-node-title' =>
							array (
								0 => 'landing-block-node-title text-center text-uppercase h2 g-font-weight-700 g-color-white g-mb-30 g-mb-70--md g-font-montserrat g-font-size-50',
							),
						'.landing-block-node-date-title' =>
							array (
								0 => 'landing-block-node-date-title g-mb-5 g-font-size-14 g-color-white-opacity-0_5 g-font-montserrat',
							),
						'.landing-block-node-date-text' =>
							array (
								0 => 'landing-block-node-date-text h3 text-uppercase g-font-size-15 g-color-white mb-0 g-font-montserrat',
							),
						'.landing-block-node-place-title' =>
							array (
								0 => 'landing-block-node-place-title g-mb-5 g-font-size-14 g-color-white-opacity-0_5 g-font-montserrat',
							),
						'.landing-block-node-place-text' =>
							array (
								0 => 'landing-block-node-place-text h3 text-uppercase g-font-size-15 g-color-white mb-0 g-font-montserrat',
							),
						'.landing-block-node-button' =>
							array (
								0 => 'landing-block-node-button btn btn-lg text-uppercase g-font-weight-700 g-font-size-11 g-color-white--hover g-bg-primary--hover g-brd-none g-py-18 g-rounded-3 g-font-montserrat u-btn-twitter',
							),
						'.landing-block-node-block-title' =>
							array (
								0 => 'landing-block-node-block-title text-uppercase g-font-weight-700 g-font-size-30 g-mb-10 g-font-montserrat g-color-blue',
							),
						'.landing-block-node-block-subtitle' =>
							array (
								0 => 'landing-block-node-block-subtitle text-uppercase g-font-weight-500 g-color-white g-font-montserrat g-font-size-14 g-mb-20',
							),
						'.landing-block-node-block-text' =>
							array (
								0 => 'landing-block-node-block-text g-font-size-14 mb-0 g-font-montserrat g-color-gray-dark-v5',
							),
						'.landing-block-node-bgimg' =>
							array (
								0 => 'landing-block-node-bgimg g-pt-150 g-bg-img-hero g-pos-rel u-bg-overlay g-bg-black-opacity-0_6--after',
							),
						'.landing-block-node-date-icon-container' =>
							array (
								0 => 'landing-block-node-date-icon-container media-left d-flex align-self-center g-mr-20 g-color-white-opacity-0_5 g-font-size-27',
							),
						'.landing-block-node-place-icon-container' =>
							array (
								0 => 'landing-block-node-place-icon-container media-left d-flex align-self-center g-mr-20 g-color-white-opacity-0_5 g-font-size-27',
							),
						'.landing-block-node-container' =>
							array (
								0 => 'landing-block-node-container js-animation fadeInUp container g-max-width-750 u-bg-overlay__inner g-mb-60',
							),
						'.landing-block-node-inner-block' =>
							array (
								0 => 'landing-block-node-inner-block col-md-6 col-lg-6 d-flex g-min-height-50vh g-theme-event-color-gray-dark-v1 g-py-80 g-py-20--md g-px-50',
							),
						'.landing-block-node-card-img' =>
							array (
								0 => 'landing-block-node-card landing-block-node-card-img js-slide g-bg-img-hero g-min-height-50vh landing-card slick-slide',
								1 => 'landing-block-node-card landing-block-node-card-img js-slide g-bg-img-hero g-min-height-50vh landing-card slick-slide',
								2 => 'landing-block-node-card landing-block-node-card-img js-slide g-bg-img-hero g-min-height-50vh landing-card slick-slide',
								3 => 'landing-block-node-card landing-block-node-card-img js-slide g-bg-img-hero g-min-height-50vh landing-card slick-slide',
								4 => 'landing-block-node-card landing-block-node-card-img js-slide g-bg-img-hero g-min-height-50vh landing-card slick-slide',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block',
							),
					),
			],
		'#block4022' => [
				'old_id' => 206,
				'code' => '44.5.three_cols_images_with_price',
				'access' => 'X',
				'cards' =>
					array (
						'.landing-block-node-card' =>
							array (
								'source' =>
									array (
										0 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										1 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										2 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										3 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										4 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
										5 =>
											array (
												'value' => 0,
												'type' => 'card',
											),
									),
							),
					),
				'nodes' =>
					array (
						'.landing-block-node-card-price' =>
							array (
								0 => 'From <span bxstyle="color: rgb(3, 169, 244);">15.02.2020</span>',
								1 => 'From <span bxstyle="color: rgb(3, 169, 244);">27.01.2020</span>',
								2 => 'From <span bxstyle="color: rgb(3, 169, 244);">12.01.2020</span>',
								3 => 'From <span bxstyle="color: rgb(3, 169, 244);">20.12.2019</span>',
								4 => 'From <span bxstyle="color: rgb(3, 169, 244);">07.12.2019</span>',
								5 => 'From <span bxstyle="color: rgb(3, 169, 244);">30.11.2019</span>',
							),
						'.landing-block-node-card-subtitle' =>
							array (
								0 => 'MARK SMIDT',
								1 => 'KARL RED',
								2 => 'MARTIN FOUL',
								3 => 'PATRIK SCOTT',
								4 => 'BARBARA VALLA',
								5 => 'SAYMON PRATT',
							),
						'.landing-block-node-card-title' =>
							array (
								0 => 'BUSINESS PROMOTION',
								1 => 'FUNDING - SAVING - BENEFIT',
								2 => 'MARKETING STRATEGY',
								3 => 'PROCESS MODERNIZATION',
								4 => 'ANALYTICS',
								5 => 'REORGANIZATION OF BUSINESS UNITS',
							),
						'.landing-block-node-card-text' =>
							array (
								0 => '3 458 PERSONS, HOTEL PARK INN',
								1 => 'RADISSON BLU SCANDINAVIA HOTEL',
								2 => 'DAS CARLS HOTEL',
								3 => 'INTERCONTINENTAL, DUSSELDORF',
								4 => '2 367 PERSONS, GRAND HOTEL',
								5 => '5 487 PERSONS, 2 DAYS, GREAT SHOW',
							),
						'.landing-block-node-card-bgimg' =>
							array (
								0 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1000x667/img10.jpg',
										'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/2000x1334/img1.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '950',
										'id2x' => '951',
									),
								1 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1000x667/img11.jpg',
										'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/2000x1334/img2.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '952',
										'id2x' => '953',
									),
								2 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1000x667/img12.jpg',
										'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/2000x1334/img3.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '954',
										'id2x' => '955',
									),
								3 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1000x667/img13.jpg',
										'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/2000x1334/img4.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '956',
										'id2x' => '957',
									),
								4 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1000x667/img14.jpg',
										'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/2000x1334/img5.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '958',
										'id2x' => '959',
									),
								5 =>
									array (
										'alt' => '',
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1000x667/img15.jpg',
										'src2x' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/2000x1334/img6.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '960',
										'id2x' => '961',
									),
							),
					),
				'style' =>
					array (
						'.landing-block-node-card' =>
							array (
								0 => 'landing-block-node-card js-animation fadeInUp col-lg-4 col-md-6 g-mb-30 landing-card',
								1 => 'landing-block-node-card js-animation fadeInUp col-lg-4 col-md-6 g-mb-30 landing-card',
								2 => 'landing-block-node-card js-animation fadeInUp col-lg-4 col-md-6 g-mb-30 landing-card',
								3 => 'landing-block-node-card js-animation fadeInUp col-lg-4 col-md-6 g-mb-30 landing-card',
								4 => 'landing-block-node-card js-animation fadeInUp col-lg-4 col-md-6 g-mb-30 landing-card',
								5 => 'landing-block-node-card js-animation fadeInUp col-lg-4 col-md-6 g-mb-30 landing-card',
							),
						'.landing-block-node-card-bg-hover' =>
							array (
								0 => 'landing-block-node-card-bg-hover opacity-0 g-opacity-1--parent-hover g-transition-0_2 g-transition--ease-in h-100 g-theme-travel-bg-black-v1-opacity-0_8',
								1 => 'landing-block-node-card-bg-hover opacity-0 g-opacity-1--parent-hover g-transition-0_2 g-transition--ease-in h-100 g-theme-travel-bg-black-v1-opacity-0_8',
								2 => 'landing-block-node-card-bg-hover opacity-0 g-opacity-1--parent-hover g-transition-0_2 g-transition--ease-in h-100 g-theme-travel-bg-black-v1-opacity-0_8',
								3 => 'landing-block-node-card-bg-hover opacity-0 g-opacity-1--parent-hover g-transition-0_2 g-transition--ease-in h-100 g-theme-travel-bg-black-v1-opacity-0_8',
								4 => 'landing-block-node-card-bg-hover opacity-0 g-opacity-1--parent-hover g-transition-0_2 g-transition--ease-in h-100 g-theme-travel-bg-black-v1-opacity-0_8',
								5 => 'landing-block-node-card-bg-hover opacity-0 g-opacity-1--parent-hover g-transition-0_2 g-transition--ease-in h-100 g-theme-travel-bg-black-v1-opacity-0_8',
							),
						'.landing-block-node-card-price' =>
							array (
								0 => 'landing-block-node-card-price g-font-weight-700 u-ribbon-v1 text-uppercase g-top-20 g-left-20 g-color-white g-theme-travel-bg-black-v1 g-pa-5 g-font-montserrat',
								1 => 'landing-block-node-card-price g-font-weight-700 u-ribbon-v1 text-uppercase g-top-20 g-left-20 g-color-white g-theme-travel-bg-black-v1 g-pa-5 g-font-montserrat',
								2 => 'landing-block-node-card-price g-font-weight-700 u-ribbon-v1 text-uppercase g-top-20 g-left-20 g-color-white g-theme-travel-bg-black-v1 g-pa-5 g-font-montserrat',
								3 => 'landing-block-node-card-price g-font-weight-700 u-ribbon-v1 text-uppercase g-top-20 g-left-20 g-color-white g-theme-travel-bg-black-v1 g-pa-5 g-font-montserrat',
								4 => 'landing-block-node-card-price g-font-weight-700 u-ribbon-v1 text-uppercase g-top-20 g-left-20 g-color-white g-theme-travel-bg-black-v1 g-pa-5 g-font-montserrat',
								5 => 'landing-block-node-card-price g-font-weight-700 u-ribbon-v1 text-uppercase g-top-20 g-left-20 g-color-white g-theme-travel-bg-black-v1 g-pa-5 g-font-montserrat',
							),
						'.landing-block-node-card-subtitle' =>
							array (
								0 => 'landing-block-node-card-subtitle g-line-height-1_2 g-font-weight-700 g-font-size-11 g-color-white g-mb-10 g-font-montserrat',
								1 => 'landing-block-node-card-subtitle g-line-height-1_2 g-font-weight-700 g-font-size-11 g-color-white g-mb-10 g-font-montserrat',
								2 => 'landing-block-node-card-subtitle g-line-height-1_2 g-font-weight-700 g-font-size-11 g-color-white g-mb-10 g-font-montserrat',
								3 => 'landing-block-node-card-subtitle g-line-height-1_2 g-font-weight-700 g-font-size-11 g-color-white g-mb-10 g-font-montserrat',
								4 => 'landing-block-node-card-subtitle g-line-height-1_2 g-font-weight-700 g-font-size-11 g-color-white g-mb-10 g-font-montserrat',
								5 => 'landing-block-node-card-subtitle g-line-height-1_2 g-font-weight-700 g-font-size-11 g-color-white g-mb-10 g-font-montserrat',
							),
						'.landing-block-node-card-title' =>
							array (
								0 => 'landing-block-node-card-title h5 g-line-height-1_2 g-font-weight-700 g-font-size-18 g-color-white g-mb-10 g-font-montserrat',
								1 => 'landing-block-node-card-title h5 g-line-height-1_2 g-font-weight-700 g-font-size-18 g-color-white g-mb-10 g-font-montserrat',
								2 => 'landing-block-node-card-title h5 g-line-height-1_2 g-font-weight-700 g-font-size-18 g-color-white g-mb-10 g-font-montserrat',
								3 => 'landing-block-node-card-title h5 g-line-height-1_2 g-font-weight-700 g-font-size-18 g-color-white g-mb-10 g-font-montserrat',
								4 => 'landing-block-node-card-title h5 g-line-height-1_2 g-font-weight-700 g-font-size-18 g-color-white g-mb-10 g-font-montserrat',
								5 => 'landing-block-node-card-title h5 g-line-height-1_2 g-font-weight-700 g-font-size-18 g-color-white g-mb-10 g-font-montserrat',
							),
						'.landing-block-node-card-text' =>
							array (
								0 => 'landing-block-node-card-text small g-color-white-opacity-0_8 g-font-montserrat',
								1 => 'landing-block-node-card-text small g-color-white-opacity-0_8 g-font-montserrat',
								2 => 'landing-block-node-card-text small g-color-white-opacity-0_8 g-font-montserrat',
								3 => 'landing-block-node-card-text small g-color-white-opacity-0_8 g-font-montserrat',
								4 => 'landing-block-node-card-text small g-color-white-opacity-0_8 g-font-montserrat',
								5 => 'landing-block-node-card-text small g-color-white-opacity-0_8 g-font-montserrat',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block g-pt-90 g-pb-90',
							),
					),
			],
		'#block4023' => [
				'old_id' => 207,
				'code' => '33.32.form_light_bgimg_right_text',
				'access' => 'X',
				'nodes' =>
					array (
						'.landing-block-node-title' =>
							array (
								0 => '<span bxstyle="font-weight: normal;">BECOME </span>SUCCESSFUL',
							),
						'.landing-block-node-subtitle' =>
							array (
								0 => 'SIGN UP NOW',
							),
						'.landing-block-node-text' =>
							array (
								0 => '<p>Maecenas lacus magna, pretium in congue a, pharetra at lacus. Nulla neque justo, sodales
							vitae dui non, imperdiet luctus libero.</p>',
							),
						'.landing-block-node-button' =>
							array (
								0 =>
									array (
										'href' => '#',
										'target' => NULL,
										'attrs' =>
											array (
												'data-embed' => NULL,
												'data-url' => NULL,
											),
										'text' => 'Learn more',
									),
							),
						'.landing-block-node-bgimg' =>
							array (
								0 =>
									array (
										'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1080/img17.jpg',
										'url' => '{"text":"","href":"","target":"_self","enabled":false}',
										'id' => '794',
										'id2x' => '795',
									),
							),
					),
				'style' =>
					array (
						'.landing-block-node-title' =>
							array (
								0 => 'landing-block-node-title js-animation fadeInRight text-uppercase g-line-height-1 g-font-weight-700 g-color-white g-mb-30 g-font-montserrat g-font-size-50',
							),
						'.landing-block-node-subtitle' =>
							array (
								0 => 'landing-block-node-subtitle text-uppercase g-font-weight-700 g-font-size-18 g-color-white g-mb-20 g-font-montserrat',
							),
						'.landing-block-node-text' =>
							array (
								0 => 'landing-block-node-text js-animation fadeInRight g-font-size-default g-color-white-opacity-0_8 g-mb-35 g-font-montserrat g-font-size-20',
							),
						'.landing-block-node-button' =>
							array (
								0 => 'landing-block-node-button js-animation fadeInRight btn btn-md text-uppercase g-font-weight-700 g-font-size-12 g-brd-none g-py-12 g-px-25 g-rounded-3 g-font-montserrat g-brd-10 u-btn-twitter',
							),
						'.landing-block-node-bgimg' =>
							array (
								0 => 'landing-block-node-bgimg g-bg-size-cover g-bg-img-hero g-bg-cover g-bg-black-opacity-0_3--after g-py-20',
							),
						'.landing-block-node-form' =>
							array (
								0 => 'col-sm-12 col-lg-6 js-animation fadeInLeft landing-block-node-form align-self-center',
							),
						'#wrapper' =>
							array (
								0 => 'g-pos-rel landing-block g-bg-primary-dark-v1',
							),
					),
			],
		'#block4024' => [
				'old_id' => 219,
				'code' => '17.copyright',
				'access' => 'X',
				'nodes' =>
					array (
						'.landing-block-node-text' =>
							array (
								0 => '
				<p>&copy; 2018 All rights reserved.</p>
			',
							),
					),
				'style' =>
					array (
						'.landing-block-node-text' =>
							array (
								0 => 'landing-block-node-text js-animation animated g-color-lightblue g-font-size-15 pulse',
							),
						'#wrapper' =>
							array (
								0 => 'landing-block js-animation animation-none animated g-theme-event-bg-blue-dark-v2',
							),
					),
			],
		],
];