<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);
Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/landing.demo/data/page/bitrix24/notttranslate.php');

return [
	'name' => Loc::getMessage('LANDING_DEMO_B24_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_B24_DESCRIPTION'),
	'sort' => 3,
	'version' => 3,
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => '3corporate',
			'THEME_CODE_TYPO' => '3corporate',
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/bitrix24/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_B24_TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_B24_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_B24_TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_B24_DESCRIPTION'),
			'METAROBOTS_INDEX' => 'Y',
		],
	],
	'replace' => [
		'#partner_id#' => Option::get('bitrix24', 'partner_id', 0),
	],
	'items' => [
		'0' => [
			'code' => '0.menu_02',
			'access' => 'X',
			'cards' => [
				'.landing-block-node-menu-list-item' => [
					'source' => [
						0 => [
							'value' => 1,
							'type' => 'card',
						],
						1 => [
							'value' => 1,
							'type' => 'card',
						],
						2 => [
							'value' => 1,
							'type' => 'card',
						],
						3 => [
							'value' => 1,
							'type' => 'card',
						],
						4 => [
							'value' => 1,
							'type' => 'card',
						],
						5 => [
							'value' => 1,
							'type' => 'card',
						],
						6 => [
							'value' => 1,
							'type' => 'card',
						],
						7 => [
							'value' => 1,
							'type' => 'card',
						],
						8 => [
							'value' => 1,
							'type' => 'card',
						],
					],
				],
			],
			'nodes' => [
				'.landing-block-node-menu-list-item-link' => [
					0 => [
						'href' => '#block1',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT1"),
					],
					1 => [
						'href' => '#block2',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT2"),
					],
					2 => [
						'href' => '#block3',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT3"),
					],
					3 => [
						'href' => '#block10',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT4"),
					],
					4 => [
						'href' => '#block13',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT5"),
					],
					5 => [
						'href' => '#block15',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT6"),
					],
					6 => [
						'href' => '#block16',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT7"),
					],
					7 => [
						'href' => '#block18',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT8"),
					],
					8 => [
						'href' => '#block19',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT9"),
					],
				],
				'.landing-block-node-menu-logo-link' => [
					0 => [
						'href' => '#system_mainpage',
						'target' => null,
						'text' => '
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180" src="https://cdn.bitrix24.site/bitrix/images/landing/logos/agency-logo-dark.png" alt="" />
				',
					],
				],
				'.landing-block-node-menu-logo' => [
					0 => [
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/logos/agency-logo-dark.png',
					],
				],
			],
			'style' => [
				'.landing-block-node-menu-list-item-link' => [
					0 => 'landing-block-node-menu-list-item-link nav-link p-0',
					1 => 'landing-block-node-menu-list-item-link nav-link p-0',
					2 => 'landing-block-node-menu-list-item-link nav-link p-0',
					3 => 'landing-block-node-menu-list-item-link nav-link p-0',
					4 => 'landing-block-node-menu-list-item-link nav-link p-0',
					5 => 'landing-block-node-menu-list-item-link nav-link p-0',
					6 => 'landing-block-node-menu-list-item-link nav-link p-0',
					7 => 'landing-block-node-menu-list-item-link nav-link p-0',
					8 => 'landing-block-node-menu-list-item-link nav-link p-0',
				],
				'.navbar' => [
					0 => 'navbar navbar-expand-lg p-0 g-px-15 u-navbar-color-black u-navbar-color-blue--hover',
				],
				'#wrapper' => [
					0 => 'landing-block landing-block-menu u-header u-header--floating u-header--floating-relative',
				],
			],
		],
		'1' => [
			'code' => '43.4.cover_with_price_text_button_bgimg',
			'access' => 'X',
			'old_id' => 1,
			'anchor' => 'about',
			'cards' => [
				'.landing-block-node-card' => [
					'source' => [
						0 => [
							'value' => 0,
							'type' => 'card',
						],
						1 => [
							'value' => 0,
							'type' => 'card',
						],
						2 => [
							'value' => 0,
							'type' => 'card',
						],
						3 => [
							'value' => 0,
							'type' => 'card',
						],
						4 => [
							'value' => 0,
							'type' => 'card',
						],
					],
				],
			],
			'nodes' => [
				'.landing-block-node-card-price' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT10"),
					1 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT11"),
					2 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT12"),
					3 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT13"),
					4 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT14"),
				],
				'.landing-block-node-card-title' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT15"),
					1 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT16"),
					2 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT17"),
					3 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT18"),
					4 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT19"),
				],
				'.landing-block-node-card-text' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT20"),
					1 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT21"),
					2 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT22"),
					3 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT23"),
					4 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT24"),
				],
				'.landing-block-node-card-button' => [
					0 => [
						'href' => '#',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT25"),
					],
					1 => [
						'href' => '#',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT25"),
					],
					2 => [
						'href' => '#',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT25"),
					],
					3 => [
						'href' => '#',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT25"),
					],
					4 => [
						'href' => '#',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT25"),
					],
				],
				'.landing-block-node-card-bgimg' => [
					0 => [
						'src' => 'https://cdn.bitrix24.ru/b1479079/landing/bcc/bcc5e00181ca124372c2a79e0f58146f/crm.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
						'id' => '5290',
					],
					1 => [
						'src' => 'https://cdn.bitrix24.ru/b1479079/landing/aa2/aa214ffc8cfa81e01621511185960e3d/tasks.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
					],
					2 => [
						'src' => 'https://cdn.bitrix24.ru/b1479079/landing/f08/f08f15c846339599e39b11a6548d9e61/sites.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
					],
					3 => [
						'src' => 'https://cdn.bitrix24.ru/b1479079/landing/5d5/5d5c83066ea04192506ede4f625bf598/olines.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
					],
					4 => [
						'src' => 'https://cdn.bitrix24.ru/b1479079/landing/a00/a008f81478fe28497216039f7dafad83/company.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
					],
				],
			],
			'style' => [
				'.landing-block-node-card-price' => [
					0 => 'landing-block-node-card-price u-ribbon-v1 text-uppercase g-pos-rel g-line-height-1_2 g-font-weight-700 g-font-size-16 g-font-size-18 g-color-white g-theme-travel-bg-black-v1 g-pa-10 g-mb-10',
					1 => 'landing-block-node-card-price u-ribbon-v1 text-uppercase g-pos-rel g-line-height-1_2 g-font-weight-700 g-font-size-16 g-font-size-18 g-color-white g-theme-travel-bg-black-v1 g-pa-10 g-mb-10',
					2 => 'landing-block-node-card-price u-ribbon-v1 text-uppercase g-pos-rel g-line-height-1_2 g-font-weight-700 g-font-size-16 g-font-size-18 g-color-white g-theme-travel-bg-black-v1 g-pa-10 g-mb-10',
					3 => 'landing-block-node-card-price u-ribbon-v1 text-uppercase g-pos-rel g-line-height-1_2 g-font-weight-700 g-font-size-16 g-font-size-18 g-color-white g-theme-travel-bg-black-v1 g-pa-10 g-mb-10',
					4 => 'landing-block-node-card-price u-ribbon-v1 text-uppercase g-pos-rel g-line-height-1_2 g-font-weight-700 g-font-size-16 g-font-size-18 g-color-white g-theme-travel-bg-black-v1 g-pa-10 g-mb-10',
				],
				'.landing-block-node-card-title' => [
					0 => 'landing-block-node-card-title text-uppercase g-pos-rel g-line-height-1 g-font-weight-700 g-color-white g-mb-10 g-font-montserrat g-font-size-60 g-font-roboto-slab',
					1 => 'landing-block-node-card-title text-uppercase g-pos-rel g-line-height-1 g-font-weight-700 g-color-white g-mb-10 g-font-montserrat g-font-size-60 g-font-roboto-slab',
					2 => 'landing-block-node-card-title text-uppercase g-pos-rel g-line-height-1 g-font-weight-700 g-color-white g-mb-10 g-font-montserrat g-font-size-60 g-font-roboto-slab',
					3 => 'landing-block-node-card-title text-uppercase g-pos-rel g-line-height-1 g-font-weight-700 g-color-white g-mb-10 g-font-montserrat g-font-size-60 g-font-roboto-slab',
					4 => 'landing-block-node-card-title text-uppercase g-pos-rel g-line-height-1 g-font-weight-700 g-color-white g-mb-10 g-font-montserrat g-font-size-60 g-font-roboto-slab',
				],
				'.landing-block-node-card-text' => [
					0 => 'landing-block-node-card-text g-mb-20 g-font-size-18 g-color-white-opacity-0_8',
					1 => 'landing-block-node-card-text g-mb-20 g-font-size-18 g-color-white-opacity-0_8',
					2 => 'landing-block-node-card-text g-mb-20 g-font-size-18 g-color-white-opacity-0_8',
					3 => 'landing-block-node-card-text g-mb-20 g-font-size-18 g-color-white-opacity-0_8',
					4 => 'landing-block-node-card-text g-mb-20 g-font-size-18 g-color-white-opacity-0_8',
				],
				'.landing-block-node-card-button' => [
					0 => 'landing-block-node-card-button btn btn-md text-uppercase g-font-weight-700 g-font-size-12 g-py-10 g-px-25 g-rounded-20 u-btn-blue',
					1 => 'landing-block-node-card-button btn btn-md text-uppercase g-font-weight-700 g-font-size-12 g-py-10 g-px-25 g-rounded-20 u-btn-blue',
					2 => 'landing-block-node-card-button btn btn-md text-uppercase g-font-weight-700 g-font-size-12 g-py-10 g-px-25 g-rounded-20 u-btn-blue',
					3 => 'landing-block-node-card-button btn btn-md text-uppercase g-font-weight-700 g-font-size-12 g-py-10 g-px-25 g-rounded-20 u-btn-blue',
					4 => 'landing-block-node-card-button btn btn-md text-uppercase g-font-weight-700 g-font-size-12 g-py-10 g-px-25 g-rounded-20 u-btn-blue',
				],
				'.landing-block-node-card-bgimg' => [
					0 => 'landing-block-node-card-bgimg g-bg-img-hero u-bg-overlay g-bg-black-opacity-0_4--after d-flex align-items-center justify-content-center w-100 h-100 g-min-height-100vh',
					1 => 'landing-block-node-card-bgimg g-bg-img-hero u-bg-overlay g-bg-black-opacity-0_4--after d-flex align-items-center justify-content-center w-100 h-100 g-min-height-100vh',
					2 => 'landing-block-node-card-bgimg g-bg-img-hero u-bg-overlay g-bg-black-opacity-0_4--after d-flex align-items-center justify-content-center w-100 h-100 g-min-height-100vh',
					3 => 'landing-block-node-card-bgimg g-bg-img-hero u-bg-overlay g-bg-black-opacity-0_4--after d-flex align-items-center justify-content-center w-100 h-100 g-min-height-100vh',
					4 => 'landing-block-node-card-bgimg g-bg-img-hero u-bg-overlay g-bg-black-opacity-0_4--after d-flex align-items-center justify-content-center w-100 h-100 g-min-height-100vh',
				],
				'.landing-block-node-card-container' => [
					0 => 'container landing-block-node-card-container js-animation animation-none g-mx-0 g-pa-0 animated',
					1 => 'container landing-block-node-card-container js-animation animation-none g-mx-0 g-pa-0 animated',
					2 => 'container landing-block-node-card-container js-animation animation-none g-mx-0 g-pa-0 animated',
					3 => 'container landing-block-node-card-container js-animation animation-none g-mx-0 g-pa-0 animated',
					4 => 'container landing-block-node-card-container js-animation animation-none g-mx-0 g-pa-0 animated',
				],
				'.landing-block-node-card-button-container' => [
					0 => 'landing-block-node-card-button-container',
					1 => 'landing-block-node-card-button-container',
					2 => 'landing-block-node-card-button-container',
					3 => 'landing-block-node-card-button-container',
					4 => 'landing-block-node-card-button-container',
				],
				'#wrapper' => [
					0 => 'landing-block g-bg-black',
				],
			],
		],
		'2' => [
			'code' => '21.2.three_cols_big_bgimg_title_text_button',
			'access' => 'X',
			'old_id' => 2,
			'anchor' => 'whyWe',
			'cards' => [
				'.landing-block-card' => [
					'source' => [
						0 => [
							'value' => 0,
							'type' => 'card',
						],
						1 => [
							'value' => 0,
							'type' => 'card',
						],
						2 => [
							'value' => 0,
							'type' => 'card',
						],
					],
				],
			],
			'nodes' => [
				'.landing-block-node-img' => [
					0 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/900x506/img1.jpg',
					],
					1 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/900x506/img2.jpg',
					],
					2 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/900x506/img3.jpg',
					],
				],
				'.landing-block-node-title' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT26"),
					1 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT27"),
					2 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT28"),
				],
				'.landing-block-node-text' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT29"),
					1 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT30"),
					2 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT31"),
				],
				'.landing-block-node-button' => [
					0 => [
						'href' => '#',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT32"),
					],
					1 => [
						'href' => '#',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT33"),
					],
					2 => [
						'href' => '#',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT34"),
					],
				],
			],
			'style' => [
				'.landing-block-card' => [
					0 => 'landing-block-card col-lg-4 landing-block-node-img g-min-height-350 g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover js-animation fadeInUp landing-card g-bg-primary-opacity-0_9--after g-pa-40',
					1 => 'landing-block-card col-lg-4 landing-block-node-img g-min-height-350 g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover js-animation fadeInUp landing-card g-bg-primary-opacity-0_9--after g-pa-40',
					2 => 'landing-block-card col-lg-4 landing-block-node-img g-min-height-350 g-bg-img-hero row no-gutters align-items-center justify-content-center u-bg-overlay g-transition--ease-in g-transition-0_2 g-transform-scale-1_03--hover js-animation fadeInUp landing-card g-bg-primary-opacity-0_9--after g-pa-40',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title js-animation animation-none text-uppercase g-font-weight-700 g-color-white g-mb-20 animated g-font-montserrat g-font-size-28',
					1 => 'landing-block-node-title js-animation animation-none text-uppercase g-font-weight-700 g-color-white g-mb-20 animated g-font-montserrat g-font-size-28',
					2 => 'landing-block-node-title js-animation animation-none text-uppercase g-font-weight-700 g-color-white g-mb-20 animated g-font-montserrat g-font-size-28',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text js-animation animation-none animated g-color-white g-font-size-18',
					1 => 'landing-block-node-text js-animation animation-none animated g-color-white g-font-size-18',
					2 => 'landing-block-node-text js-animation animation-none animated g-color-white g-font-size-18',
				],
				'.landing-block-node-button' => [
					0 => 'landing-block-node-button js-animation animation-none btn btn-lg u-btn-inset mx-2 animated u-btn-white g-rounded-25 g-color-black g-color-blue--hover',
					1 => 'landing-block-node-button js-animation animation-none btn btn-lg u-btn-inset mx-2 animated u-btn-white g-rounded-25 g-color-black g-color-blue--hover',
					2 => 'landing-block-node-button js-animation animation-none btn btn-lg u-btn-inset mx-2 animated u-btn-white g-rounded-25 g-color-black g-color-blue--hover',
				],
				'.landing-block-node-button-container' => [
					0 => 'landing-block-node-button-container',
					1 => 'landing-block-node-button-container',
					2 => 'landing-block-node-button-container',
				],
				'.landing-block-inner' => [
					0 => 'row no-gutters g-overflow-hidden landing-block-inner',
				],
				'#wrapper' => [
					0 => 'landing-block container-fluid px-0',
				],
			],
		],
		'3' => [
			'code' => '27.one_col_fix_title_and_text_2',
			'access' => 'X',
			'old_id' => 3,
			'anchor' => 'services',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT35"),
				],
				'.landing-block-node-text' => [
					0 => ' ',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title h2 g-text-transform-none g-font-size-42 g-font-montserrat',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-font-size-16 g-pb-1',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pt-60 g-pb-30',
				],
			],
		],
		'4' => [
			'code' => '49.2.two_cols_text_video_fix',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT36"),
				],
				'.landing-block-node-text' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT37"),
				],
				'.landing-block-node-video' => [
					0 => [
						'src' => 'https://www.youtube.com/embed/WON4qYmW4MQ?autoplay=0&controls=0&loop=1&mute=0&rel=0&start=0&html5=1&v=WON4qYmW4MQ&playerVars=[object%20Object]&enablejsapi=1',
						'data-source' => 'https://www.youtube.com/watch?v=WON4qYmW4MQ',
					],
				],
			],
			'style' => [
				'.landing-block-node-text-container' => [
					0 => 'landing-block-node-text-container js-animation slideInRight col-md-6',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title text-uppercase g-font-weight-700 g-font-size-26 mb-0 g-mb-15 g-font-montserrat',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-color-black',
				],
				'.landing-block-node-video-col' => [
					0 => 'landing-block-node-video-col js-animation slideInLeft col-md-6 g-mb-0--md g-mb-20',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-50 g-pb-50',
				],
			],
		],
		'5' => [
			'code' => '49.3.two_cols_video_text_fix',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT38"),
				],
				'.landing-block-node-text' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT39"),
				],
				'.landing-block-node-video' => [
					0 => [
						'src' => 'https://www.youtube.com/embed/4X09G41i71U?autoplay=0&controls=0&loop=1&mute=0&rel=0&start=0&html5=1&v=4X09G41i71U&playerVars=[object%20Object]&enablejsapi=1',
						'data-source' => 'https://www.youtube.com/watch?v=4X09G41i71U',
					],
				],
			],
			'style' => [
				'.landing-block-node-text-container' => [
					0 => 'landing-block-node-text-container js-animation slideInLeft col-md-6 g-pb-20 g-pb-0--md',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title text-uppercase g-font-weight-700 g-font-size-26 mb-0 g-mb-15 g-font-montserrat',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-color-black',
				],
				'.landing-block-node-video-col' => [
					0 => 'landing-block-node-video-col js-animation slideInRight col-md-6',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-50 g-pb-50',
				],
			],
		],
		'6' => [
			'code' => '49.2.two_cols_text_video_fix',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT40"),
				],
				'.landing-block-node-text' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT41"),
				],
				'.landing-block-node-video' => [
					0 => [
						'src' => 'https://www.youtube.com/embed/U-4N2ez4IbE?autoplay=0&controls=0&loop=1&mute=0&rel=0&start=0&html5=1&v=U-4N2ez4IbE&playerVars=[object%20Object]&enablejsapi=1',
						'data-source' => 'https://www.youtube.com/watch?v=U-4N2ez4IbE',
					],
				],
			],
			'style' => [
				'.landing-block-node-text-container' => [
					0 => 'landing-block-node-text-container js-animation slideInRight col-md-6',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title text-uppercase g-font-weight-700 g-font-size-26 mb-0 g-mb-15 g-font-montserrat',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-color-black',
				],
				'.landing-block-node-video-col' => [
					0 => 'landing-block-node-video-col js-animation slideInLeft col-md-6 g-mb-0--md g-mb-20',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-50 g-pb-50',
				],
			],
		],
		'7' => [
			'code' => '49.3.two_cols_video_text_fix',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT42"),
				],
				'.landing-block-node-text' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT43"),
				],
				'.landing-block-node-video' => [
					0 => [
						'src' => 'https://www.youtube.com/embed/8_MzDktbznk?autoplay=0&controls=0&loop=1&mute=0&rel=0&start=0&html5=1&v=8_MzDktbznk&playerVars=[object%20Object]&enablejsapi=1',
						'data-source' => 'https://www.youtube.com/watch?v=8_MzDktbznk',
					],
				],
			],
			'style' => [
				'.landing-block-node-text-container' => [
					0 => 'landing-block-node-text-container js-animation slideInLeft col-md-6 g-pb-20 g-pb-0--md',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title text-uppercase g-font-weight-700 g-font-size-26 mb-0 g-mb-15 g-font-montserrat',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-color-black',
				],
				'.landing-block-node-video-col' => [
					0 => 'landing-block-node-video-col js-animation slideInRight col-md-6',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-50 g-pb-50',
				],
			],
		],
		'8' => [
			'code' => '49.2.two_cols_text_video_fix',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT44"),
				],
				'.landing-block-node-text' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT45"),
				],
				'.landing-block-node-video' => [
					0 => [
						'src' => 'https://www.youtube.com/embed/6NMb3zldoTM?autoplay=0&controls=0&loop=1&mute=0&rel=0&start=0&html5=1&v=6NMb3zldoTM&enablejsapi=1',
						'data-source' => 'https://www.youtube.com/watch?v=6NMb3zldoTM',
					],
				],
			],
			'style' => [
				'.landing-block-node-text-container' => [
					0 => 'landing-block-node-text-container js-animation slideInRight col-md-6',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title text-uppercase g-font-weight-700 g-font-size-26 mb-0 g-mb-15 g-font-montserrat',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-color-gray-dark-v4',
				],
				'.landing-block-node-video-col' => [
					0 => 'landing-block-node-video-col js-animation slideInLeft col-md-6 g-mb-0--md g-mb-20',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-50 g-pb-50',
				],
			],
		],
		'9' => [
			'code' => '13.2.one_col_fix_button',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-button' => [
					0 => [
						'href' => "https://www.bitrix24.ru/features/?p=#partner_id#",
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT46"),
					],
				],
			],
			'style' => [
				'.landing-block-node-button' => [
					0 => 'landing-block-node-button btn btn-md text-uppercase g-px-15 g-font-weight-700 g-rounded-20 g-font-size-14 g-font-montserrat u-btn-blue',
				],
				'#wrapper' => [
					0 => 'landing-block text-center g-pt-20 g-pb-20',
				],
			],
		],
		'10' => [
			'code' => '27.one_col_fix_title_and_text_2',
			'access' => 'X',
			'old_id' => 10,
			'anchor' => 'workProcess',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT47"),
				],
				'.landing-block-node-text' => [
					0 => ' ',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title h2 g-text-transform-none g-font-montserrat g-font-size-36',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-font-size-16 g-pb-1',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pb-5 g-pt-60',
				],
			],
		],
		'11' => [
			'code' => '11.three_cols_fix_tariffs',
			'access' => 'X',
			'cards' => [
				'.landing-block-card' => [
					'source' => [
						0 => [
							'value' => 0,
							'type' => 'card',
						],
						1 => [
							'value' => 0,
							'type' => 'card',
						],
						2 => [
							'value' => 0,
							'type' => 'card',
						],
						3 => [
							'value' => 0,
							'type' => 'card',
						],
					],
				],
			],
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT48"),
					1 => 'CRM+',
					2 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT49"),
					3 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT50"),
				],
				'.landing-block-node-subtitle' => [
					0 => ' ',
					1 => ' ',
					2 => ' ',
					3 => ' ',
				],
				'.landing-block-node-price' => [
					0 => '<span bxstyle="font-weight: bold; color: rgb(51, 152, 220);">2&nbsp;490&nbsp;'.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT51").'</span>',
					1 => '<span bxstyle="font-weight: bold; color: rgb(51, 152, 220);">2&nbsp;490&nbsp;'.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT51").'</span>',
					2 => '<span bxstyle="font-weight: bold; color: rgb(51, 152, 220);">4&nbsp;990&nbsp;'.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT51").'</span>',
					3 => '<span bxstyle="font-weight: bold; color: rgb(51, 152, 220);">9&nbsp;990&nbsp;'.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT51").'</span>',
				],
				'.landing-block-node-price-text' => [
					0 => '<span bxstyle="color: rgb(51, 152, 220);">'.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT52").'</span>',
					1 => '<span bxstyle="color: rgb(51, 152, 220);">'.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT52").'</span>',
					2 => '<span bxstyle="color: rgb(51, 152, 220);">'.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT52").'</span>',
					3 => '<span bxstyle="color: rgb(51, 152, 220);">'.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT52").'</span>',
				],
				'.landing-block-node-price-list' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT53"),
					1 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT54"),
					2 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT56"),
					3 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT55"),
				],
				'.landing-block-node-price-button' => [
					0 => [
						'href' => '#form',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT57"),
					],
					1 => [
						'href' => '#form',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT57"),
					],
					2 => [
						'href' => '#form',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT57"),
					],
					3 => [
						'href' => '#form',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT57"),
					],
				],
			],
			'style' => [
				'.landing-block-card' => [
					0 => 'landing-block-card col-md-4 g-mb-30 g-mb-0--md landing-card col-lg-3 js-animation animation-none animated',
					1 => 'landing-block-card col-md-4 g-mb-30 g-mb-0--md landing-card col-lg-3 js-animation animation-none animated',
					2 => 'landing-block-card col-md-4 g-mb-30 g-mb-0--md landing-card col-lg-3 js-animation animation-none animated',
					3 => 'landing-block-card col-md-4 g-mb-30 g-mb-0--md landing-card col-lg-3 js-animation animation-none animated',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title text-uppercase h5 g-font-weight-500 g-mb-10 g-font-montserrat g-color-black',
					1 => 'landing-block-node-title text-uppercase h5 g-font-weight-500 g-mb-10 g-font-montserrat g-color-black',
					2 => 'landing-block-node-title text-uppercase h5 g-font-weight-500 g-mb-10 g-font-montserrat g-color-black',
					3 => 'landing-block-node-title text-uppercase h5 g-font-weight-500 g-mb-10 g-font-montserrat g-color-black',
				],
				'.landing-block-node-subtitle' => [
					0 => 'landing-block-node-subtitle g-font-style-normal',
					1 => 'landing-block-node-subtitle g-font-style-normal',
					2 => 'landing-block-node-subtitle g-font-style-normal',
					3 => 'landing-block-node-subtitle g-font-style-normal',
				],
				'.landing-block-node-price' => [
					0 => 'landing-block-node-price g-line-height-1_2 g-font-size-28 g-font-montserrat g-color-blue',
					1 => 'landing-block-node-price g-line-height-1_2 g-font-size-28 g-font-montserrat g-color-blue',
					2 => 'landing-block-node-price g-line-height-1_2 g-font-size-28 g-font-montserrat g-color-blue',
					3 => 'landing-block-node-price g-line-height-1_2 g-font-size-28 g-font-montserrat g-color-blue',
				],
				'.landing-block-node-price-text' => [
					0 => 'landing-block-node-price-text g-font-montserrat g-color-cyan',
					1 => 'landing-block-node-price-text g-font-montserrat g-color-cyan',
					2 => 'landing-block-node-price-text g-font-montserrat g-color-cyan',
					3 => 'landing-block-node-price-text g-font-montserrat g-color-cyan',
				],
				'.landing-block-node-price-list-item' => [
					0 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-font-size-16 g-color-black',
					1 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-font-size-16 g-color-black',
					2 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black',
					3 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black',
					4 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black',
					5 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black',
					6 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black',
					7 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black',
					8 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black',
					9 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-font-size-16 g-color-black',
					10 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-font-size-16 g-color-black',
					11 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-font-size-16 g-color-black',
					12 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-font-size-16 g-color-black',
					13 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black',
					14 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black',
					15 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black',
					16 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-font-size-16 g-color-black',
					17 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-font-size-16 g-color-black',
					18 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-font-size-16 g-color-black',
					19 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-font-size-16 g-color-black',
					20 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-font-size-16 g-color-black',
					21 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-font-size-16 g-color-black',
					22 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-font-size-16 g-color-black',
					23 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-font-size-16 g-color-black',
					24 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-font-size-16 g-color-black',
					25 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-font-size-16 g-color-black',
					26 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-font-size-16 g-color-black',
					27 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-font-size-16 g-color-black',
				],
				'.landing-block-node-price-button' => [
					0 => 'landing-block-node-price-button btn btn-md text-uppercase g-px-15 g-rounded-20 g-font-montserrat u-btn-blue',
					1 => 'landing-block-node-price-button btn btn-md text-uppercase g-px-15 g-rounded-20 g-font-montserrat u-btn-blue',
					2 => 'landing-block-node-price-button btn btn-md text-uppercase g-px-15 g-rounded-20 g-font-montserrat u-btn-blue',
					3 => 'landing-block-node-price-button btn btn-md text-uppercase g-px-15 g-rounded-20 g-font-montserrat u-btn-blue',
				],
				'.landing-block-card-container' => [
					0 => 'landing-block-card-container g-pa-30 g-bg-teal-opacity-0_1',
					1 => 'landing-block-card-container g-pa-30 g-bg-teal-opacity-0_1',
					2 => 'landing-block-card-container g-pa-30 g-bg-darkblue-opacity-0_1',
					3 => 'landing-block-card-container g-pa-30 g-bg-darkblue-opacity-0_1',
				],
				'.landing-block-node-price-container' => [
					0 => 'landing-block-node-price-container',
					1 => 'landing-block-node-price-container',
					2 => 'landing-block-node-price-container',
					3 => 'landing-block-node-price-container',
				],
				'.landing-block-inner' => [
					0 => 'row no-gutters landing-block-inner',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-30 g-pb-20',
				],
			],
		],
		'12' => [
			'code' => '13.1.one_col_fix_text_and_button',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT58"),
				],
				'.landing-block-node-button' => [
					0 => [
						'href' => "https://www.bitrix24.ru/features/?p=#partner_id#",
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT59"),
					],
				],
			],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-color-black g-font-size-25',
				],
				'.landing-block-node-button' => [
					0 => 'landing-block-node-button btn btn-md text-uppercase g-px-15 g-font-weight-700 g-rounded-20 g-font-montserrat u-btn-blue',
				],
				'#wrapper' => [
					0 => 'landing-block text-center g-pt-40 g-pb-40',
				],
			],
		],
		'13' => [
			'code' => '27.one_col_fix_title_and_text_2',
			'access' => 'X',
			'old_id' => 13,
			'anchor' => 'skills',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT60"),
				],
				'.landing-block-node-text' => [
					0 => ' ',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title h2 g-font-size-42 g-font-montserrat text-uppercase',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-font-size-16 g-pb-1',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pt-60 g-pb-20',
				],
			],
		],
		'14' => [
			'code' => '11.three_cols_fix_tariffs',
			'access' => 'X',
			'cards' => [
				'.landing-block-card' => [
					'source' => [
						0 => [
							'value' => 0,
							'type' => 'card',
						],
						1 => [
							'value' => 0,
							'type' => 'card',
						],
						2 => [
							'value' => 0,
							'type' => 'card',
						],
					],
				],
			],
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT61"),
					1 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT62"),
					2 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT63"),
				],
				'.landing-block-node-subtitle' => [
					0 => ' ',
					1 => ' ',
					2 => ' ',
				],
				'.landing-block-node-price' => [
					0 => '<span bxstyle="font-weight: bold; color: rgb(51, 152, 220);">2 490 '.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT64").'</span>',
					1 => '<span bxstyle="font-weight: bold; color: rgb(51, 152, 220);">2 490 '.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT64").'</span>',
					2 => '<span bxstyle="font-weight: bold; color: rgb(51, 152, 220);">4 490 '.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT64").'</span>',
				],
				'.landing-block-node-price-text' => [
					0 => '<span bxstyle="color: rgb(51, 152, 220);">'.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT65").'</span>',
					1 => '<span bxstyle="color: rgb(51, 152, 220);">'.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT65").'</span>',
					2 => '<span bxstyle="color: rgb(51, 152, 220);">'.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT65").'</span>',
				],
				'.landing-block-node-price-list' => [
					0 => '<li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black" bxstyle="">'.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT66-1").' </li><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black" bxstyle="">'.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT66").'</li>',
					1 => '<li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black" bxstyle="">'.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT66-1").' </li><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black" bxstyle="">'.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT66").'</li>',
					2 => '<li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black" bxstyle="">'.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT66-1").' </li><li class="landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black" bxstyle="">'.Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT66").'</li>',
				],
				'.landing-block-node-price-button' => [
					0 => [
						'href' => '#form',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT92"),
					],
					1 => [
						'href' => '#form',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT92"),
					],
					2 => [
						'href' => '#form',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT92"),
					],
				],
			],
			'style' => [
				'.landing-block-card' => [
					0 => 'landing-block-card js-animation fadeInUp col-md-4 col-lg-4 g-mb-30 g-mb-0--md landing-card',
					1 => 'landing-block-card js-animation fadeInUp col-md-4 col-lg-4 g-mb-30 g-mb-0--md landing-card',
					2 => 'landing-block-card js-animation fadeInUp col-md-4 col-lg-4 g-mb-30 g-mb-0--md landing-card',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title text-uppercase h5 g-color-gray-dark-v3 g-font-weight-500 g-mb-10 g-font-montserrat',
					1 => 'landing-block-node-title text-uppercase h5 g-color-gray-dark-v3 g-font-weight-500 g-mb-10 g-font-montserrat',
					2 => 'landing-block-node-title text-uppercase h5 g-color-gray-dark-v3 g-font-weight-500 g-mb-10 g-font-montserrat',
				],
				'.landing-block-node-subtitle' => [
					0 => 'landing-block-node-subtitle g-font-style-normal',
					1 => 'landing-block-node-subtitle g-font-style-normal',
					2 => 'landing-block-node-subtitle g-font-style-normal',
				],
				'.landing-block-node-price' => [
					0 => 'landing-block-node-price g-font-size-30 g-line-height-1_2 g-color-black g-font-montserrat',
					1 => 'landing-block-node-price g-font-size-30 g-line-height-1_2 g-color-black g-font-montserrat',
					2 => 'landing-block-node-price g-font-size-30 g-line-height-1_2 g-color-black g-font-montserrat',
				],
				'.landing-block-node-price-text' => [
					0 => 'landing-block-node-price-text g-font-montserrat',
					1 => 'landing-block-node-price-text g-font-montserrat',
					2 => 'landing-block-node-price-text g-font-montserrat',
				],
				'.landing-block-node-price-list-item' => [
					0 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black',
					1 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black',
					2 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black',
					3 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black',
					4 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black',
					5 => 'landing-block-node-price-list-item g-brd-bottom g-brd-gray-light-v3 g-py-12 g-color-black',
				],
				'.landing-block-node-price-button' => [
					0 => 'landing-block-node-price-button btn btn-md text-uppercase rounded-0 g-px-15 u-btn-blue',
					1 => 'landing-block-node-price-button btn btn-md text-uppercase rounded-0 g-px-15 u-btn-blue',
					2 => 'landing-block-node-price-button btn btn-md text-uppercase rounded-0 g-px-15 u-btn-blue',
				],
				'.landing-block-card-container' => [
					0 => 'landing-block-card-container g-pl-30 g-pr-30 g-pt-30 g-pb-30 g-bg-gray-light-v5',
					1 => 'landing-block-card-container g-pl-30 g-pr-30 g-pt-30 g-pb-30 g-bg-gray-light-v5',
					2 => 'landing-block-card-container g-pl-30 g-pr-30 g-pt-30 g-pb-30 g-bg-gray-light-v5',
				],
				'.landing-block-node-price-container' => [
					0 => 'landing-block-node-price-container',
					1 => 'landing-block-node-price-container',
					2 => 'landing-block-node-price-container',
				],
				'.landing-block-inner' => [
					0 => 'row no-gutters landing-block-inner',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-30 g-pb-20',
				],
			],
		],
		'15' => [
			'code' => '19.2.features_with_img_right',
			'access' => 'X',
			'old_id' => 15,
			'anchor' => 'team',
			'cards' => [
				'.landing-block-node-card' => [
					'source' => [
						0 => [
							'value' => 0,
							'type' => 'card',
						],
						1 => [
							'value' => 0,
							'type' => 'card',
						],
						2 => [
							'value' => 0,
							'type' => 'card',
						],
						3 => [
							'value' => 0,
							'type' => 'card',
						],
					],
				],
			],
			'nodes' => [
				'.landing-block-node-img' => [
					0 => [
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/mockups/mockup2.png',
					],
				],
				'.landing-block-node-subtitle' => [
					0 => ' ',
				],
				'.landing-block-node-title' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT67"),
				],
				'.landing-block-node-text' => [
					0 => ' ',
				],
				'.landing-block-node-card-icon' => [
					0 => [
						'classList' => [
							0 => 'landing-block-node-card-icon fa fa-flask',
						],
					],
					1 => [
						'classList' => [
							0 => 'landing-block-node-card-icon fa fa-magic',
						],
					],
					2 => [
						'classList' => [
							0 => 'landing-block-node-card-icon fa fa-magic',
						],
					],
					3 => [
						'classList' => [
							0 => 'landing-block-node-card-icon fa fa-magic',
						],
					],
				],
				'.landing-block-node-card-title' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT68"),
					1 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT69"),
					2 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT70"),
					3 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT71"),
				],
				'.landing-block-node-card-text' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT72"),
					1 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT73"),
					2 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT74"),
					3 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT75"),
				],
			],
			'style' => [
				'.landing-block-node-subtitle' => [
					0 => 'landing-block-node-subtitle g-font-weight-700 g-font-size-11 g-mb-15',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-line-height-1_3 g-font-size-36 mb-0 g-color-black g-font-montserrat',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-mb-65',
				],
				'.landing-block-node-card-title' => [
					0 => 'landing-block-node-card-title h6 text-uppercase g-font-weight-700 g-color-black g-mb-15 g-font-montserrat g-font-size-18',
					1 => 'landing-block-node-card-title h6 text-uppercase g-font-weight-700 g-color-black g-mb-15 g-font-montserrat g-font-size-18',
					2 => 'landing-block-node-card-title h6 text-uppercase g-font-weight-700 g-color-black g-mb-15 g-font-montserrat g-font-size-18',
					3 => 'landing-block-node-card-title h6 text-uppercase g-font-weight-700 g-color-black g-mb-15 g-font-montserrat g-font-size-18',
				],
				'.landing-block-node-card-text' => [
					0 => 'landing-block-node-card-text g-font-size-default mb-0 g-color-black g-font-size-17',
					1 => 'landing-block-node-card-text g-font-size-default mb-0 g-color-black g-font-size-17',
					2 => 'landing-block-node-card-text g-font-size-default mb-0 g-color-black g-font-size-17',
					3 => 'landing-block-node-card-text g-font-size-default mb-0 g-color-black g-font-size-17',
				],
				'.landing-block-node-card-icon-border' => [
					0 => 'landing-block-node-card-icon-border u-icon-v2 u-icon-size--lg g-font-size-26 g-rounded-50x g-brd-primary-dark-dark-v3 g-color-blue',
					1 => 'landing-block-node-card-icon-border u-icon-v2 u-icon-size--lg g-font-size-26 g-rounded-50x g-brd-primary-dark-dark-v3 g-color-blue',
					2 => 'landing-block-node-card-icon-border u-icon-v2 u-icon-size--lg g-font-size-26 g-rounded-50x g-brd-primary-dark-dark-v3 g-color-blue',
					3 => 'landing-block-node-card-icon-border u-icon-v2 u-icon-size--lg g-font-size-26 g-rounded-50x g-brd-primary-dark-dark-v3 g-color-blue',
				],
				'.landing-block-node-img' => [
					0 => 'landing-block-node-img js-animation slideInRight img-fluid',
				],
				'.landing-block-node-text-container' => [
					0 => 'landing-block-node-text-container col-md-7 col-lg-7 d-flex text-center text-md-left',
				],
				'#wrapper' => [
					0 => 'landing-block g-bg-gray-light-v5 g-pt-90 g-pb-90',
				],
			],
		],
		'16' => [
			'code' => '27.one_col_fix_title_and_text_2',
			'access' => 'X',
			'old_id' => 16,
			'anchor' => 'testimonials',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT76"),
				],
				'.landing-block-node-text' => [
					0 => ' ',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title h2 g-font-size-42 g-font-montserrat g-color-white text-uppercase',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-font-size-16 g-pb-1',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pt-40 g-pb-40 g-bg-blue',
				],
			],
		],
		'17' => [
			'code' => '12.image_carousel_6_cols_fix',
			'access' => 'X',
		],
		'18' => [
			// todo: terebonk
			'code' => '07.2.two_col_fix_text_with_icon_with_title',
			'access' => 'X',
			'old_id' => 18,
			'anchor' => 'steps',
			'cards' => [
				'.landing-block-card' => [
					'source' => [
						0 => [
							'value' => 0,
							'type' => 'card',
						],
						1 => [
							'value' => 0,
							'type' => 'card',
						],
						2 => [
							'value' => 0,
							'type' => 'card',
						],
						3 => [
							'value' => 0,
							'type' => 'card',
						],
					],
				],
			],
			'nodes' => [
				'.landing-block-node-subtitle' => [
					0 => ' ',
				],
				'.landing-block-node-title' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT78"),
				],
				'.landing-block-node-element-icon' => [
					0 => [
						'classList' => [
							0 => 'landing-block-node-element-icon fa fa-bar-chart',
						],
					],
					1 => [
						'classList' => [
							0 => 'landing-block-node-element-icon fa fa-check-square-o',
						],
					],
					2 => [
						'classList' => [
							0 => 'landing-block-node-element-icon fa fa-cogs',
						],
					],
					3 => [
						'classList' => [
							0 => 'landing-block-node-element-icon fa fa-handshake-o',
						],
					],
				],
				'.landing-block-node-element-icon-hover' => [
					0 => [
						'src' => 'https://cdn.bitrix24.ru/b1479079/landing/f24/f24b3e442f07d5b970d830e156631b51/help-cloudman-07.jpg',
						'src2x' => 'https://cdn.bitrix24.ru/b1479079/landing/c45/c45bf460958c2f70ac98cab1ca021d7f/help-cloudman-07_2x.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
						'id' => '14924',
						'id2x' => '14926',
					],
					1 => [
						'src' => 'https://cdn.bitrix24.ru/b1479079/landing/f68/f6822050adc7dc3ac235173eb75f1e7c/vesy.jpg',
						'src2x' => 'https://cdn.bitrix24.ru/b1479079/landing/42f/42f6f7e90d1f2f1b5293712e5d9a5bd1/vesy_2x.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
						'id' => '14928',
						'id2x' => '14930',
					],
					2 => [
						'src' => 'https://cdn.bitrix24.ru/b1479079/landing/c98/c98f0842307771e010b5f276ae4c7a83/kluch.jpg',
						'src2x' => 'https://cdn.bitrix24.ru/b1479079/landing/a3b/a3baade1a1b745919a75702b53466422/kluch_2x.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
						'id' => '14932',
						'id2x' => '14934',
					],
					3 => [
						'src' => 'https://cdn.bitrix24.ru/b1479079/landing/723/723b46b2709130a40f9f29ec4c97abb7/palec.jpg',
						'src2x' => 'https://cdn.bitrix24.ru/b1479079/landing/0fa/0fa99995b8a1fd678b3d1d0b0bd10b22/palec_2x.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
						'id' => '14936',
						'id2x' => '14938',
					],
				],
				'.landing-block-node-element-title' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT79"),
					1 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT80"),
					2 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT81"),
					3 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT82"),
				],
				'.landing-block-node-element-text' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT83"),
					1 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT84"),
					2 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT85"),
					3 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT86"),
				],
			],
			'style' => [
				'.landing-block-node-subtitle' => [
					0 => 'landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-font-size-40 g-mb-minus-10 g-font-montserrat g-color-white',
				],
				'.landing-block-node-element-title' => [
					0 => 'landing-block-node-element-title h5 text-uppercase g-mb-10 g-font-montserrat g-color-blue-dark-v1',
					1 => 'landing-block-node-element-title h5 text-uppercase g-mb-10 g-font-montserrat g-color-blue-dark-v1',
					2 => 'landing-block-node-element-title h5 text-uppercase g-mb-10 g-font-montserrat g-color-blue-dark-v1',
					3 => 'landing-block-node-element-title h5 text-uppercase g-mb-10 g-font-montserrat g-color-blue-dark-v1',
				],
				'.landing-block-node-element-text' => [
					0 => 'landing-block-node-element-text g-color-blue-dark-v1',
					1 => 'landing-block-node-element-text g-color-blue-dark-v1',
					2 => 'landing-block-node-element-text g-color-blue-dark-v1',
					3 => 'landing-block-node-element-text g-color-blue-dark-v1',
				],
				'.landing-block-card-container' => [
					0 => 'landing-block-card-container g-pos-rel g-parent g-py-35 g-px-25 g-pl-70--sm g-pl-60 g-ml-30 g-ml-0--sm g-bg-main',
					1 => 'landing-block-card-container g-pos-rel g-parent g-py-35 g-px-25 g-pl-70--sm g-pl-60 g-ml-30 g-ml-0--sm g-bg-main',
					2 => 'landing-block-card-container g-pos-rel g-parent g-py-35 g-px-25 g-pl-70--sm g-pl-60 g-ml-30 g-ml-0--sm g-bg-main',
					3 => 'landing-block-card-container g-pos-rel g-parent g-py-35 g-px-25 g-pl-70--sm g-pl-60 g-ml-30 g-ml-0--sm g-bg-main',
				],
				'.landing-block-card' => [
					0 => 'landing-block-card js-animation fadeIn col-lg-6 g-px-30 g-mb-10 landing-card',
					1 => 'landing-block-card js-animation fadeIn col-lg-6 g-px-30 g-mb-10 landing-card',
					2 => 'landing-block-card js-animation fadeIn col-lg-6 g-px-30 g-mb-10 landing-card',
					3 => 'landing-block-card js-animation fadeIn col-lg-6 g-px-30 g-mb-10 landing-card',
				],
				'.landing-block-node-element-icon-container' => [
					0 => 'landing-block-node-element-icon-container g-pull-50x-left g-brd-around g-brd-5 g-rounded-50x g-overflow-hidden g-color-white g-bg-blue',
					1 => 'landing-block-node-element-icon-container g-pull-50x-left g-brd-around g-brd-5 g-rounded-50x g-overflow-hidden g-color-white g-bg-blue',
					2 => 'landing-block-node-element-icon-container g-pull-50x-left g-brd-around g-brd-5 g-rounded-50x g-overflow-hidden g-color-white g-bg-blue',
					3 => 'landing-block-node-element-icon-container g-pull-50x-left g-brd-around g-brd-5 g-rounded-50x g-overflow-hidden g-color-white g-bg-blue',
				],
				'.landing-block-node-header' => [
					0 => 'landing-block-node-header text-uppercase u-heading-v2-4--bottom g-mb-40 g-brd-transparent',
				],
				'.landing-block-inner' => [
					0 => 'row landing-block-inner',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-20 g-pb-65 g-bg-blue',
				],
			],
		],
		'19' => [
			'code' => '27.one_col_fix_title_and_text_2',
			'access' => 'X',
			'old_id' => 19,
			'anchor' => 'form',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT87"),
				],
				'.landing-block-node-text' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT88"),
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title h2 text-uppercase g-font-size-0 g-color-blue',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 g-font-montserrat g-color-black text-uppercase g-font-size-42',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pt-30 g-pb-25',
				],
			],
		],
		'20' => [
			'code' => '33.2.form_1_transparent_black_right_text',
			'access' => 'X',
			'cards' => [
				'.landing-block-node-card-contact' => [
					'source' => [
						0 => [
							'value' => 'text',
							'type' => 'preset',
						],
						1 => [
							'value' => 'link',
							'type' => 'preset',
						],
						2 => [
							'value' => 'link',
							'type' => 'preset',
						],
					],
				],
			],
			'nodes' => [
				'.landing-block-node-bgimg' => [
					0 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1080/img4.jpg',
					],
				],
				'.landing-block-node-text' => [
					0 => ' ',
				],
				'.landing-block-node-main-title' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT89"),
				],
				'.landing-block-node-title' => [
					0 => ' ',
				],
				'.landing-block-card-contact-icon' => [
					0 => [
						'classList' => [
							0 => 'landing-block-card-contact-icon icon-hotel-restaurant-235 u-line-icon-pro',
						],
					],
					1 => [
						'classList' => [
							0 => 'landing-block-card-contact-icon icon-communication-033 u-line-icon-pro',
						],
					],
					2 => [
						'classList' => [
							0 => 'landing-block-card-contact-icon icon-communication-062 u-line-icon-pro',
						],
					],
				],
				'.landing-block-node-contact-text' => [
					0 => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT90"),
				],
				'.landing-block-node-contact-link' => [],
				'.landing-block-card-linkcontact-link' => [
					0 => [
						'href' => 'tel:',
						'target' => '_self',
						'text' => Loc::getMessage("NOTTRANSLATE__LANDING_DEMO_B24__TEXT91"),
					],
					1 => [
						'href' => 'mailto:',
						'target' => '_self',
						'text' => 'email',
					],
				],
			],
			'style' => [
				'.landing-block-node-main-title' => [
					0 => 'landing-block-node-main-title js-animation fadeInUp h1 g-color-white mb-4 g-font-montserrat',
				],
				'.landing-block-card-contact-icon-container' => [
					0 => 'landing-block-card-contact-icon-container u-icon-v1 u-icon-size--sm g-color-white mr-2',
					1 => 'landing-block-card-contact-icon-container u-icon-v1 u-icon-size--sm g-color-white mr-2',
					2 => 'landing-block-card-contact-icon-container u-icon-v1 u-icon-size--sm g-color-white mr-2',
				],
				'.landing-block-node-title' => [
					0 => 'h4 g-color-white mb-4 landing-block-node-title',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text js-animation fadeInUp g-line-height-1_5 text-left g-mb-40 g-color-white-opacity-0_6',
				],
				'.landing-block-node-contact-text' => [
					0 => 'landing-block-node-contact-text g-color-white-opacity-0_6 mb-0',
				],
				'.landing-block-node-form' => [
					0 => 'bitrix24forms landing-block-node-form js-animation fadeInUp g-brd-none g-brd-around--sm g-brd-white-opacity-0_6 g-px-0 g-px-20--sm g-px-45--lg g-py-0 g-py-30--sm g-py-60--lg u-form-alert-v1',
				],
				'.landing-block-card-linkcontact-link' => [
					0 => 'landing-block-card-linkcontact-link g-color-white-opacity-0_6',
					1 => 'landing-block-card-linkcontact-link g-color-white-opacity-0_6',
				],
				'.landing-block-node-bgimg' => [
					0 => 'landing-block g-pt-120 g-pb-120 g-pos-rel landing-block-node-bgimg g-bg-size-cover g-bg-img-hero g-bg-cover g-bg-black-opacity-0_7--after',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-120 g-pb-120 g-pos-rel landing-block-node-bgimg g-bg-size-cover g-bg-img-hero g-bg-cover g-bg-black-opacity-0_7--after',
				],
			],
		],
	],

];