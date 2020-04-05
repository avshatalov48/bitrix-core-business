<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'old_id' => '228',
	'code' => 'wiki/sidebar',
	'name' => Loc::getMessage("LANDING_DEMO_WIKI_SEDEBAR-TITLE"),
	'description' => null,
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'N',
	'active' => false,
	'type' => ['knowledge', 'group'],
	'version' => 3,
	'fields' => [
		'TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_SEDEBAR-TITLE"),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => '3corporate',
			'THEME_CODE_TYPO' => 'app',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'METAMAIN_USE' => 'N',
			'METAMAIN_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_SEDEBAR-TITLE"),
			'METAMAIN_DESCRIPTION' => '',
			'METAOG_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_SEDEBAR-TITLE"),
			'METAOG_DESCRIPTION' => '',
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/wiki/sidebar/preview.jpg',
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
		'#block3363' => [
			'old_id' => 3363,
			'code' => '35.7.header_logo_and_slogan',
			'access' => 'X',
			'anchor' => 'block3267',
			'nodes' => [
				'.landing-block-node-logo' => [
					0 => [
						'alt' => 'Logo',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/logos/logo-wiki.png',
						'url' => '{"text":"","href":"#system_mainpage","target":"_self","enabled":true}',
					],
				],
				'.landing-block-node-text' => [
					0 => '<span bxstyle="font-style: normal;color: rgb(144, 164, 174);" class="g-font-montserrat">Knowledge base</span>',
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block landing-block-menu g-pl-auto g-pr-auto g-mt-auto g-pt-40 g-pb-40',
				],
			],
		],
		'#block3427' => [
			'old_id' => 3427,
			'code' => '0.menu_25',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-menu' => [
					[
						0 => [
							'text' => 'Main page',
							'href' => '#landing227',
							'target' => '_self',
						],
						1 => [
							'text' => 'Category',
							'href' => '#landing229',
							'target' => '_self',
							'children' => [
								0 => [
									'text' => 'Detail page',
									'href' => '#landing230',
									'target' => '_self',
								],
								1 => [
									'text' => 'Another detail page',
									'href' => '#landing233',
									'target' => '_self',
								],
							]
						],
						2 => [
							'text' => 'Another category',
							'href' => '#landing232',
							'target' => '_self',
						],
					],
				],
			],
			'style' => [
				'.landing-block-node-navbar' => [
					0 => 'landing-block-node-navbar g-font-montserrat g-font-size-14 g-px-15 navbar navbar-expand-md g-brd-0 u-navbar-color-gray-dark-v5 u-navbar-color-primary--hover',
				],
			],
		],
		'#block3361' => [
			'old_id' => 3361,
			'code' => '27.3.one_col_fix_title',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => 'New posts',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 text-left g-color-main g-font-montserrat container g-pl-0 g-pr-0',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation u-block-border u-block-border-margin-sm g-rounded-5 u-block-border-first l-d-xs-none g-mt-25 g-pt-5 animation-none animated g-bg-main text-center',
				],
			],
		],
		'#block3362' => [
			'old_id' => 3362,
			'code' => '58.1.news_sidebar_1',
			'access' => 'X',
			'anchor' => 'block3171',
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
			'dynamic' => [
				'.landing-block-card' => [
					'settings' => [
						'source' => [
							'source' => 'landing:landing',
							'sort' => [
								'by' => 'DATE_CREATE',
								'order' => 'DESC',
							],
						],
						'pagesCount' => '4',
					],
					'references' => [
						'.landing-block-node-title@0' => [
							'id' => 'TITLE',
							'link' => 'true',
						],
						'.landing-block-node-subtitle@0' => [
							'id' => 'DESCRIPTION',
							'link' => 'true',
						],
						'.landing-block-node-img@0' => [
							'id' => 'IMAGE',
							'link' => 'true',
						],
					],
					'source' => 'landing:landing',
				],
			],
			'nodes' => [
				'.landing-block-node-title' => [
					0 => 'Best dessert recipes for breakfast which will..',
					1 => 'Stylish things to do, see and purchase..',
					2 => 'Government plans to test new primary school..',
					3 => 'Top 10 Luxury Hotels - 5 Star Best Luxury Hotels',
				],
				'.landing-block-node-subtitle' => [
					0 => '<p>July 20, 2019</p>',
					1 => '<p>July 16, 2019</p>',
					2 => '<p>July 07, 2019</p>',
					3 => '<p>July 11, 2019</p>',
				],
				'.landing-block-node-img' => [
					0 => [
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img1.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
					],
					1 => [
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img2.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
					],
					2 => [
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img3.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
					],
					3 => [
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/100x100/img4.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
					],
				],
			],
			'style' => [
				'.landing-block-card' => [
					0 => 'landing-block-card js-animation media g-mb-30 g-mb-0--last landing-card animation-none animated',
					1 => 'landing-block-card js-animation media g-mb-30 g-mb-0--last landing-card animation-none animated',
					2 => 'landing-block-card js-animation media g-mb-30 g-mb-0--last landing-card animation-none animated',
					3 => 'landing-block-card js-animation media g-mb-30 g-mb-0--last landing-card animation-none animated',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-open-sans g-color-black-opacity-0_8',
					1 => 'landing-block-node-title g-font-open-sans g-color-black-opacity-0_8',
					2 => 'landing-block-node-title g-font-open-sans g-color-black-opacity-0_8',
					3 => 'landing-block-node-title g-font-open-sans g-color-black-opacity-0_8',
				],
				'.landing-block-node-subtitle' => [
					0 => 'landing-block-node-subtitle g-color-gray-dark-v4 g-font-montserrat g-font-size-11',
					1 => 'landing-block-node-subtitle g-color-gray-dark-v4 g-font-montserrat g-font-size-11',
					2 => 'landing-block-node-subtitle g-color-gray-dark-v4 g-font-montserrat g-font-size-11',
					3 => 'landing-block-node-subtitle g-color-gray-dark-v4 g-font-montserrat g-font-size-11',
				],
				'.landing-block-node-img' => [
					0 => 'landing-block-node-img g-width-60 g-max-height-60 g-object-fit-cover',
					1 => 'landing-block-node-img g-width-60 g-max-height-60 g-object-fit-cover',
					2 => 'landing-block-node-img g-width-60 g-max-height-60 g-object-fit-cover',
					3 => 'landing-block-node-img g-width-60 g-max-height-60 g-object-fit-cover',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-30 g-pb-25 g-pl-5 g-pr-5 u-block-border u-block-border-margin-sm g-rounded-5 u-block-border-end l-d-xs-none g-bg-main',
				],
			],
		],
		'#block3359' => [
			'old_id' => 3359,
			'code' => '27.3.one_col_fix_title',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => 'Popular<br />',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 text-left g-font-montserrat container g-pl-0 g-pr-0',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation u-block-border-first g-rounded-5 u-block-border u-block-border-margin-sm l-d-xs-none g-mt-25 g-pt-5 animation-none animated g-bg-main text-center',
				],
			],
		],
		'#block3360' => [
			'old_id' => 3360,
			'code' => '58.2.news_sidebar_2',
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
			'dynamic' => [
				'.landing-block-card' => [
					'settings' => [
						'source' => [
							'source' => 'landing:landing',
							'sort' => [
								'by' => 'VIEWS',
								'order' => 'DESC',
							],
						],
						'pagesCount' => '4',
					],
					'references' => [
						'.landing-block-node-title@0' => [
							'id' => 'TITLE',
							'link' => 'true',
						],
						'.landing-block-node-subtitle@0' => [
							'id' => 'DESCRIPTION',
							'link' => 'true',
						],
					],
					'source' => 'landing:landing',
				],
			],
			'nodes' => [
				'.landing-block-node-title' => [
					0 => 'Jonathan Owen',
					1 => 'James Doe',
					2 => 'Albert Coolmen',
				],
				'.landing-block-node-subtitle' => [
					0 => '<p>Architects plan to stop skyscrapers from blocking out sunlight</p>',
					1 => '<p>Cooltex is one of the best technology company of our age and future</p>',
					2 => '<p>Some text goes here with plain English and much more other texts go there..</p>',
				],
			],
			'style' => [
				'.landing-block-card' => [
					0 => 'landing-block-card js-animation g-brd-bottom g-brd-gray-light-v4 g-brd-1 g-pb-1 g-mb-10 g-mb-0--last g-brd-bottom-0--last landing-card animation-none animated',
					1 => 'landing-block-card js-animation g-brd-bottom g-brd-gray-light-v4 g-brd-1 g-pb-1 g-mb-10 g-mb-0--last g-brd-bottom-0--last landing-card animation-none animated',
					2 => 'landing-block-card js-animation g-brd-bottom g-brd-gray-light-v4 g-brd-1 g-pb-1 g-mb-10 g-mb-0--last g-brd-bottom-0--last landing-card animation-none animated',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-color-gray-dark-v4 g-font-size-12 g-font-montserrat',
					1 => 'landing-block-node-title g-color-gray-dark-v4 g-font-size-12 g-font-montserrat',
					2 => 'landing-block-node-title g-color-gray-dark-v4 g-font-size-12 g-font-montserrat',
				],
				'.landing-block-node-subtitle' => [
					0 => 'landing-block-node-subtitle h6 g-font-open-sans g-color-black-opacity-0_8',
					1 => 'landing-block-node-subtitle h6 g-font-open-sans g-color-black-opacity-0_8',
					2 => 'landing-block-node-subtitle h6 g-font-open-sans g-color-black-opacity-0_8',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-15 g-pl-5 g-pr-5 u-block-border u-block-border-margin-sm g-rounded-5 u-block-border-end l-d-xs-none g-bg-main',
				],
			],
		],
	],
];