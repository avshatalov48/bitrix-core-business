<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'code' => 'search-result',
	'type' => ['page', 'knowledge', 'group'],
	'section' => ['dynamic'],
	'name' => Loc::getMessage('LANDING_DEMO_SEARCH-RESULT-NAME'),
	'description' => Loc::getMessage('LANDING_DEMO_SEARCH-RESULT-DESCRIPTION'),
	'publication' => true,
	'version' => 3,
	'active' => false,
	'fields' => [
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_SEARCH-RESULT-NAME'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_SEARCH-RESULT-DESCRIPTION'),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/search-result/preview.jpg',
			'VIEW_USE' => 'N',
			'THEME_CODE' => '3corporate',
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
					0 => '<p>Ask a question or search any keyword</p>',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title text-uppercase g-font-weight-300 g-mb-30 g-color-white g-font-weight-700 g-font-size-46',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text form-text g-opacity-0_8 g-color-white',
				],
				'#wrapper' => [
					0 => 'landing-block landing-block-node-bgimage g-flex-centered u-bg-overlay g-bg-img-hero g-bg-darkblue-opacity-0_7--after g-mt-auto g-pb-25 g-pl-auto g-pr-auto g-pt-6 g-min-height-50vh',
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
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 g-font-size-48 text-left container g-max-width-100x g-pl-0 g-pr-0',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pl-auto g-pb-auto g-pt-30 text-center g-pl-7 g-pr-15',
				],
			],
		],
		'#block3432' => [
			'old_id' => 3432,
			'code' => '18.2.two_cols_fix_img_text_button_with_cards',
			'access' => 'X',
			'dynamic' => [
				'.landing-block-card' => [
					'settings' => [
						'source' => [
							'source' => 'landing:landing',
							'sort' => [
								'by' => 'TITLE',
								'order' => 'DESC',
							],
						],
						'pagesCount' => '6',
					],
					'references' => [
						'.landing-block-node-title@0' => array(
							'id' => 'TITLE',
							'link' => 'true',
						),
						'.landing-block-node-text@0' => array(
							'id' => 'DESCRIPTION',
							'link' => 'true',
						),
						// todo: how set link to detail?
						'.landing-block-node-link@0' => array(
							'id' => 'LINK',
							'text' => 'Read more ...',
							'action' => 'landing',
							'link' => array(
								'href' => '#',
								'target' => '_self',
							),
						),
						'.landing-block-node-img@0' => array(
							'id' => 'IMAGE',
							'link' => 'true',
						),
					],
					'source' => 'landing:landing',
				],
			],
		],
	],
];
