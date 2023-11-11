<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'code' => 'store_v3/footer',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_FOOTER_NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 3,
	'fields' => [
		'RULE' => NULL,
		'ADDITIONAL_FIELDS' => [],
	],
	'layout' => [],
	'items' => [
		'0' => [
			'code' => '55.1.list_of_links',
			'access' => 'X',
			'cards' => [
				'.landing-block-node-list-item' => [
					'source' => [
						0 => [
							'value' => 0,
							'type' => 'card',
						],
						1 => [
							'value' => 0,
							'type' => 'card',
						],
					],
				],
			],
			'nodes' => [
				'.landing-block-node-link' => [
					0 => [
						'href' => '#landing@landing[store_v3/contacts]',
						'target' => '_self',
					],
					1 => [
						'href' => '#landing@landing[store_v3/payinfo]',
						'target' => '_self',
					],
				],
				'.landing-block-node-link-text' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_FOOTER_TEXT1'),
					1 => Loc::getMessage('LANDING_DEMO_STORE_FOOTER_TEXT2'),
				],
			],
			'style' => [
				'.landing-block-node-list-container' => [
					0 => 'landing-block-node-list-container row no-gutters justify-content-center',
				],
				'.landing-block-node-list-item' => [
					0 => 'landing-block-node-list-item g-brd-bottom g-brd-1 g-py-12 js-animation animation-none landing-card g-brd-white-opacity-0_2 g-pt-12 g-pb-12',
					1 => 'landing-block-node-list-item g-brd-bottom g-brd-1 g-py-12 js-animation animation-none landing-card g-brd-white-opacity-0_2 g-pt-12 g-pb-12 g-brd-bottom--last-child',
				],
				'.landing-block-node-link' => [
					0 => 'landing-block-node-link row no-gutters justify-content-between align-items-center g-text-decoration-none--hover g-font-size-16 g-color-white-opacity-0_6 g-color-gray-light-v1--hover',
					1 => 'landing-block-node-link row no-gutters justify-content-between align-items-center g-text-decoration-none--hover g-font-size-16 g-color-white-opacity-0_6 g-color-gray-light-v1--hover',
				],
				'.landing-block-node-link-icon-container' => [
					0 => 'landing-block-node-link-icon-container d-block g-valign-middle g-mr-5 g-font-size-15 g-opacity-0_7',
					1 => 'landing-block-node-link-icon-container d-block g-valign-middle g-mr-5 g-font-size-15 g-opacity-0_7',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-20 g-pb-10 g-pl-15 g-pr-15 u-block-border-none g-bg-black l-d-lg-none l-d-md-none',
				],
			],
		],
		'1' => [
			'code' => '60.2.openlines_buttons_circle',
			'cards' => [],
			'nodes' => [],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-pt-10 g-pb-10 g-bg-black l-d-lg-none l-d-md-none',
				],
			],
			'attrs' => [],
		],
		'2' => [
			'code' => '13.1.one_col_fix_text_and_button',
			'cards' => [],
			'nodes' => [
				'.landing-block-node-text' => [
					0 => Loc::getMessage("LANDING_DEMO_STORE_FOOTER_TEXT3"),
				],
				'.landing-block-node-button' => [
					0 => [
						'href' => 'tel:#crmPhone1',
						'target' => '_self',
						'attrs' => [
							'data-embed' => NULL,
							'data-url' => NULL,],
						'text' => '#crmPhoneTitle1',
					],
				],
			],
			'style' => [
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-color-white-opacity-0_6 g-mb-10',
				],
				'.landing-block-node-button' => [
					0 => 'landing-block-node-button btn text-uppercase g-btn-size-md g-btn-px-m g-rounded-50 g-btn-type-outline g-btn-gray g-color-white',
				],
				'#wrapper' => [
					0 => 'landing-block text-center g-pt-20 g-pb-40 g-bg-black l-d-lg-none l-d-md-none',
				],
			],
			'attrs' => [],
		],
	],
];