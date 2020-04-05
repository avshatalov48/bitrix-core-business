<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'code' => 'search-result3-dark',
	'type' => ['page', 'knowledge', 'group'],
	'section' => ['dynamic'],
	'name' => Loc::getMessage('LANDING_DEMO_PAGE_S_RES_3_NAME'),
	'description' => Loc::getMessage('LANDING_DEMO_PAGE_S_RES_3_DESCRIPTION'),
	'publication' => true,
	'version' => 3,
	'active' => false,
	'fields' => [
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_PAGE_S_RES_3_NAME'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_PAGE_S_RES_3_DESCRIPTION'),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/search-result3-dark/preview.jpg',
			'VIEW_USE' => 'N',
			'THEME_CODE' => '3corporate',
			'THEME_CODE_TYPO' => 'app',
			'PIXELFB_USE' => 'N',
			'GACOUNTER_USE' => 'N',
			'GACOUNTER_SEND_CLICK' => 'N',
			'GACOUNTER_SEND_SHOW' => 'N',
			'METAROBOTS_INDEX' => 'Y',
			'BACKGROUND_USE' => 'N',
			'BACKGROUND_POSITION' => 'center',
		],
	],
	'layout' => [
	],

	'items' => [
		'#block15135' => [
			'old_id' => 15135,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>KNOWLEDGE BASE</p>',
				],
			],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 container text-left g-max-width-container g-font-size-13 g-color-white g-font-montserrat g-letter-spacing-1',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation g-pt-15 g-pb-auto g-theme-bitrix-bg-dark-v2 animation-none',
				],
			],
		],
		'#block15136' => [
			'old_id' => 15136,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>Bitrix 24</p>',
				],
			],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 container text-left g-max-width-container g-font-size-75 g-color-white g-font-montserrat font-weight-bold',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation g-pt-auto g-pb-auto g-theme-bitrix-bg-dark-v2 animation-none',
				],
			],
		],
		'#block15134' => [
			'old_id' => 15134,
			'code' => '59.3.search_dark',
			'access' => 'X',
			'style' => [
				'.landing-block-node-button-container' => [
					0 => 'landing-block-node-button-container input-group-append g-z-index-4 g-theme-bitrix-bg-v3 g-theme-bitrix-bg-v3--hover g-color-white g-color-white--hover g-font-montserrat g-font-size-15',
				],
				'.landing-block-node-input-container' => [
					0 => 'landing-block-node-input-container form-control g-brd-primary--focus g-px-20 g-height-45 g-bg-transparent--hover g-color-white--hover g-color-white g-theme-bitrix-brd-v3 g-theme-bitrix-bg-dark-v2',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-15 g-pb-15 g-pl-15 g-pr-15 g-theme-bitrix-bg-dark-v2',
				],
			],
		],
		'#block15137' => [
			'old_id' => 15137,
			'code' => '58.1.news_sidebar_1',
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
					0 => 'Changes in identity confirmation for room rental',
					1 => 'Work schedules',
					2 => 'Knowledge base',
					3 => 'My reports',
				],
				'.landing-block-node-subtitle' => [
					0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.</p>',
					1 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.</p>',
					2 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.</p>',
					3 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.</p>',
				],
				'.landing-block-node-img' => [
					0 => [
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img1.jpg',
					],
					1 => [
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img2.jpg',
					],
					2 => [
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img3.jpg',
					],
					3 => [
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img4.jpg',
					],
				],
			],
			'style' => [
				'.landing-block-card' => [
					0 => 'landing-block-card js-animation fadeIn media g-mb-0--last landing-card g-mb-50',
					1 => 'landing-block-card js-animation fadeIn media g-mb-0--last landing-card g-mb-50',
					2 => 'landing-block-card js-animation fadeIn media g-mb-0--last landing-card g-mb-50',
					3 => 'landing-block-card js-animation fadeIn media g-mb-0--last landing-card g-mb-50',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-roboto g-theme-bitrix-color-v4 g-font-size-22',
					1 => 'landing-block-node-title g-font-roboto g-theme-bitrix-color-v4 g-font-size-22',
					2 => 'landing-block-node-title g-font-roboto g-theme-bitrix-color-v4 g-font-size-22',
					3 => 'landing-block-node-title g-font-roboto g-theme-bitrix-color-v4 g-font-size-22',
				],
				'.landing-block-node-subtitle' => [
					0 => 'landing-block-node-subtitle g-font-roboto g-font-size-12 g-color-gray-light-v4 g-font-size-16 g-pr-150',
					1 => 'landing-block-node-subtitle g-font-roboto g-font-size-12 g-color-gray-light-v4 g-font-size-16 g-pr-150',
					2 => 'landing-block-node-subtitle g-font-roboto g-font-size-12 g-color-gray-light-v4 g-font-size-16 g-pr-150',
					3 => 'landing-block-node-subtitle g-font-roboto g-font-size-12 g-color-gray-light-v4 g-font-size-16 g-pr-150',
				],
				'.landing-block-node-img' => [
					0 => 'landing-block-node-img g-width-60 g-height-60 g-object-fit-cover g-rounded-50x',
					1 => 'landing-block-node-img g-width-60 g-height-60 g-object-fit-cover g-rounded-50x',
					2 => 'landing-block-node-img g-width-60 g-height-60 g-object-fit-cover g-rounded-50x',
					3 => 'landing-block-node-img g-width-60 g-height-60 g-object-fit-cover g-rounded-50x',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-30 g-pb-25 g-pl-5 g-pr-5 g-theme-bitrix-bg-dark-v2',
				],
			],
		],
		'#block15138' => [
			'old_id' => 15138,
			'code' => '13.2.one_col_fix_button',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-button' => [
					0 => [
						'href' => '#',
						'target' => '_self',
						'attrs' => [
							'data-embed' => NULL,
							'data-url' => NULL,
						],
						'text' => 'MAIN PAGE',
					],
				],
			],
			'style' => [
				'.landing-block-node-button' => [
					0 => 'landing-block-node-button btn btn-sm btn-lg--sm text-uppercase rounded-0 g-color-white g-color-white--hover u-theme-bitrix-btn-v4 g-font-montserrat g-font-size-15 g-px-90 g-py-20 g-letter-spacing-2',
				],
				'#wrapper' => [
					0 => 'landing-block text-center g-pt-20 g-pb-20 g-theme-bitrix-bg-dark-v2',
				],
			],
		],
	],
];
