<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'old_id' => '231',
	'code' => 'wiki/searchresult',
	'name' => Loc::getMessage("LANDING_DEMO_WIKI_SEARCHRESULTS-TITLE"),
	'description' => Loc::getMessage("LANDING_DEMO_WIKI_SEARCHRESULTS-DESCRIPTION"),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'N',
	'active' => false,
	'type' => ['knowledge', 'group'],
	'version' => 3,
	'fields' => [
		'TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_SEARCHRESULTS-TITLE"),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => '3corporate',
			'THEME_CODE_TYPO' => 'app',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'METAMAIN_USE' => 'N',
			'METAMAIN_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_SEARCHRESULTS-TITLE"),
			'METAMAIN_DESCRIPTION' => Loc::getMessage("LANDING_DEMO_WIKI_SEARCHRESULTS-DESCRIPTION"),
			'METAOG_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_SEARCHRESULTS-TITLE"),
			'METAOG_DESCRIPTION' => Loc::getMessage("LANDING_DEMO_WIKI_SEARCHRESULTS-DESCRIPTION"),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/wiki/searchresult/preview.jpg',
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
		'#block3430' => [
			'old_id' => 3430,
			'code' => '59.1.search',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-bgimage' => [
					0 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/1920x1280/img2.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
					],
				],
				'.landing-block-node-title' => [
					0 => '<span bxstyle="font-weight: 700;">How can we help you?</span>',
				],
				'.landing-block-node-text' => [
					0 => '<p>Ask a question of search any keyword</p>',
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
			'attrs' => [
				'.landing-block-node-form' => [
					0 => [
						'action' => '#landing231',
					],
				],
			],
		],
		'#block3431' => [
			'old_id' => 3431,
			'code' => '27.2.one_col_full_title',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '<span bxstyle="font-weight: bold;">Here is what we have found</span><br />',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 g-font-size-48 text-left g-font-montserrat',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pl-0 g-pb-auto g-pt-30 container g-max-width-container',
				],
			],
		],
		'#block3432' => [
			'old_id' => 3432,
			'code' => '31.5.two_cols_img_and_title_text_button',
			'access' => 'X',
			'dynamic' => [
				'.landing-block-card' => [
					'settings' => [
						'source' => [
							'source' => 'landing:landing',
							// 'filter' => array(
							// 	0 => array(
							// 		'key' => 'LANDING',
							// 		'name' => 'Страницы сайта',
							// 		'value' => array(
							// 			'VALUE' => '',
							// 		),
							// 	),
							// ),
							'sort' => [
								// todo: del comments
								// 'by' => 'VIEWS',
								'by' => 'DATE_CREATE',
								'order' => 'ASC',
							],
						],
						// 'pagesCount' => '2',
					],
					'references' => [
						// '.landing-block-node-subtitle@0' => array(
						// 	'id' => 'TITLE',
						// 	'link' => 'false',
						// ),
						// '.landing-block-node-title@0' => array(
						// 	'id' => 'DESCRIPTION',
						// 	'link' => 'false',
						// ),
						// '.landing-block-node-text@0' => '@hide',
						// '.landing-block-node-link@0' => array(
						// 	'id' => 'LINK',
						// 	'text' => '',
						// 	'action' => 'landing',
						// 	'link' => array(
						// 		'href' => '#',
						// 		'target' => '_self',
						// 	),
						// ),
						// '.landing-block-node-img@0' => array(
						// 	'id' => 'IMAGE',
						// 	'link' => 'false',
						// ),
					],
					// 'stubs' => array(
					// 	'.landing-block-node-text@0' => array(
					// 		'id' => '-1',
					// 		'src' => 'data:image/gif;base64,R0lGODlhAQABAIAAAP',
					// 		'alt' => '',
					// 	),
					// ),
					'source' => 'landing:landing',
					// 'filterId' => '4',
				],
			],
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
				'.landing-block-node-subtitle' => [
					0 => '<span bxstyle="font-weight: normal;">Alex Teseira &mdash;&nbsp; 5 June 2019</span>',
					1 => '<span bxstyle="font-weight: normal;">William Sh. &mdash;&nbsp; 1 June 2019</span>',
					2 => '<span bxstyle="font-weight: normal;">William Sh. &mdash;&nbsp; 1 June 2019</span>',
				],
				'.landing-block-node-title' => [
					0 => 'Exclusive interview with InVision&#039;s CEO',
					1 => 'We build your website to realise your vision, project and more',
					2 => 'Government plans to test new primary school of programming',
				],
				'.landing-block-node-text' => [
					0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a
						fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra
						eros, fringilla
						porttitor lorem eros vel odio.</p>
						<p>In rutrum tellus vitae blandit lacinia. Phasellus eget sapien odio. Phasellus eget sapien odio.
						Vivamus at risus quis leo tincidunt scelerisque non et erat.</p>',
					1 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a
						fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra
						eros, fringilla
						porttitor lorem eros vel odio.</p>
						<p>In rutrum tellus vitae blandit lacinia. Phasellus eget sapien odio. Phasellus eget sapien odio.
						Vivamus at risus quis leo tincidunt scelerisque non et erat.</p>',
					2 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a
						fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra
						eros, fringilla
						porttitor lorem eros vel odio.</p>
						<p>In rutrum tellus vitae blandit lacinia. Phasellus eget sapien odio. Phasellus eget sapien odio.
						Vivamus at risus quis leo tincidunt scelerisque non et erat.</p>',
				],
				'.landing-block-node-link' => [
					0 => [
						'href' => '#',
						'target' => '_self',
						'attrs' => [
							'data-embed' => null,
							'data-url' => null,
						],
						'text' => 'Read more ...',
					],
					1 => [
						'href' => '#',
						'target' => '_self',
						'attrs' => [
							'data-embed' => null,
							'data-url' => null,
						],
						'text' => 'Read more ...',
					],
					2 => [
						'href' => '#',
						'target' => '_self',
						'attrs' => [
							'data-embed' => null,
							'data-url' => null,
						],
						'text' => 'Read more ...',
					],
				],
				'.landing-block-node-img' => [
					0 => [
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/540x335/img1.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
					],
					1 => [
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/540x335/img2.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
					],
					2 => [
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/540x335/img3.jpg',
						'url' => '{"text":"","href":"","target":"_self","enabled":false}',
					],
				],
			],
			'style' => [
				'.landing-block-card' => [
					0 => 'landing-block-card row no-gutters align-items-center g-mb-30 g-mb-0--last landing-card',
					1 => 'landing-block-card row no-gutters align-items-center g-mb-30 g-mb-0--last landing-card',
					2 => 'landing-block-card row no-gutters align-items-center g-mb-30 g-mb-0--last landing-card',
				],
				'.landing-block-node-subtitle' => [
					0 => 'landing-block-node-subtitle g-font-weight-600 g-font-size-12 font-italic g-font-montserrat g-theme-event-color-gray-dark-v1',
					1 => 'landing-block-node-subtitle g-font-weight-600 g-font-size-12 font-italic g-font-montserrat g-theme-event-color-gray-dark-v1',
					2 => 'landing-block-node-subtitle g-font-weight-600 g-font-size-12 font-italic g-font-montserrat g-theme-event-color-gray-dark-v1',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title h3 g-color-black g-font-weight-600 mb-4 g-font-montserrat g-font-size-23',
					1 => 'landing-block-node-title h3 g-color-black g-font-weight-600 mb-4 g-font-montserrat g-font-size-23',
					2 => 'landing-block-node-title h3 g-color-black g-font-weight-600 mb-4 g-font-montserrat g-font-size-23',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-font-open-sans g-color-black-opacity-0_8 g-font-size-16',
					1 => 'landing-block-node-text g-font-open-sans g-color-black-opacity-0_8 g-font-size-16',
					2 => 'landing-block-node-text g-font-open-sans g-color-black-opacity-0_8 g-font-size-16',
				],
				'.landing-block-node-link' => [
					0 => 'landing-block-node-link g-font-weight-600 g-font-size-12 g-text-underline--none--hover text-uppercase g-font-open-sans g-color-primary g-color-teal--hover',
					1 => 'landing-block-node-link g-font-weight-600 g-font-size-12 g-text-underline--none--hover text-uppercase g-font-open-sans g-color-primary g-color-teal--hover',
					2 => 'landing-block-node-link g-font-weight-600 g-font-size-12 g-text-underline--none--hover text-uppercase g-font-open-sans g-color-primary g-color-teal--hover',
				],
			],
		],
	],
];