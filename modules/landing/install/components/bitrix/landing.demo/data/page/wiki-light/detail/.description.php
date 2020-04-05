<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'old_id' => '230',
	'code' => 'wiki-light/detail',
	//'name' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_DETAIL_TITLE"),
	'description' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_DETAIL_DESCRIPTION"),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'N',
	'type' => ['knowledge', 'group'],
	'version' => 3,
	'fields' => [
		'TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_DETAIL_TITLE"),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => '3corporate',
			'THEME_CODE_TYPO' => 'app',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'METAMAIN_USE' => 'N',
			'METAMAIN_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_DETAIL_TITLE"),
			'METAMAIN_DESCRIPTION' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_DETAIL_DESCRIPTION"),
			'METAOG_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_DETAIL_TITLE"),
			'METAOG_DESCRIPTION' => Loc::getMessage("LANDING_DEMO_WIKI_LIGHT_DETAIL_DESCRIPTION"),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/wiki-light/detail/preview.jpg',
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
		'#block3436' => [
			'old_id' => 3436,
			'code' => '27.2.one_col_full_title',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '<span style="font-weight: bold;">Kanban for tasks & projects</span>',
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pb-auto container g-max-width-container g-pt-50 g-pl-10',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 text-left g-font-size-38 g-color-black g-font-montserrat font-weight-bold g-max-width-100x',
				],
			],
		],
		'#block3437' => [
			'old_id' => 3437,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>Kanban for Projects - is a tool that will help you manage your projects!</p>',
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pt-auto g-pb-8',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 container g-pa-0 g-max-width-container text-left g-font-open-sans g-color-black-opacity-0_5 g-font-size-16 g-pl-10',
				],
			],
		],
		'#block3438' => [
			'old_id' => 3438,
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
					0 => 'Max Brickson',
				],
				'.landing-block-node-subtitle' => [
					0 => '<p>5 August 2019</p>',
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-pr-5 g-pl-auto g-bg-transparent g-pt-8 g-pb-8 g-mt-auto u-block-border g-rounded-50x u-block-border-margin-none container',
				],
				'.landing-block-card' => [
					0 => 'landing-block-card js-animation fadeIn media g-mb-0--last landing-card g-mb-auto',
				],
				'.landing-block-node-img' => [
					0 => 'landing-block-node-img g-width-60 g-height-60 g-object-fit-cover g-rounded-50x',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-letter-spacing-inherit g-line-height-2 g-font-open-sans font-weight-bold g-color-primary g-mb-auto g-font-size-16',
				],
				'.landing-block-node-subtitle' => [
					0 => 'landing-block-node-subtitle g-font-open-sans g-color-gray-dark-v4 g-font-size-12 g-letter-spacing-inherit',
				],
			],
		],
		'#block3439' => [
			'old_id' => 3439,
			'code' => '26.separator',
			'access' => 'X',
			'nodes' => [
			],
			'style' => [
			],
		],
		'#block3440' => [
			'old_id' => 3440,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>Unlike Task Planner, Kanban in Bitrix24 is created especially for projects (workgroups). Here is what you get with this tool:</p>
						<ul>
							<li>Project visualization: control flow & manage workload for each stage.</li>
							<li>Project board organization: group task cards logically the way you need. </li>
							<li>Customization for each project: create custom stages suitable for each particular project - stages number, names, colors & order are fully customizable. </li>
							<li>Convenient project navigation: scroll, drag & drop to reorder or move project tasks to a different stage. </li>
							<li>Quick actions for tasks: add new tasks, start or finish tasks, set deadlines directly inside the board - no need to open each task form. </li>
							<li>Easy switch between projects boards: special project tab above Kanban board allows to switch to another project board in one click.</li>
						</ul>',
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block container js-animation fadeInUp g-pb-9 g-pr-65 g-pl-auto g-pt-20',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 text-left g-color-main g-font-open-sans g-font-size-18 g-max-width-100x g-line-height-2',
				],
			],
		],
		'#block3441' => [
			'old_id' => 3441,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>Please note that Kanban for Projects does not take into account tasks deadlines.</p>',
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block container js-animation fadeInUp g-pr-60 g-pt-30 g-pb-30 g-pl-auto',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 text-left g-color-deeporange g-line-height-1_4 g-font-open-sans g-font-size-28',
				],
			],
		],
		'#block3442' => [
			'old_id' => 3442,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>New search & filter options are available above each Kanban board. Switch between default tasks statuses or apply filters to your board. Smart Kanban - the system will automatically show you the last project you have visited when you open Kanban view in Tasks.</p>',
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block container js-animation fadeInUp g-pl-auto g-pb-9 g-pr-65 g-pt-25',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 text-left g-color-main g-font-open-sans g-line-height-1_8 g-font-size-18',
				],
			],
		],
		'#block3443' => [
			'old_id' => 3443,
			'code' => '32.2.img_one_big',
			'access' => 'X',
			'nodes' => [
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block container g-pt-10 g-pl-6 g-pb-65',
				],
				'.landing-block-node-img' => [
					0 => 'landing-block-node-img js-animation zoomIn img-fluid',
				],
			],
		],
		'#block3444' => [
			'old_id' => 3444,
			'code' => '27.2.one_col_full_title',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '<span style="font-weight: bold;">How to use</span>',
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pb-auto container g-max-width-container g-pt-55 g-pl-auto',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 text-left g-line-height-0 g-letter-spacing-0_5 g-font-montserrat g-font-size-27 g-color-black-opacity-0_7',
				],
			],
		],
		'#block3445' => [
			'old_id' => 3445,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>Main text. Maecenas ut mauris risus. Quisque mi urna, mattis id varius nec, convallis eu odio. Integer eu malesuada leo, placerat semper neque. Nullam id volutpat dui, quis luctus magna. Suspendisse rutrum ipsum in quam semper laoreet. Praesent dictum nulla id viverra vehicula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas ac nulla vehicula risus pulvinar feugiat ullamcorper sit amet mi. Aliquam mattis neque justo, non maximus dui ornare nec. Praesent efficitur velit nisl, sed tincidunt mi imperdiet at. Cras urna libero, fringilla vitae luctus eu, egestas eget metus. Nam et massa eros. Maecenas sit amet lacinia lectus. Nam et nulla rutrum, dignissim eros quis, dictum eros. In ullamcorper molestie neque, ac faucibus felis efficitur sed. Nam et tristique nisi. Cras iaculis venenatis libero. Suspendisse fermentum, ipsum ac facilisis elementu. Praesent dictum nulla id viverra vehicula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas ac nulla vehicula risus pulvinar feugiat ullamcorper sit amet mi. Aliquam mattis neque justo, non maximus dui ornare nec. <br /></p>',
				],
			],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 g-pa-0 text-left g-font-open-sans g-color-black-opacity-0_8 g-font-size-16 g-max-width-100x',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pb-20 g-pt-20 g-pl-20 g-pr-65',
				],
			],
		],
		'#block3446' => [
			'old_id' => 3446,
			'code' => '27.4.one_col_fix_text',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-text' => [
					0 => '<p>* Additional text. Maecenas ut mauris risus. Quisque mi urna, mattis id varius nec, convallis eu odio. Integer eu malesuada leo, placerat semper neque. Nullam id volutpat dui, quis luctus magna. Suspendisse rutrum ipsum in quam semper laoreet. Praesent dictum nulla id viverra vehicula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas ac nulla vehicula risus pulvinar feugiat ullamcorper sit amet mi. Aliquam mattis neque justo, non maximus dui ornare nec. Praesent efficitur velit nisl, sed tincidunt mi imperdiet at. Cras urna libero, fringilla vitae luctus eu, egestas eget metus. Nam et massa eros. Maecenas sit amet lacinia lectus. Nam et nulla rutrum, dignissim eros quis, dictum eros. In ullamcorper molestie neque, ac faucibus felis efficitur sed. Nam et tristique nisi. Cras iaculis venenatis libero. Suspendisse fermentum, ipsum ac facilisis elementu. Praesent dictum nulla id viverra vehicula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas ac nulla vehicula risus pulvinar feugiat ullamcorper sit amet mi. Aliquam mattis neque justo, non maximus dui ornare nec. <br /></p>',
				],
			],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 g-pa-0 text-left g-font-open-sans g-font-size-14 g-color-black-opacity-0_6 g-max-width-100x',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pb-50 g-pt-auto g-pr-45 g-pl-20',
				],
			],
		],
	],
];
