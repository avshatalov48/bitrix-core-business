<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'code' => 'store-chats-dark/catalog',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CHATS_CATALOG-NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 3,
	'fields' => [
		'RULE' => '(.*?)',
		'ADDITIONAL_FIELDS' => [
			'BACKGROUND_USE' => 'Y',
			'BACKGROUND_COLOR' => '#ffffff',
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/store_v3/catalog/preview.jpg',
			// 'VIEW_TYPE' => 'adaptive',
		],
	],
	'layout' => [
		'code' => 'empty',
	],
	'items' => [
		0 => [
			// todo: do block
			'code' => 'store.store_v3_menu_3',
		],
		1 => [
			'code' => '01.big_with_text_3_1',
			'nodes' => [
				'.landing-block-node-img' => [
					0 => [
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/blocks/1.jpg',
					],
				],
				'.landing-block-node-title' => [
					0 => Loc::getMessage("LANDING_DEMO_STORE_CHATS_CATALOG-TEXT_1"),
				],
				'.landing-block-node-text' => [
					0 => Loc::getMessage("LANDING_DEMO_STORE_CHATS_CATALOG-TEXT_2"),
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block landing-block-node-img u-bg-overlay g-flex-centered g-bg-img-hero g-height-auto g-pb-90 g-bg-size-cover g-pt-30 g-mb-20 g-bg-none--after l-d-xs-none l-d-md-none',
				],
				'.landing-block-node-container' => [
					0 => 'landing-block-node-container container text-center u-bg-overlay__inner g-mx-0',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title landing-semantic-title-image-medium g-line-height-1 g-mt-20 text-left g-color-black g-text-transform-none g-font-size-46 g-font-roboto g-font-weight-600 g-mb-10',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text landing-semantic-text-image-medium text-left g-color-black g-font-size-22 g-font-roboto g-font-weight-400 g-mb-0',
				],
			],
		],
		2 => [
			'code' => '27.3.one_col_fix_title',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '#title#',
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block text-center container g-pb-25 g-pt-0 l-d-xs-none l-d-md-none',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title landing-semantic-title-medium g-my-0 container g-pl-0 g-pr-0 text-left g-font-size-30 g-font-weight-500',
				],
			],
		],
		3 => [
			'code' => 'store.catalog.sections_carousel',
			'nodes' => [
				'bitrix:catalog.section' => [
					'SHOW_SECTIONS' => 'N'
				]
			],
			'style' => [
				'.landing-component' => [
					0 => 'landing-component',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-0 g-pb-0',
				],
			],
			'attrs' => [
				'bitrix:catalog.section' => [],
			],
		],
	],
];