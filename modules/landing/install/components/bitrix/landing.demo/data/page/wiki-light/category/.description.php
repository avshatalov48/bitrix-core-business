<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'old_id' => '229',
	'code' => 'wiki-light/category',
	//'name' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_CATEGORY_TITLE"),
	'description' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_CATEGORY_DESCRIPTION"),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'N',
	'type' => ['knowledge', 'group'],
	'version' => 3,
	'fields' => [
		'TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_CATEGORY_TITLE"),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => '3corporate',
			'THEME_CODE_TYPO' => 'app',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'METAMAIN_USE' => 'N',
			'METAMAIN_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_CATEGORY_TITLE"),
			'METAMAIN_DESCRIPTION' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_CATEGORY_DESCRIPTION"),
			'METAOG_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_CATEGORY_TITLE"),
			'METAOG_DESCRIPTION' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_CATEGORY_DESCRIPTION"),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/wiki-light/category/preview.jpg',
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
		'#block3524' => [
			'old_id' => 3524,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<span>Tasks and Projects<br /></span>',
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pt-50 g-pb-auto',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 container text-left g-font-montserrat font-weight-bold g-font-size-30',
				],
			],
		],
		'#block3525' => [
			'old_id' => 3525,
			'code' => '59.2.search_sidebar',
			'access' => 'X',
			'nodes' => [
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-pt-15 g-pb-15 g-pl-15 g-pr-15',
				],
			],
		],
		'#block3526' => [
			'old_id' => 3526,
			'code' => '27.2.one_col_full_title',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '<span style="font-weight: 700;">New posts<br /></span>',
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pl-0 g-pb-auto container g-max-width-container g-pt-50',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 text-left g-font-montserrat g-font-size-30',
				],
			],
		],
		'#block3527' => [
			'old_id' => 3527,
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
					0 => 'landing-block-node-text g-color-black-opacity-0_3',
					1 => 'landing-block-node-text g-color-black-opacity-0_3',
					2 => 'landing-block-node-text g-color-black-opacity-0_3',
				],
				'.landing-block-node-button' => [
					0 => 'landing-block-node-button btn u-btn-outline-primary g-font-size-11 text-uppercase',
					1 => 'landing-block-node-button btn u-btn-outline-primary g-font-size-11 text-uppercase',
					2 => 'landing-block-node-button btn u-btn-outline-primary g-font-size-11 text-uppercase',
				],
			],
		],
		'#block3528' => [
			'old_id' => 3528,
			'code' => '27.2.one_col_full_title',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '<span style="font-weight: 700;">Other posts<br /></span>',
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pl-0 g-pb-auto container g-max-width-container g-pt-50',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 text-left g-font-montserrat g-font-size-30',
				],
			],
		],
		'#block3529' => [
			'old_id' => 3529,
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
					0 => 'landing-block-node-text g-color-black-opacity-0_3',
					1 => 'landing-block-node-text g-color-black-opacity-0_3',
					2 => 'landing-block-node-text g-color-black-opacity-0_3',
				],
				'.landing-block-node-button' => [
					0 => 'landing-block-node-button btn u-btn-outline-primary g-font-size-11 text-uppercase',
					1 => 'landing-block-node-button btn u-btn-outline-primary g-font-size-11 text-uppercase',
					2 => 'landing-block-node-button btn u-btn-outline-primary g-font-size-11 text-uppercase',
				],
			],
		],
	],
];
