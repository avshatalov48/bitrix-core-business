<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'old_id' => '227',
	'parent' => 'wiki',
	'code' => 'wiki/main',
	'name' => Loc::getMessage("LANDING_DEMO_WIKI_MAIN-TITLE"),
	'description' => Loc::getMessage("LANDING_DEMO_WIKI-DESCRIPTION"),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'N',
	'active' => false,
	'type' => ['knowledge', 'group'],
	'version' => 3,
	'fields' => [
		'TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_MAIN-TITLE"),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => '3corporate',
			'THEME_CODE_TYPO' => 'app',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'METAMAIN_USE' => 'N',
			'METAMAIN_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_TITLE"),
			'METAMAIN_DESCRIPTION' => Loc::getMessage("LANDING_DEMO_WIKI_MAIN-DESCRIPTION"),
			'METAOG_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_TITLE"),
			'METAOG_DESCRIPTION' => Loc::getMessage("LANDING_DEMO_WIKI_MAIN-DESCRIPTION"),
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
		'#block3354' => [
			'old_id' => 3354,
			'code' => '59.1.search',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-bgimage' => [
					0 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img2.jpg',
					],
				],
				'.landing-block-node-title' => [
					0 => '<span bxstyle="font-weight: 700;">How can we help you?</span>',
				],
				'.landing-block-node-text' => [
					0 => '<p>Ask a question or search any keyword</p>',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title text-uppercase g-font-weight-300 g-mb-30 g-color-white font-weight-bold g-font-size-46 g-font-montserrat',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text form-text g-opacity-0_8 g-font-size-14 g-color-white g-font-open-sans',
				],
				'#wrapper' => [
					0 => 'landing-block landing-block-node-bgimage g-flex-centered u-bg-overlay g-bg-img-hero g-bg-darkblue-opacity-0_7--after g-mt-auto g-pb-25 g-pl-auto g-pr-auto g-pt-6 g-min-height-50vh',
				],
			],
		],
		'#block3349' => [
			'old_id' => 3349,
			'code' => '27.3.one_col_fix_title',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '<span bxstyle="font-weight: 700;">Categories<br /></span>',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 text-center g-font-montserrat g-font-size-50 container g-pl-0 g-pr-0',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp text-center g-pt-30 g-pb-4',
				],
			],
		],
		'#block3353' => [
			'old_id' => 3353,
			'code' => '08.5.three_col_text_with_colorstrip_top',
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
				'.landing-block-node-title' => [
					0 => '<p><span bxstyle="font-weight: bold;">Getting started</span></p>',
					1 => '<span bxstyle="font-weight: bold;">Create content</span>',
					2 => '<p><span bxstyle="font-weight: bold;">Navigation bar</span></p>',
					3 => '<span bxstyle="font-weight: bold;">FAQ</span>',
					4 => '<p><span bxstyle="font-weight: bold;">Sources</span></p>',
					5 => '<p><span bxstyle="font-weight: bold;">About tags</span></p>',
				],
				'.landing-block-node-text' => [
					0 => '<p>Proin dignissim eget enim id aliquam. Proin ornare dictum leo</p>',
					1 => '<p>Nteger commodo est id erat bibendum, eu convallis dolor tempus. </p>',
					2 => '<p>Aenean lobortis ante ac porttitor eleifend. Morbi massa justo, gravida </p>',
					3 => '<p>Proin dignissim eget enim id aliquam. Proin ornare dictum leo</p>',
					4 => '<p>Nteger commodo est id erat bibendum, eu convallis dolor tempus. </p>',
					5 => '<p>Aenean lobortis ante ac porttitor eleifend. Morbi massa justo, gravida </p>',
				],
				'.landing-block-node-info' => [
					0 => '<p>By: Alex / In: Web Trends / Posted: Aug 25, 2016</p>',
					1 => '<p>By: Tom / In: Tech / Posted: Aug 24, 2016</p>',
					2 => '<p>By: Kate / In: Startups / Posted: Aug 24, 2016</p>',
					3 => '<p>By: Alex / In: Web Trends / Posted: Aug 25, 2016</p>',
					4 => '<p>By: Tom / In: Tech / Posted: Aug 24, 2016</p>',
					5 => '<p>By: Kate / In: Startups / Posted: Aug 24, 2016</p>',
				],
			],
			'style' => [
				'.landing-block-card' => [
					0 => 'landing-block-card js-animation fadeIn g-mp-5 landing-card g-mb-30 col-lg-4',
					1 => 'landing-block-card js-animation fadeIn g-mp-5 landing-card g-mb-30 col-lg-4',
					2 => 'landing-block-card js-animation fadeIn g-mp-5 landing-card g-mb-30 col-lg-4',
					3 => 'landing-block-card js-animation fadeIn g-mp-5 landing-card g-mb-30 col-lg-4',
					4 => 'landing-block-card js-animation fadeIn g-mp-5 landing-card g-mb-30 col-lg-4',
					5 => 'landing-block-card js-animation fadeIn g-mp-5 landing-card g-mb-30 col-lg-4',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title u-heading-v2__title g-font-weight-300 g-color-black-opacity-0_9 g-font-size-20 g-font-montserrat',
					1 => 'landing-block-node-title u-heading-v2__title g-font-weight-300 g-color-black-opacity-0_9 g-font-size-20 g-font-montserrat',
					2 => 'landing-block-node-title u-heading-v2__title g-font-weight-300 g-color-black-opacity-0_9 g-font-size-20 g-font-montserrat',
					3 => 'landing-block-node-title u-heading-v2__title g-font-weight-300 g-color-black-opacity-0_9 g-font-size-20 g-font-montserrat',
					4 => 'landing-block-node-title u-heading-v2__title g-font-weight-300 g-color-black-opacity-0_9 g-font-size-20 g-font-montserrat',
					5 => 'landing-block-node-title u-heading-v2__title g-font-weight-300 g-color-black-opacity-0_9 g-font-size-20 g-font-montserrat',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text lead g-font-size-14 g-color-main g-font-montserrat',
					1 => 'landing-block-node-text lead g-font-size-14 g-color-main g-font-montserrat',
					2 => 'landing-block-node-text lead g-font-size-14 g-color-main g-font-montserrat',
					3 => 'landing-block-node-text lead g-font-size-14 g-color-main g-font-montserrat',
					4 => 'landing-block-node-text lead g-font-size-14 g-color-main g-font-montserrat',
					5 => 'landing-block-node-text lead g-font-size-14 g-color-main g-font-montserrat',
				],
				'.landing-block-node-info' => [
					0 => 'landing-block-node-info g-font-size-12 g-font-montserrat g-theme-event-color-gray-dark-v1',
					1 => 'landing-block-node-info g-font-size-12 g-font-montserrat g-theme-event-color-gray-dark-v1',
					2 => 'landing-block-node-info g-font-size-12 g-font-montserrat g-theme-event-color-gray-dark-v1',
					3 => 'landing-block-node-info g-font-size-12 g-font-montserrat g-theme-event-color-gray-dark-v1',
					4 => 'landing-block-node-info g-font-size-12 g-font-montserrat g-theme-event-color-gray-dark-v1',
					5 => 'landing-block-node-info g-font-size-12 g-font-montserrat g-theme-event-color-gray-dark-v1',
				],
				'.landing-block-node-card-container' => [
					0 => 'landing-block-node-card-container g-brd-around g-brd-top-2 g-pa-25 g-pb-11 g-brd-primary-top g-brd-black-opacity-0_1 g-rounded-4',
					1 => 'landing-block-node-card-container g-brd-around g-brd-top-2 g-pa-25 g-pb-11 g-brd-primary-top g-brd-black-opacity-0_1 g-rounded-4',
					2 => 'landing-block-node-card-container g-brd-around g-brd-top-2 g-pa-25 g-pb-11 g-brd-primary-top g-brd-black-opacity-0_1 g-rounded-4',
					3 => 'landing-block-node-card-container g-brd-around g-brd-top-2 g-pa-25 g-pb-11 g-brd-primary-top g-brd-black-opacity-0_1 g-rounded-4',
					4 => 'landing-block-node-card-container g-brd-around g-brd-top-2 g-pa-25 g-pb-11 g-brd-primary-top g-brd-black-opacity-0_1 g-rounded-4',
					5 => 'landing-block-node-card-container g-brd-around g-brd-top-2 g-pa-25 g-pb-11 g-brd-primary-top g-brd-black-opacity-0_1 g-rounded-4',
				],
				'#wrapper' => [
					0 => 'landing-block g-pb-9 g-pt-30 g-pl-auto',
				],
			],
		],
		'#block3419' => [
			'old_id' => 3419,
			'code' => '27.2.one_col_full_title',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '<span bxstyle="font-weight: bold;">New information</span>',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 g-font-size-48 text-left g-font-montserrat container g-max-width-100x g-pl-0 g-pr-0',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pt-20 g-pb-auto text-center g-pl-7 g-pr-15',
				],
			],
		],
		'#block3350' => [
			'old_id' => 3350,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>Maecenas ut mauris risus. Quisque mi urna, mattis id varius nec, convallis eu odio. Integer eu malesuada leo, placerat semper neque. Nullam id volutpat dui, quis luctus magna. Suspendisse rutrum ipsum in quam semper laoreet. Praesent dictum nulla id viverra vehicula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas ac nulla vehicula risus pulvinar feugiat ullamcorper sit amet mi. Aliquam mattis neque justo, non maximus dui ornare nec. Praesent efficitur velit nisl, sed tincidunt mi imperdiet at. Cras urna libero, fringilla vitae luctus eu, egestas eget metus. Nam et massa eros. Maecenas sit amet lacinia lectus. Nam et nulla rutrum, dignissim eros quis, dictum eros. In ullamcorper molestie neque, ac faucibus felis efficitur sed. Nam et tristique nisi. Cras iaculis venenatis libero. Suspendisse fermentum, ipsum ac facilisis elementu. Praesent dictum nulla id viverra vehicula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas ac nulla vehicula risus pulvinar feugiat ullamcorper sit amet mi. Aliquam mattis neque justo, non maximus dui ornare nec. <br /><br />Maecenas ut mauris risus. Quisque mi urna, mattis id varius nec, convallis eu odio. Integer eu malesuada leo, placerat semper neque. Nullam id volutpat dui, quis luctus magna. Suspendisse rutrum ipsum in quam semper laoreet. Praesent dictum nulla id viverra vehicula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas ac nulla vehicula risus pulvinar feugiat ullamcorper sit amet mi.<br /><br />Aliquam mattis neque justo, non maximus dui ornare nec. Praesent efficitur velit nisl, sed tincidunt mi imperdiet at. Cras urna libero, fringilla vitae luctus eu, egestas eget metus. Nam et massa eros. Maecenas sit amet lacinia lectus.<br /><br />Nam et nulla rutrum, dignissim eros quis, dictum eros. In ullamcorper molestie neque, ac faucibus felis efficitur sed. Nam et tristique nisi. Cras iaculis venenatis libero. Suspendisse fermentum, ipsum ac facilisis elementu. Aliquam mattis neque justo, non maximus dui ornare nec. Praesent efficitur velit nisl, sed tincidunt mi imperdiet at. Cras urna libero, fringilla vitae luctus eu, egestas eget metus. Nam et massa eros. Maecenas sit amet lacinia lectus.<br /></p>',
				],
			],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 container g-pa-0 g-max-width-100x text-left g-font-open-sans g-color-black-opacity-0_8 g-font-size-16',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pt-50 g-pb-50 g-pl-7 g-pr-15',
				],
			],
		],
		'#block3418' => [
			'old_id' => 3418,
			'code' => '32.14.img_grid_no_gallery_1x2',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-img-title' => [
					0 => 'Analytics',
					1 => 'Team building',
					2 => 'Work Process',
				],
			],
			'style' => [
				'.landing-block-node-img-container-right-top' => [
					0 => 'landing-block-node-img-container landing-block-node-img-container-right-top js-animation fadeInRight h-100 g-pos-rel g-parent u-block-hover g-rounded-5',
				],
				'.landing-block-node-img-container-right-bottom' => [
					0 => 'landing-block-node-img-container landing-block-node-img-container-right-bottom js-animation fadeInRight h-100 g-pos-rel g-parent u-block-hover g-rounded-5',
				],
				'.landing-block-node-img-container-left' => [
					0 => 'landing-block-node-img-container landing-block-node-img-container-left js-animation fadeInLeft h-100 g-pos-rel g-parent u-block-hover g-rounded-5',
				],
				'.landing-block-node-img-title' => [
					0 => 'landing-block-node-img-title text-center h3 w-100 g-color-white g-line-height-1_4 g-letter-spacing-5 g-font-size-20 g-pointer-events-all g-font-montserrat',
					1 => 'landing-block-node-img-title text-center h3 w-100 g-color-white g-line-height-1_4 g-letter-spacing-5 g-font-size-20 g-pointer-events-all g-font-montserrat',
					2 => 'landing-block-node-img-title text-center h3 w-100 g-color-white g-line-height-1_4 g-letter-spacing-5 g-font-size-20 g-pointer-events-all g-font-montserrat',
				],
			],
		],
		'#block3421' => [
			'old_id' => 3421,
			'code' => '27.2.one_col_full_title',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '<span bxstyle="font-weight: 700;">Title H1</span>',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 g-font-size-48 text-left g-font-montserrat container g-max-width-100x g-pl-0 g-pr-0',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp text-center g-pt-20 g-pb-auto g-pl-7 g-pr-15',
				],
			],
		],
		'#block3422' => [
			'old_id' => 3422,
			'code' => '27.2.one_col_full_title',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '<span bxstyle="font-weight: 700;">Title H2</span>',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 text-left g-font-montserrat g-font-size-38 container g-max-width-100x g-pl-0 g-pr-0',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pt-20 g-pb-auto text-center g-pl-7 g-pr-15',
				],
			],
		],
		'#block3423' => [
			'old_id' => 3423,
			'code' => '27.2.one_col_full_title',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '<span bxstyle="font-weight: 700;">Title H3</span>',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 text-left g-font-montserrat g-font-size-30 container g-max-width-100x g-pl-0 g-pr-0',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pt-20 g-pb-auto text-center g-pl-7 g-pr-15',
				],
			],
		],
		'#block3424' => [
			'old_id' => 3424,
			'code' => '27.2.one_col_full_title',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '<span bxstyle="font-weight: 700;">Title H4<br /><br /></span>',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 text-left g-font-montserrat g-font-size-22 container g-max-width-100x g-pl-0 g-pr-0',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pb-auto g-pt-25 text-center g-pl-7 g-pr-15',
				],
			],
		],
		'#block3425' => [
			'old_id' => 3425,
			'code' => '27.2.one_col_full_title',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '<span bxstyle="font-weight: 700;">Title H5<br /><br /></span>',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 text-left g-font-montserrat g-font-size-17 container g-max-width-100x g-pl-0 g-pr-0',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pb-auto g-pt-auto text-center g-pl-7 g-pr-15',
				],
			],
		],
		'#block3420' => [
			'old_id' => 3420,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>Main text. Maecenas ut mauris risus. Quisque mi urna, mattis id varius nec, convallis eu odio. Integer eu malesuada leo, placerat semper neque. Nullam id volutpat dui, quis luctus magna. Suspendisse rutrum ipsum in quam semper laoreet. Praesent dictum nulla id viverra vehicula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas ac nulla vehicula risus pulvinar feugiat ullamcorper sit amet mi. Aliquam mattis neque justo, non maximus dui ornare nec. Praesent efficitur velit nisl, sed tincidunt mi imperdiet at. Cras urna libero, fringilla vitae luctus eu, egestas eget metus. Nam et massa eros. Maecenas sit amet lacinia lectus. Nam et nulla rutrum, dignissim eros quis, dictum eros. In ullamcorper molestie neque, ac faucibus felis efficitur sed. Nam et tristique nisi. Cras iaculis venenatis libero. Suspendisse fermentum, ipsum ac facilisis elementu. Praesent dictum nulla id viverra vehicula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas ac nulla vehicula risus pulvinar feugiat ullamcorper sit amet mi. Aliquam mattis neque justo, non maximus dui ornare nec. <br /></p>',
				],
			],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 container g-pa-0 text-left g-font-open-sans g-color-black-opacity-0_8 g-font-size-16 g-max-width-100x',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pt-4 g-pb-20 g-pl-auto g-pl-7 g-pr-15',
				],
			],
		],
		'#block3426' => [
			'old_id' => 3426,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>Additional text. Maecenas ut mauris risus. Quisque mi urna, mattis id varius nec, convallis eu odio. Integer eu malesuada leo, placerat semper neque. Nullam id volutpat dui, quis luctus magna. Suspendisse rutrum ipsum in quam semper laoreet. Praesent dictum nulla id viverra vehicula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas ac nulla vehicula risus pulvinar feugiat ullamcorper sit amet mi. Aliquam mattis neque justo, non maximus dui ornare nec. Praesent efficitur velit nisl, sed tincidunt mi imperdiet at. Cras urna libero, fringilla vitae luctus eu, egestas eget metus. Nam et massa eros. Maecenas sit amet lacinia lectus. Nam et nulla rutrum, dignissim eros quis, dictum eros. In ullamcorper molestie neque, ac faucibus felis efficitur sed. Nam et tristique nisi. Cras iaculis venenatis libero. Suspendisse fermentum, ipsum ac facilisis elementu. Praesent dictum nulla id viverra vehicula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas ac nulla vehicula risus pulvinar feugiat ullamcorper sit amet mi. Aliquam mattis neque justo, non maximus dui ornare nec. <br /></p>',
				],
			],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 container g-pa-0 text-left g-font-open-sans g-font-size-14 g-color-black-opacity-0_6 g-max-width-100x',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pb-50 g-pt-auto g-pl-auto g-pl-7 g-pr-15',
				],
			],
		],
	],
];
