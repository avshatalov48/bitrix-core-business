<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;

return [
	'parent' => 'requisites',
	'code' => 'requisites/main',
	'name' => Loc::getMessage('LANDING_DEMO_PAGE_REQUISITES_NAME'),
	'description' => Loc::getMessage('LANDING_DEMO_PAGE_REQUISITES_DESCRIPTION'),
	'active' => Manager::isB24(),
	'show_in_list' => 'N',
	'type' => 'page',
	'version' => 3,
	'fields' => [
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => 'gym',
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/landing/business-card-website.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_PAGE_REQUISITES_NAME'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_PAGE_REQUISITES_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_PAGE_REQUISITES_NAME'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_PAGE_REQUISITES_DESCRIPTION'),
		],
	],
	'layout' => [],
	'items' => [
		'0' => [
			'code' => '27.one_col_fix_title_and_text_2',
			'access' => 'X',
				'nodes' => [
					'.landing-block-node-title' => [
						0 => '#requisiteCompanyTitle',
					],
					'.landing-block-node-text' => [
						0 => Loc::getMessage('LANDING_DEMO_PAGE_REQUISITES_TEXT_1'),
					],
				],
				'style' => [
					'.landing-block-node-title' => [
						0 => 'landing-block-node-title js-animation fadeIn u-heading-v2-0 g-font-size-45 text-center g-font-weight-700 g-mb-auto g-color-primary',
					],
					'.landing-block-node-text' => [
						0 => 'landing-block-node-text js-animation text-center g-font-size-20 g-mb-20',
					],
					'.landing-block-node-text-container' => [
						0 => 'landing-block-node-text-container container g-max-width-container g-pl-auto',
					],
					'#wrapper' => [
						0 => 'landing-block js-animation g-pb-30 u-block-border g-bg-white u-block-border-all u-block-border-margin-none g-mt-20 g-rounded-20 g-pt-40',
					],
				],
		],
		'1' => [
			'code' => '26.separator',
			'access' => 'X',
			'nodes' => [],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-pt-25 g-pb-25',
				],
				'.landing-block-line' => [
					0 => 'landing-block-line g-brd-gray-light-v3 my-0',
				],
			],
		],
		'2' => [
			'code' => '69.1.contacts',
			'access' => 'X',
			'nodes' => [],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-bg-white g-pt-0 g-pb-0 g-rounded-20',
				],
				'.landing-block-container' => [
					0 => 'landing-block-container g-max-width-container g-color',
				],
			],
		],
		'3' => [
			'code' => '26.separator',
			'access' => 'X',
			'nodes' => [],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-pt-25 g-pb-25',
				],
				'.landing-block-line' => [
					0 => 'landing-block-line g-brd-gray-light-v3 my-0',
				],
			],
		],
		'4' => [
			'code' => '69.2.requisites',
			'access' => 'X',
			'nodes' => [],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-bg-white g-pt-0 g-pb-0 g-rounded-20 g-mt-auto',
				],
				'.landing-block-container' => [
					0 => 'landing-block-container g-max-width-container g-color',
				],
			],
		],
		'5' => [
			'code' => '26.separator',
			'access' => 'X',
			'nodes' => [],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-pt-25 g-pb-25',
				],
				'.landing-block-line' => [
					0 => 'landing-block-line g-brd-gray-light-v3 my-0',
				],
			],
		],
		'6' => [
			'code' => '69.3.bank_requisites',
			'access' => 'X',
			'nodes' => [],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-bg-white g-pt-0 g-pb-0 u-block-border u-block-border-margin-none g-rounded-20 u-block-border-first',
				],
				'.landing-block-container' => [
					0 => 'landing-block-container g-max-width-container g-color',
				],
			],
		],
	],
];