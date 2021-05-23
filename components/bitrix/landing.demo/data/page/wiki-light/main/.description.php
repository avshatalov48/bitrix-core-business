<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'old_id' => '227',
	'parent' => 'wiki-light',
	'code' => 'wiki-light/main',
	'name' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_MAIN_TITLE"),
	'description' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_DESCRIPTION"),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'N',
	'active' => false,
	'type' => ['knowledge', 'group'],
	'version' => 3,
	'fields' => [
		'TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_TITLE"),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => '3corporate',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'METAMAIN_USE' => 'N',
			'METAMAIN_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_TITLE"),
			'METAMAIN_DESCRIPTION' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_MAIN_DESCRIPTION"),
			'METAOG_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_TITLE"),
			'METAOG_DESCRIPTION' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_MAIN_DESCRIPTION"),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/wiki-light/main/preview.jpg',
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
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1080/img16.jpg',
					],
				],
				'.landing-block-node-title' => [
					0 => '<span bxstyle="font-weight: 700;">Search in knowledge base</span>',
				],
				'.landing-block-node-text' => [
					0 => '',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-300 g-color-white g-font-weight-700 g-font-size-25 g-mb-20 g-text-transform-none',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text form-text g-opacity-0_8 g-color-white',
				],
				'#wrapper' => [
					0 => 'landing-block landing-block-node-bgimage g-flex-centered u-bg-overlay g-bg-img-hero g-bg-darkblue-opacity-0_7--after g-mt-auto g-pb-25 g-pl-auto g-pr-auto g-pt-6 g-min-height-80vh',
				],
			],
		],
		'#block3349' => [
			'old_id' => 3349,
			'code' => '27.3.one_col_fix_title',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '<span bxstyle="font-weight: 700;">New posts<br /></span>',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 g-font-size-33 text-left',
				],
				'#wrapper' => [
					0 => 'landing-block container js-animation fadeInUp g-pb-20 g-pt-80',
				],
			],
		],
		'#block3353' => [
			'old_id' => 3353,
			'code' => '58.3.news_sidebar_3',
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
					0 => 'Send messages from the Activity Stream',
					1 => 'Record video and post it to the Activity Stream',
					2 => 'Emoji in Bitrix24',
				],
				'.landing-block-node-text' => [
					0 => '<p>The Activity Stream allows users to be aware of all the current activity with pertains to them, such as new messages, comments, files, workflows or tasks notifications, etc.</p>',
					1 => '<p>You can record video announcements right in Bitrix24 Activity Stream & share it with your team.</p>',
					2 => '<p>Emoji are ideograms used in electronic messages and web pages to express an idea or an emotion.</p>',
				],
				'.landing-block-node-button' => [
					0 => [
						'href' => '#landing229',
					],
					1 => [
						'href' => '#landing229',
					],
					2 => [
						'href' => '#landing229',
					],
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-pt-40 g-pb-40 g-pl-5',
				],
				'.landing-block-card' => [
					0 => 'landing-block-card media g-mb-35 g-mb-0--last js-animation fadeInLeft landing-card',
					1 => 'landing-block-card media g-mb-35 g-mb-0--last js-animation fadeInLeft landing-card',
					2 => 'landing-block-card media g-mb-35 g-mb-0--last js-animation fadeInLeft landing-card',
				],
				'.landing-block-node-img' => [
					0 => 'landing-block-node-img d-flex u-shadow-v25 g-width-40 g-height-40 g-rounded-50x g-object-fit-cover mr-3',
					1 => 'landing-block-node-img d-flex u-shadow-v25 g-width-40 g-height-40 g-rounded-50x g-object-fit-cover mr-3',
					2 => 'landing-block-node-img d-flex u-shadow-v25 g-width-40 g-height-40 g-rounded-50x g-object-fit-cover mr-3',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-size-20 g-color-blue',
					1 => 'landing-block-node-title g-font-size-20 g-color-blue',
					2 => 'landing-block-node-title g-font-size-20 g-color-blue',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-color-black',
					1 => 'landing-block-node-text g-color-black',
					2 => 'landing-block-node-text g-color-black',
				],
				'.landing-block-node-button' => [
					0 => 'landing-block-node-button btn g-btn-type-outline g-btn-size-sm g-btn-px-m g-btn-primary rounded-0 text-uppercase',
					1 => 'landing-block-node-button btn g-btn-type-outline g-btn-size-sm g-btn-px-m g-btn-primary rounded-0 text-uppercase',
					2 => 'landing-block-node-button btn g-btn-type-outline g-btn-size-sm g-btn-px-m g-btn-primary rounded-0 text-uppercase',
				],
			],
		],
		'#block3351' => [
			'old_id' => 3351,
			'code' => '27.3.one_col_fix_title',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '<span bxstyle="font-weight: 700;">Themes<br /></span>',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 g-font-size-33 text-left',
				],
				'#wrapper' => [
					0 => 'landing-block container js-animation fadeInUp g-pb-20 g-pt-80',
				],
			],
		],
		'#block3418' => [
			'old_id' => 3418,
			'code' => '32.10.img_grid_2cols_3',
			'nodes' => [
				'.landing-block-node-img' => [
					0 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1080x1440/img1.jpg',
					],
					1 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1080x715/img1.jpg',
					],
				],
			],
		],
		'#block3419' => [
			'old_id' => 3419,
			'code' => '32.10.img_grid_2cols_3',
			'nodes' => [
				'.landing-block-node-img' => [
					0 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1080x720/img1.jpg',
					],
					1 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1080x811/img1.jpg',
					],
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
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 g-font-size-48 text-left container g-max-width-100x g-pl-0 g-pr-0',
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
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 text-left g-font-size-38 container g-max-width-100x g-pl-0 g-pr-0',
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
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 text-left g-font-size-30 container g-max-width-100x g-pl-0 g-pr-0',
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
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 text-left g-font-size-22 container g-max-width-100x g-pl-0 g-pr-0',
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
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 text-left g-font-size-17 container g-max-width-100x g-pl-0 g-pr-0',
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
					0 => 'landing-block-node-text g-pb-1 container g-pa-0 text-left g-color-black g-font-size-16 g-max-width-100x',
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
					0 => '<p>* Additional text. Maecenas ut mauris risus. Quisque mi urna, mattis id varius nec, convallis eu odio. Integer eu malesuada leo, placerat semper neque. Nullam id volutpat dui, quis luctus magna. Suspendisse rutrum ipsum in quam semper laoreet. Praesent dictum nulla id viverra vehicula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas ac nulla vehicula risus pulvinar feugiat ullamcorper sit amet mi. Aliquam mattis neque justo, non maximus dui ornare nec. Praesent efficitur velit nisl, sed tincidunt mi imperdiet at. Cras urna libero, fringilla vitae luctus eu, egestas eget metus. Nam et massa eros. Maecenas sit amet lacinia lectus. Nam et nulla rutrum, dignissim eros quis, dictum eros. In ullamcorper molestie neque, ac faucibus felis efficitur sed. Nam et tristique nisi. Cras iaculis venenatis libero. Suspendisse fermentum, ipsum ac facilisis elementu. Praesent dictum nulla id viverra vehicula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas ac nulla vehicula risus pulvinar feugiat ullamcorper sit amet mi. Aliquam mattis neque justo, non maximus dui ornare nec. <br /></p>',
				],
			],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 container g-pa-0 text-left g-color-black g-max-width-100x',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pb-50 g-pt-auto g-pl-auto g-pl-7 g-pr-15',
				],
			],
		],
	],
];
