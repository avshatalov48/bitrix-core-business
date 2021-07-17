<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

return [
//	'old_id' => '707',
//	'code' => 'holiday.blackfriday',
	'name' => Loc::getMessage('LANDING_DEMO_BLACKFRIDAY-TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_BLACKFRIDAY-DESCRIPTION'),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'Y',
	'type' => 'page',
	'version' => 2,
	'fields' => [
		'TITLE' => Loc::getMessage('LANDING_DEMO_BLACKFRIDAY-TITLE'),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => 'photography',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'SETTINGS_HIDE_NOT_AVAILABLE' => 'L',
			'SETTINGS_HIDE_NOT_AVAILABLE_OFFERS' => 'N',
			'SETTINGS_PRODUCT_SUBSCRIPTION' => 'Y',
			'SETTINGS_USE_PRODUCT_QUANTITY' => 'Y',
			'SETTINGS_DISPLAY_COMPARE' => 'Y',
			'SETTINGS_PRICE_CODE' => [
				0 => 'BASE',
			],
			'SETTINGS_USE_PRICE_COUNT' => 'N',
			'SETTINGS_SHOW_PRICE_COUNT' => 1,
			'SETTINGS_PRICE_VAT_INCLUDE' => 'Y',
			'SETTINGS_SHOW_OLD_PRICE' => 'Y',
			'SETTINGS_SHOW_DISCOUNT_PERCENT' => 'Y',
			'SETTINGS_USE_ENHANCED_ECOMMERCE' => 'Y',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_BLACKFRIDAY-TITLE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_BLACKFRIDAY-DESCRIPTION'),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/holidays/holiday.blackfriday/preview.jpg',
			'GTM_USE' => 'N',
			'GACOUNTER_USE' => 'N',
			'GACOUNTER_SEND_CLICK' => 'N',
			'GACOUNTER_SEND_SHOW' => 'N',
			'METAROBOTS_INDEX' => 'Y',
			'BACKGROUND_USE' => 'N',
			'BACKGROUND_POSITION' => 'center',
			'YACOUNTER_USE' => 'N',
			'METAMAIN_USE' => 'N',
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_BLACKFRIDAY-TITLE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_BLACKFRIDAY-DESCRIPTION'),
			'HEADBLOCK_USE' => 'N',
		],
	],
	'layout' => [],
	'items' => [
		'#block8762' => [
			'old_id' => 8762,
			'code' => '43.4.cover_with_price_text_button_bgimg',
			'cards' => [
				'.landing-block-node-card' => 1,
			],
			'nodes' => [
				'.landing-block-node-card-price' => [
					0 => 'Only this <span style="">November</span>',
				],
				'.landing-block-node-card-title' => [
					0 => 'Black friday',
				],
				'.landing-block-node-card-text' => [
					0 => '<p>
								Donec erat urna, tincidunt at leo non, blandit finibus ante. Nunc venenatis risus in
								finibus dapibus. Ut ac
								massa sodales, mattis enim id, efficitur tortor.
							</p>',
				],
				'.landing-block-node-card-button' => [
					0 => [
						'href' => '#offer',
						'target' => '_self',
						'attrs' => [
							'data-embed' => null,
							'data-url' => null,
						],
						'text' => 'learn more',
					],
				],
				'.landing-block-node-card-bgimg' => [
					0 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img33.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					],
				],
			],
			'style' => [
				'.landing-block-node-card-price' => [
					0 => 'landing-block-node-card-price u-ribbon-v1 text-uppercase g-pos-rel g-line-height-1_2 g-font-weight-700 g-font-size-16 g-font-size-18 g-pa-10 g-mb-10 g-color-white-opacity-0_9 g-bg-lightblue-opacity-0_1',
				],
				'.landing-block-node-card-title' => [
					0 => 'landing-block-node-card-title text-uppercase g-pos-rel g-font-weight-700 g-color-white g-mb-10 g-font-size-60 g-letter-spacing-1 g-line-height-1_1',
				],
				'.landing-block-node-card-text' => [
					0 => 'landing-block-node-card-text g-mb-20 g-color-white g-font-size-16 g-letter-spacing-0 g-line-height-0',
				],
				'.landing-block-node-card-button' => [
					0 => 'landing-block-node-card-button btn g-btn-type-solid g-btn-px-m g-btn-size-md text-uppercase g-btn-red text-uppercase g-color-white-opacity-0_9 g-rounded-3',
				],
				'.landing-block-node-card-bgimg' => [
					0 => 'landing-block-node-card-bgimg g-bg-img-hero u-bg-overlay g-bg-black-opacity-0_4--after d-flex align-items-center justify-content-center w-100 h-100 g-min-height-100vh',
				],
				'.landing-block-node-card-button-container' => [
					0 => 'landing-block-node-card-button-container',
				],
				'#wrapper' => [
					0 => 'landing-block',
				],
			],
		],
		'#block8764' => [
			'old_id' => 8764,
			'anchor' => 'offer',
			'code' => '51.1.countdown_01',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => 'Upcoming event',
				],
				'.landing-block-node-number-text-days' => [
					0 => 'Days',
				],
				'.landing-block-node-number-text-hours' => [
					0 => 'Hours',
				],
				'.landing-block-node-number-text-minutes' => [
					0 => 'Minutes',
				],
				'.landing-block-node-number-text-seconds' => [
					0 => 'Seconds',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-300 g-color-primary g-font-size-55',
				],
				'.landing-block-node-number-number' => [
					0 => 'landing-block-node-number-number landing-block-node-number-number-days g-font-size-25 g-font-size-36--sm mb-0',
					1 => 'landing-block-node-number-number landing-block-node-number-number-days g-font-size-25 g-font-size-36--sm mb-0',
					2 => 'landing-block-node-number-number landing-block-node-number-number-days g-font-size-25 g-font-size-36--sm mb-0',
					3 => 'landing-block-node-number-number landing-block-node-number-number-days g-font-size-25 g-font-size-36--sm mb-0',
				],
				'.landing-block-node-number-text@0' => [
					0 => 'landing-block-node-number-text landing-block-node-number-text-days g-font-size-10 g-font-size-12--sm',
				],
				'.landing-block-node-number-text@01' => [
					0 => 'landing-block-node-number-text landing-block-node-number-text-hours g-font-size-10 g-font-size-12--sm',
				],
				'.landing-block-node-number-text@2' => [
					0 => 'landing-block-node-number-text landing-block-node-number-text-minutes g-font-size-10 g-font-size-12--sm',
				],
				'.landing-block-node-number-text@3' => [
					0 => 'landing-block-node-number-text landing-block-node-number-text-seconds g-font-size-10 g-font-size-12--sm',
				],
				'.landing-block-node-number-delimiter@0' => [
					0 => 'landing-block-node-number-delimiter u-countdown--days-hide d-inline-block align-top g-font-size-25 g-font-size-36--sm',
				],
				'.landing-block-node-number-delimiter@1' => [
					0 => 'landing-block-node-number-delimiter d-inline-block align-top g-font-size-25 g-font-size-36--sm',
				],
				'.landing-block-node-number-delimiter@2' => [
					0 => 'landing-block-node-number-delimiter d-inline-block align-top g-font-size-25 g-font-size-36--sm',
				],
				'#wrapper' => [
					0 => 'landing-block g-pb-80 g-pt-55',
				],
			],
			'attrs' => [
				'.landing-block-node-date' => [
					0 => [
						'data-end-date' => '1542924000000',
					],
				],
			],
		],
		'#block8766' => [
			'old_id' => 8766,
			'code' => '27.3.one_col_fix_title',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => 'What we offer<br /><div style="position: absolute; border: 2px dashed rgb(254, 84, 30); top: 0px; right: 0px; bottom: 0px; left: 0px; z-index: 9999; opacity: 0.4; pointer-events: none; transform: translateZ(0px);"></div>',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 g-color-white g-font-size-55 container g-pl-0 g-pr-0',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation g-bg-primary-dark-v3 g-pb-0 g-pt-45 animation-none text-center',
				],
			],
		],
		'#block8747' => [
			'old_id' => 8747,
			'code' => '34.4.two_cols_with_text_and_icons',
			'cards' => [
				'.landing-block-node-card' => 3,
			],
			'nodes' => [
				'.landing-block-node-card-icon' => [
					0 => [
						'classList' => [
							0 => 'landing-block-node-card-icon icon-finance-237 u-line-icon-pro',
						],
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					],
					1 => [
						'classList' => [
							0 => 'landing-block-node-card-icon icon-transport-025 u-line-icon-pro',
						],
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					],
					2 => [
						'classList' => [
							0 => 'landing-block-node-card-icon icon-christmas-037 u-line-icon-pro',
						],
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					],
				],
				'.landing-block-node-card-title' => [
					0 => 'Lowest prices',
					1 => 'free shipping',
					2 => 'gifts &quot;1+1=1&quot;',
				],
				'.landing-block-node-card-text' => [
					0 => '<p>Proin dignissim eget enim id aliquam.
								Proin ornare dictum leo, non elementum tellus molestie et.</p>',
					1 => '<p>Proin dignissim eget enim id aliquam. Proin ornare dictum leo, non elementum tellus molestie et.</p>',
					2 => '<p>Proin dignissim eget enim id aliquam. Proin ornare dictum leo, non elementum tellus molestie et.</p>',
				],
			],
			'style' => [
				'.landing-block-node-card' => [
					0 => 'landing-block-node-card js-animation fadeInUp col-md-6 g-mb-40 col-lg-4 g-mb-0--last',
					1 => 'landing-block-node-card js-animation fadeInUp col-md-6 g-mb-40 col-lg-4 g-mb-0--last',
					2 => 'landing-block-node-card js-animation fadeInUp col-md-6 g-mb-40 col-lg-4 g-mb-0--last',
				],
				'.landing-block-node-card-text' => [
					0 => 'landing-block-node-card-text mb-0 g-color-gray-light-v2',
					1 => 'landing-block-node-card-text mb-0 g-color-gray-light-v2',
					2 => 'landing-block-node-card-text mb-0 g-color-gray-light-v2',
				],
				'.landing-block-node-card-title' => [
					0 => 'landing-block-node-card-title h5 text-uppercase g-font-weight-800 g-color-white',
					1 => 'landing-block-node-card-title h5 text-uppercase g-font-weight-800 g-color-white',
					2 => 'landing-block-node-card-title h5 text-uppercase g-font-weight-800 g-color-white',
				],
				'.landing-block-node-card-icon-container' => [
					0 => 'landing-block-node-card-icon-container d-block g-font-size-48 g-line-height-1 g-color-deeporange',
					1 => 'landing-block-node-card-icon-container d-block g-font-size-48 g-line-height-1 g-color-deeporange',
					2 => 'landing-block-node-card-icon-container d-block g-font-size-48 g-line-height-1 g-color-deeporange',
				],
				'#wrapper' => [
					0 => 'landing-block g-bg-primary-dark-v3 g-pb-40 g-pt-55',
				],
			],
		],
		'#block8763' => [
			'old_id' => 8763,
			'code' => '33.3.form_1_transparent_black_no_text',
			'nodes' => [
				'.landing-block-node-bgimg' => [
					0 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img34.jpg',
						'data-pseudo-url' => '{"text":"","href":"","target":"_self","enabled":false}',
					],
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-pos-rel g-bg-primary-dark-v1 g-pt-120 g-pb-120 landing-block-node-bgimg g-bg-size-cover g-bg-img-hero g-bg-cover g-bg-black-opacity-0_7--after',
				],
			],
		],
	],
];