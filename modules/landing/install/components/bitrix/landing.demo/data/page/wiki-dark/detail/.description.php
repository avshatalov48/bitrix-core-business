<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'old_id' => '1239',
	'code' => 'wiki-dark/detail',
	//'name' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_DETAIL_TITLE"),
	'description' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_DETAIL_DESCRIPTION"),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'N',
	'type' => ['knowledge', 'group'],
	'version' => 3,
	'fields' => [
		'TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_DETAIL_TITLE"),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'METAROBOTS_INDEX' => 'Y',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => '2business',
			'PIXELVK_USE' => 'N',
			'PIXELFB_USE' => 'N',
			'METAOG_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_DETAIL_TITLE"),
			'METAOG_DESCRIPTION' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_DETAIL_DESCRIPTION"),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/empty/preview.jpg',
			'BACKGROUND_USE' => 'N',
			'BACKGROUND_POSITION' => 'center',
			'METAMAIN_USE' => 'N',
			'METAMAIN_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_DETAIL_TITLE"),
			'METAMAIN_DESCRIPTION' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_DETAIL_DESCRIPTION"),
			'GTM_USE' => 'N',
			'GACOUNTER_USE' => 'N',
			'GACOUNTER_SEND_CLICK' => 'N',
			'GACOUNTER_CLICK_TYPE' => 'text',
			'GACOUNTER_SEND_SHOW' => 'N',
			'YACOUNTER_USE' => 'N',
			'HEADBLOCK_USE' => 'N',
			'CSSBLOCK_USE' => 'N',
			],
		],
	'layout' => [],
	'items' => [
		'#block15122' => [
			'old_id' => 15122,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>Office</p>',
					],
				],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 container g-max-width-container g-font-size-76 text-left g-font-montserrat font-weight-bold g-color-white',
					],
				'#wrapper' => [
					0 => 'landing-block js-animation g-pb-auto g-pt-15 animation-none animated g-theme-bitrix-bg-dark-v2',
					],
				],
			],
		'#block15123' => [
			'old_id' => 15123,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
					'.landing-block-node-text' => [
							0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p>',
							],
					],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-font-size-16 g-pb-1 container g-max-width-container text-left g-color-gray-light-v3 g-font-roboto g-pr-150',
					],
				'#wrapper' => [
					0 => 'landing-block js-animation g-pt-auto g-pb-auto animation-none animated g-theme-bitrix-bg-dark-v2',
					],
				],
			],
		'#block15124' => [
			'old_id' => 15124,
			'code' => '58.1.news_sidebar_1',
			'access' => 'X',
			'cards' => [
				'.landing-block-card' => [
					'source' => [
						0 => [
							'value' => 0,
							'type' => 'card',
							],
						],
					],
				],
			'nodes' => [
				'.landing-block-node-title' => [
					0 => 'Julia Exon',
					],
				'.landing-block-node-subtitle' => [
					0 => '<p>January 16, 2020</p>',
					],
				'.landing-block-node-img' => [
					0 => [
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img1.jpg',
						],
					],
				],
			'style' => [
				'.landing-block-card' => [
					0 => 'landing-block-card js-animation fadeIn media g-mb-0--last landing-card g-mb-50',
					],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-roboto g-theme-bitrix-color-v4 g-font-size-24',
					],
				'.landing-block-node-subtitle' => [
					0 => 'landing-block-node-subtitle g-font-roboto g-font-size-12 g-color-gray-light-v4 g-font-size-16',
					],
				'.landing-block-node-img' => [
					0 => 'landing-block-node-img g-width-60 g-height-60 g-object-fit-cover g-rounded-50x',
					],
				'#wrapper' => [
					0 => 'landing-block g-pb-10 g-pl-5 g-pr-5 g-pt-50 g-pb-10 g-theme-bitrix-bg-dark-v2',
					],
				],
			],
		'#block15133' => [
			'old_id' => 15133,
			'code' => '26.separator',
			'access' => 'X',
			'nodes' => [
			],
			'style' => [
				'.landing-block-line' => [
					0 => 'landing-block-line container g-brd-gray-dark-v2 my-0',
				],
				'#wrapper' => [
					0 => 'landing-block g-theme-bitrix-bg-dark-v2 g-pt-10 g-pb-10',
				],
			],
		],
		'#block15125' => [
			'old_id' => 15125,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p>',
					],
				],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 container g-color-white g-max-width-container text-left g-font-size-35 g-font-roboto g-line-height-1_3',
					],
				'#wrapper' => [
					0 => 'landing-block js-animation g-pt-40 g-pb-10 animation-none animated g-theme-bitrix-bg-dark-v2',
					],
				],
			],
		'#block15126' => [
			'old_id' => 15126,
			'code' => '40.5.slider_blocks_with_img_and_description',
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
						4 => [
							'value' => 0,
							'type' => 'card',
							],
						5 => [
							'value' => 0,
							'type' => 'card',
							],
						],
					],
				],
			'nodes' => [
				'.landing-block-card-img' => [
					0 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1230x736/img1.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
						],
					1 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1230x736/img2.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
						],
					2 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1230x736/img1.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
						],
					3 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1230x736/img2.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
						],
					4 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1230x736/img1.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
						],
					5 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1230x736/img2.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
						],
					],
				'.landing-block-card-text' => [
					0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem.</p>',
					1 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem.</p>',
					2 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem.</p>',
					3 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem.</p>',
					4 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem.</p>',
					5 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem.</p>',
					],
				],
			'style' => [
				'.landing-block-card-text' => [
					0 => 'landing-block-card-text g-font-size-17 text-center g-mt-30 g-mb-0 g-color-white',
					1 => 'landing-block-card-text g-font-size-17 text-center g-mt-30 g-mb-0 g-color-white',
					2 => 'landing-block-card-text g-font-size-17 text-center g-mt-30 g-mb-0 g-color-white',
					3 => 'landing-block-card-text g-font-size-17 text-center g-mt-30 g-mb-0 g-color-white',
					4 => 'landing-block-card-text g-font-size-17 text-center g-mt-30 g-mb-0 g-color-white',
					5 => 'landing-block-card-text g-font-size-17 text-center g-mt-30 g-mb-0 g-color-white',
					],
				'.landing-block-border' => [
					0 => 'landing-block-border g-brd-bottom g-brd-black g-width-80 g-ml-auto g-mr-auto g-brd-0 g-mt-auto g-mb-auto',
					1 => 'landing-block-border g-brd-bottom g-brd-black g-width-80 g-ml-auto g-mr-auto g-brd-0 g-mt-auto g-mb-auto',
					2 => 'landing-block-border g-brd-bottom g-brd-black g-width-80 g-ml-auto g-mr-auto g-brd-0 g-mt-auto g-mb-auto',
					3 => 'landing-block-border g-brd-bottom g-brd-black g-width-80 g-ml-auto g-mr-auto g-brd-0 g-mt-auto g-mb-auto',
					4 => 'landing-block-border g-brd-bottom g-brd-black g-width-80 g-ml-auto g-mr-auto g-brd-0 g-mt-auto g-mb-auto',
					5 => 'landing-block-border g-brd-bottom g-brd-black g-width-80 g-ml-auto g-mr-auto g-brd-0 g-mt-auto g-mb-auto',
					],
				'.landing-block-card' => [
					0 => 'landing-block-card js-slide js-animation fadeIn slick-slide landing-card slick-current slick-active',
					1 => 'landing-block-card js-slide js-animation fadeIn slick-slide landing-card slick-current slick-active',
					2 => 'landing-block-card js-slide js-animation fadeIn slick-slide landing-card slick-current slick-active',
					3 => 'landing-block-card js-slide js-animation fadeIn slick-slide landing-card slick-current slick-active',
					4 => 'landing-block-card js-slide js-animation fadeIn slick-slide landing-card slick-current slick-active',
					5 => 'landing-block-card js-slide js-animation fadeIn slick-slide landing-card slick-current slick-active',
					],
				'.landing-block-card-img' => [
					0 => 'landing-block-card-img g-min-height-184 g-min-height-388--md g-min-height-624--lg g-bg-img-hero g-px-0',
					1 => 'landing-block-card-img g-min-height-184 g-min-height-388--md g-min-height-624--lg g-bg-img-hero g-px-0',
					2 => 'landing-block-card-img g-min-height-184 g-min-height-388--md g-min-height-624--lg g-bg-img-hero g-px-0',
					3 => 'landing-block-card-img g-min-height-184 g-min-height-388--md g-min-height-624--lg g-bg-img-hero g-px-0',
					4 => 'landing-block-card-img g-min-height-184 g-min-height-388--md g-min-height-624--lg g-bg-img-hero g-px-0',
					5 => 'landing-block-card-img g-min-height-184 g-min-height-388--md g-min-height-624--lg g-bg-img-hero g-px-0',
					],
				'#wrapper' => [
					0 => 'landing-block g-pt-20 g-pb-20 g-theme-bitrix-bg-dark-v2',
					],
				],
			],
		'#block15127' => [
			'old_id' => 15127,
			'code' => '31.3.two_cols_text_img_fix',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '',
					],
				'.landing-block-node-text' => [
					0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p>
					<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p>',
					],
				'.landing-block-node-img' => [
					0 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/540x729/img5.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
						],
					],
				],
			'style' => [
				'.landing-block-node-text-container' => [
					0 => 'landing-block-node-text-container js-animation slideInLeft col-md-6 col-lg-6 g-pb-20 g-pb-0--md',
					],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title text-uppercase g-font-weight-700 g-font-size-26 mb-0 g-mb-15',
					],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-font-size-35 g-color-gray-light-v4 g-line-height-1_3 g-pr-10',
					],
				'.landing-block-node-img' => [
					0 => 'landing-block-node-img js-animation slideInRight img-fluid',
					],
				'.landing-block-node-block' => [
					0 => 'row landing-block-node-block align-items-center',
					],
				'#wrapper' => [
					0 => 'landing-block g-pt-30 g-pb-50 g-theme-bitrix-bg-dark-v2',
					],
				],
			],
		'#block15131' => [
			'old_id' => 15131,
			'code' => '32.15.img_one_big_full',
			'access' => 'X',
			'nodes' => [
				'.landing-block-img' => [
					0 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1230x736/img2.jpg',
						],
					],
				'.landing-block-title' => [
					0 => '<p>You can calculate the remaining time and vacation here</p>',
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
				'.landing-block-container' => [
					0 => 'landing-block-container container js-animation zoomIn',
					],
				'.landing-block-img' => [
					0 => 'landing-block-img u-bg-overlay g-flex-centered g-min-height-60vh g-bg-img-hero',
					],
				'.landing-block-text-container' => [
					0 => 'landing-block-text-container align-items-center g-bottom-0 w-100 g-pl-30 g-pr-30 g-pb-20 g-pt-20 u-bg-overlay__inner',
					],
				'.landing-block-title' => [
					0 => 'landing-block-title g-color-white g-font-size-17 g-font-roboto',
					],
				'.landing-block-link' => [
					0 => 'landing-block-link u-link-v5 g-font-size-18 g-font-roboto font-weight-bold g-color-blue g-color-blue--hover',
					],
				'.landing-block-link-container' => [
					0 => 'landing-block-link-container',
					],
				'#wrapper' => [
					0 => 'landing-block g-pt-30 g-pb-30 g-pl-15 g-pr-15 g-theme-bitrix-bg-dark-v2',
					],
				],
			],
		'#block15128' => [
			'old_id' => 15128,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>Office</p>',
					],
				],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 container g-color-white g-font-size-50 g-max-width-container text-left g-font-montserrat font-weight-bold',
					],
				'#wrapper' => [
					0 => 'landing-block js-animation g-pt-50 g-pb-auto animation-none animated g-theme-bitrix-bg-dark-v2',
					],
				],
			],
		'#block15129' => [
			'old_id' => 15129,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p>',
					],
				],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-font-size-16 g-pb-1 container text-left g-max-width-container g-color-gray-light-v1 g-pr-150',
					],
				'#wrapper' => [
					0 => 'landing-block js-animation g-pt-auto g-pb-auto animation-none animated g-theme-bitrix-bg-dark-v2',
					],
				],
			],
		'#block15130' => [
			'old_id' => 15130,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p>
				<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p>
				<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p>
				<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel odio.</p>',
					],
				],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 container g-max-width-container text-left g-font-size-22 g-color-gray-light-v4 g-line-height-1_3',
					],
				'#wrapper' => [
					0 => 'landing-block js-animation g-pb-50 g-pt-40 animation-none animated g-theme-bitrix-bg-dark-v2',
					],
				],
			],
		'#block15132' => [
			'old_id' => 15132,
			'code' => '08.7.list_with_text',
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
				'.landing-block-title' => [
					0 => 'Change in ID for renting a room',
					1 => 'Work schedules',
					2 => 'Knowledge base',
					3 => 'My reports',
					],
				'.landing-block-text' => [
					0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.</p>',
					1 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.</p>',
					2 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.</p>',
					3 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra eros, fringilla porttitor lorem eros vel.</p>',
					],
				'.landing-block-icon' => [
					0 => [
						'classList' => [
							0 => 'landing-block-icon fa fa-check',
							],
						],
					1 => [
						'classList' => [
							0 => 'landing-block-icon fa fa-check',
							],
						],
					2 => [
						'classList' => [
							0 => 'landing-block-icon fa fa-check',
							],
						],
					3 => [
						'classList' => [
							0 => 'landing-block-icon fa fa-check',
							],
						],
					],
				],
			'style' => [
				'.landing-block-container' => [
					0 => 'landing-block-container container',
					],
				'.landing-block-card' => [
					0 => 'landing-block-card row js-animation fadeIn g-mb-10 g-mb-0--last landing-card',
					1 => 'landing-block-card row js-animation fadeIn g-mb-10 g-mb-0--last landing-card',
					2 => 'landing-block-card row js-animation fadeIn g-mb-10 g-mb-0--last landing-card',
					3 => 'landing-block-card row js-animation fadeIn g-mb-10 g-mb-0--last landing-card',
					],
				'.landing-block-title' => [
					0 => 'landing-block-title col-10 col-md-11 text-left g-font-size-22 g-font-roboto font-weight-bold g-ma-0 g-color-gray-light-v4',
					1 => 'landing-block-title col-10 col-md-11 text-left g-font-size-22 g-font-roboto font-weight-bold g-ma-0 g-color-gray-light-v4',
					2 => 'landing-block-title col-10 col-md-11 text-left g-font-size-22 g-font-roboto font-weight-bold g-ma-0 g-color-gray-light-v4',
					3 => 'landing-block-title col-10 col-md-11 text-left g-font-size-22 g-font-roboto font-weight-bold g-ma-0 g-color-gray-light-v4',
					],
				'.landing-block-text' => [
					0 => 'col-10 offset-2 col-md-11 offset-md-1 text-left landing-block-text g-font-size-16 g-font-roboto g-color-gray-light-v1',
					1 => 'col-10 offset-2 col-md-11 offset-md-1 text-left landing-block-text g-font-size-16 g-font-roboto g-color-gray-light-v1',
					2 => 'col-10 offset-2 col-md-11 offset-md-1 text-left landing-block-text g-font-size-16 g-font-roboto g-color-gray-light-v1',
					3 => 'col-10 offset-2 col-md-11 offset-md-1 text-left landing-block-text g-font-size-16 g-font-roboto g-color-gray-light-v1',
					],
				'.landing-block-icon-container' => [
					0 => 'landing-block-icon-container col-2 col-md-1 text-right g-font-size-22 g-color-gray-light-v4',
					1 => 'landing-block-icon-container col-2 col-md-1 text-right g-font-size-22 g-color-gray-light-v4',
					2 => 'landing-block-icon-container col-2 col-md-1 text-right g-font-size-22 g-color-gray-light-v4',
					3 => 'landing-block-icon-container col-2 col-md-1 text-right g-font-size-22 g-color-gray-light-v4',
					],
				'#wrapper' => [
					0 => 'landing-block g-pb-30 g-pt-30 g-theme-bitrix-bg-dark-v2',
					],
				],
			],
		],
];