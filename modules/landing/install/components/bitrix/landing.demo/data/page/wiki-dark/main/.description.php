<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'old_id' => '1237',
	'parent' => 'wiki-dark',
	'code' => 'wiki-dark/main',
	'name' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_MAIN_TITLE"),
	'description' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_MAIN_DESCRIPTION"),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'N',
	'active' => false,
	'type' => ['knowledge', 'group'],
	'version' => 3,
	'fields' => [
		'TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_MAIN_TITLE"),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => '3corporate',
			'THEME_CODE_TYPO' => 'app',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'METAMAIN_USE' => 'N',
			'METAMAIN_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_MAIN_TITLE"),
			'METAMAIN_DESCRIPTION' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_MAIN_DESCRIPTION"),
			'METAOG_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_MAIN_TITLE"),
			'METAOG_DESCRIPTION' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_MAIN_DESCRIPTION"),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/wiki/main/preview.jpg',
			'PIXELFB_USE' => 'N',
			'GACOUNTER_USE' => 'N',
			'GACOUNTER_SEND_CLICK' => 'N',
			'GACOUNTER_SEND_SHOW' => 'N',
			'METAROBOTS_INDEX' => 'Y',
			'BACKGROUND_USE' => 'N',
			'BACKGROUND_POSITION' => 'center',
			'YACOUNTER_USE' => 'N',
			'GTM_USE' => 'N',
			'PIXELVK_USE' => 'N',
			'HEADBLOCK_USE' => 'N',
			'CSSBLOCK_USE' => 'N',
			],
		],
	'layout' => [],
	'items' => [
		'#block15110' => [
			'old_id' => 15110,
			'code' => '27.one_col_fix_title_and_text_2',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => 'KNOWLEDGE BASE',
					],
				'.landing-block-node-text' => [
					0 => '<p>Bitrix 24</p>',
					],
				],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title h2 g-font-size-13 g-font-montserrat g-color-white g-mb-minus-10 text-left g-letter-spacing-2',
					],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 g-font-size-75 font-weight-bold g-font-montserrat g-color-white g-mb-minus-10 text-left',
					],
				'.landing-block-node-text-container' => [
					0 => 'landing-block-node-text-container container g-max-width-container',
					],
				'#wrapper' => [
					0 => 'landing-block js-animation g-pt-30 g-pb-5 animation-none animated g-theme-bitrix-bg-dark-v2',
					],
				],
			],
		'#block15045' => [
			'old_id' => 15045,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p>',
					],
				],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-font-size-16 g-pb-1 container g-max-width-container text-left g-pl-auto g-color-gray-light-v3 g-font-roboto g-pr-150',
					],
				'#wrapper' => [
					0 => 'landing-block js-animation g-pb-auto g-pt-auto animation-none animated g-theme-bitrix-bg-dark-v2',
					],
				],
			],
		'#block15051' => [
			'old_id' => 15051,
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
					0 => 'landing-block g-pt-30 g-pb-80 g-pl-15 g-pr-15 g-theme-bitrix-bg-dark-v2',
				],
			],
		],
		'#block15046' => [
			'old_id' => 15046,
			'code' => '40.6.two_img_top_bottom',
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
						],
					],
				],
			'nodes' => [
				'.landing-block-card-title' =>
					array (
						0 => '<p>Office</p>',
						1 => '<p>Office</p>',
						),
				'.landing-block-card-text' => [
					0 => '<p>An office manager is an employee managing an office. As a rule, this employee is introduced into the state of the central (head)</p>',
					1 => '<p>An office manager is an employee managing an office. As a rule, this employee is introduced into the state of the central (head)</p>',
					],
				'.landing-block-img' => [
					0 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/540x729/img1.jpg',
						],
					1 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/540x729/img2.jpg',
						],
					],
				],
			'style' => [
				'.landing-block-card' => [
					0 => 'landing-block-card col-12 col-md-6 col-lg-6 js-animation g-pt-0 g-pb-0 g-pt-0--lg-down align-self-start landing-card animation-none animated',
					1 => 'landing-block-card col-12 col-md-6 col-lg-6 js-animation g-pt-150 g-pb-0 g-pt-0--lg-down align-self-start landing-card animation-none animated',
					],
				'.landing-block-text-container' => [
					0 => 'landing-block-text-container g-flex-centered-item--bottom text-left u-bg-overlay__inner g-pl-70 g-pr-70 g-pt-30 g-pb-30',
					1 => 'landing-block-text-container g-flex-centered-item--bottom text-left u-bg-overlay__inner g-pl-70 g-pr-70 g-pt-30 g-pb-30',
					],
				'.landing-block-card-title' => [
					0 => 'landing-block-card-title text-uppercase font-weight-bold g-ma-0 g-font-montserrat g-font-size-40 g-line-height-1_4 g-color-white g-brd-bottom g-brd-4 g-brd-white',
					1 => 'landing-block-card-title text-uppercase font-weight-bold g-ma-0 g-font-montserrat g-font-size-40 g-line-height-1_4 g-color-white g-brd-bottom g-brd-4 g-brd-white',
					],
				'.landing-block-card-text' => [
					0 => 'landing-block-card-text g-font-size-16 g-color-white g-line-height-1_7 g-font-roboto g-pt-10 g-pt-30--md',
					1 => 'landing-block-card-text g-font-size-16 g-color-white g-line-height-1_7 g-font-roboto g-pt-10 g-pt-30--md',
					],
				'.landing-block-img' => [
					0 => 'landing-block-img u-bg-overlay g-flex-centered g-min-height-70vh g-bg-img-hero',
					1 => 'landing-block-img u-bg-overlay g-flex-centered g-min-height-70vh g-bg-img-hero',
					],
				'.landing-block-inner' => [
					0 => 'landing-block-inner row',
					],
				'#wrapper' => [
					0 => 'landing-block g-pt-0 g-pb-0 g-theme-bitrix-bg-dark-v2',
					],
				],
			],
		'#block15049' => [
			'old_id' => 15049,
			'code' => '40.7.one_img_right_text_left',
			'access' => 'X',
			'nodes' => [
				'.landing-block-card-title-left' => [
					0 => 'What are your interests?',
					],
				'.landing-block-card-title-right' => [
					0 => '<p>Office</p>',
					],
				'.landing-block-card-text-left' => [
					0 => '<p>An office manager is an employee managing an office. As a rule, this employee is introduced into the state of the central (head)</p>',
					],
				'.landing-block-card-text-right' => [
					0 => '<p>An office manager is an employee managing an office. As a rule, this employee is introduced into the state of the central (head)</p>',
					],
				'.landing-block-img' => [
					0 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/540x729/img3.jpg',
						],
					],
				'.landing-block-link' => [
					0 => [
						'href' => '#',
						'target' => NULL,
						'attrs' => [
							'data-embed' => NULL,
							'data-url' => NULL,
							],
						'text' => 'Read More &#10140;',
						],
					],
				],
			'style' => [
				'.landing-block-card-title-left' => [
					0 => 'landing-block-card-title-left text-uppercase font-weight-bold g-font-size-50 g-ma-0 g-font-montserrat g-line-height-1_4 g-brd-bottom g-brd-4 g-color-white g-brd-white',
					],
				'.landing-block-card-text-left' => [
					0 => 'landing-block-card-text-left g-font-size-18 g-line-height-1_7 g-font-roboto g-pt-10 g-pt-30--md g-px-0 g-color-white',
					],
				'.landing-block-card-title-right' => [
					0 => 'landing-block-card-title-right text-uppercase font-weight-bold g-ma-0 g-font-montserrat g-font-size-40 g-line-height-1_4 g-color-white g-brd-bottom g-brd-4 g-brd-white',
					],
				'.landing-block-card-text-right' => [
					0 => 'landing-block-card-text-right g-font-size-16 g-color-white g-line-height-1_7 g-font-roboto g-pt-10 g-pt-30--md',
					],
				'.landing-block-text-container' => [
					0 => 'landing-block-text-container js-animation slideInLeft g-px-80--lg align-self-center',
					],
				'.landing-block-container' => [
					0 => 'landing-block-container col-12 col-md-6 col-lg-6 js-animation fadeIn g-pt-0 g-pb-0 align-self-center',
					],
				'.landing-block-text-container-right' => [
					0 => 'landing-block-text-container-right g-flex-centered-item--bottom text-left u-bg-overlay__inner g-pl-70 g-pr-70 g-pt-30 g-pb-30',
					],
				'.landing-block-img' => [
					0 => 'landing-block-img u-bg-overlay g-flex-centered g-min-height-70vh g-bg-img-hero',
					],
				'.landing-block-link' => [
					0 => 'landing-block-link u-link-v5 g-font-size-18 g-font-roboto font-weight-bold g-color-white g-color-white--hover',
					],
				'.landing-block-link-container' => [
					0 => 'landing-block-link-container',
					],
				'#wrapper' => [
					0 => 'landing-block g-pt-30 g-pb-30 g-theme-bitrix-bg-dark-v2',
					],
				],
			],
		'#block15050' => [
			'old_id' => 15050,
			'code' => '40.8.one_img_left_text_right',
			'access' => 'X',
			'nodes' => [
				'.landing-block-card-title-left' => [
					0 => '<p>Office</p>',
					],
				'.landing-block-card-text-left' => [
					0 => '<p>An office manager is an employee managing an office. As a rule, this employee is introduced into the state of the central (head)</p>',
					],
				'.landing-block-card-title-right' => [
					0 => 'What are your interests?',
					],
				'.landing-block-card-text-right' => [
					0 => '<p>An office manager is an employee managing an office. As a rule, this employee is introduced into the state of the central (head)</p>',
					],
				'.landing-block-img' => [
					0 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/540x729/img4.jpg',
						],
					],
				'.landing-block-link' => [
					0 => [
						'href' => '#',
						'target' => NULL,
						'attrs' => [
							'data-embed' => NULL,
							'data-url' => NULL,
							],
						'text' => 'Read More &#10140;',
						],
					],
				],
			'style' => [
				'.landing-block-card-title-left' => [
					0 => 'landing-block-card-title-left text-uppercase font-weight-bold g-ma-0 g-font-montserrat g-font-size-40 g-line-height-1_4 g-color-white g-brd-bottom g-brd-4 g-brd-white',
					],
				'.landing-block-card-title-right' => [
					0 => 'landing-block-card-title-right text-uppercase font-weight-bold g-font-size-50 g-ma-0 g-font-montserrat g-line-height-1_4 g-brd-bottom g-brd-4 g-color-white g-brd-white',
					],
				'.landing-block-card-text-left' => [
					0 => 'landing-block-card-text-left g-font-size-16 g-color-white g-line-height-1_7 g-font-roboto g-pt-10 g-pt-30--md',
					],
				'.landing-block-card-text-right' => [
					0 => 'landing-block-card-text-right g-flex-centered-item--bottom col-11 g-font-size-18 g-line-height-1_7 g-font-roboto g-pt-10 g-pt-30--md g-px-0 g-color-white',
					],
				'.landing-block-text-container-right' => [
					0 => 'landing-block-text-container-right js-animation slideInRight g-px-80--lg align-self-center',
					],
				'.landing-block-container' => [
					0 => 'landing-block-container col-12 col-md-6 col-lg-6 js-animation fadeIn g-pt-0 g-pb-0 align-self-center order-2 order-md-1',
					],
				'.landing-block-text-container' => [
					0 => 'landing-block-text-container g-flex-centered-item--bottom text-left u-bg-overlay__inner g-pl-70 g-pr-70 g-pt-30 g-pb-30',
					],
				'.landing-block-img' => [
					0 => 'landing-block-img u-bg-overlay g-flex-centered g-min-height-70vh g-bg-img-hero',
					],
				'.landing-block-link' => [
					0 => 'landing-block-link u-link-v5 g-font-size-18 g-font-roboto font-weight-bold g-color-white--hover g-color-white',
					],
				'.landing-block-link-container' => [
					0 => 'landing-block-link-container',
					],
				'#wrapper' => [
					0 => 'landing-block g-pt-30 g-pb-30 g-theme-bitrix-bg-dark-v2',
					],
				],
			],
		],
	];