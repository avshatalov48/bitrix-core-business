<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'code' => 'store_v3/payinfo',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_V3_PAYINFO_NAME'),
	'description' => Loc::getMessage('LANDING_DEMO_STORE_V3_PAYINFO_DESC'),
	'type' => 'store',
	'version' => 3,
	'lock_delete' => true,
	'fields' => [
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/store_v3/payinfo/preview.jpg',
		],
	],
	'layout' => [
		'code' => 'without_right',
		'ref' => [
			1 => 'store_v3/header2',
			2 => 'store_v3/sidebar',
			3 => 'store_v3/footer',
		],
	],
	'items' => [
		0 => [
			'code' => '52.5.link_back',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_V3_PAYINFO_TEXT_7'),
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-bg-white l-d-xs-none l-d-md-none',
				],
			],
		],
		1 => [
			'code' => '27.3.one_col_fix_title',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '#title#',
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block text-center container g-pb-10 g-pt-0 l-d-xs-none l-d-md-none',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-my-0 container g-pl-0 g-pr-0 text-left g-font-size-30 g-font-weight-500',
				],
			],
		],
		2 => [
			'code' => '27.one_col_fix_title_and_text_2',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_V3_PAYINFO_TEXT_1'),
				],
				'.landing-block-node-text' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_V3_PAYINFO_TEXT_2'),
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-bg-white js-animation animation-none g-pt-10 g-pb-20',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title h2 text-left g-mb-15 g-font-weight-500 g-font-size-20',
				],
				'.landing-block-node-text-container' => [
					0 => 'landing-block-node-text-container container g-max-width-container',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text text-left g-font-weight-400 g-font-size-17',
				],
			],
		],
		3 => [
			'code' => 'store.salescenter.payment.pay_store_v3',
			'access' => 'W',
			'nodes' => [],
			'style' => [],
		],
		4 => [
			'code' => '27.one_col_fix_title_and_text_2',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_V3_PAYINFO_TEXT_3'),
				],
				'.landing-block-node-text' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_V3_PAYINFO_TEXT_4'),
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-bg-white js-animation animation-none g-pt-20 g-pb-20',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title h2 text-left g-mb-15 g-font-weight-500 g-font-size-20',
				],
				'.landing-block-node-text-container' => [
					0 => 'landing-block-node-text-container container g-max-width-container',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text text-left g-font-weight-400 g-font-size-17',
				],
			],
		],
		5 => [
			'code' => '27.one_col_fix_title_and_text_2',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_V3_PAYINFO_TEXT_5'),
				],
				'.landing-block-node-text' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_V3_PAYINFO_TEXT_6'),
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-bg-white js-animation animation-none g-pt-0 g-pb-0',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title h2 text-left g-mb-15 g-font-weight-500 g-font-size-20',
				],
				'.landing-block-node-text-container' => [
					0 => 'landing-block-node-text-container container g-max-width-container',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text text-left g-font-weight-400 g-font-size-17',
				],
			],
		],
		6 => [
			'code' => '33.14.form_2_light_no_text_simple',
			'cards' => [],
			'nodes' => [],
			'style' => [
				'#wrapper' => [
					0 => 'g-bg-white g-pos-rel landing-block g-color-gray-dark-v1 g-pt-0 g-pb-40',
				],
				'.landing-block-node-form-container' => [
					0 => 'landing-block-node-form-container row justify-content-start',
				],
			],
			'attrs' => [
				'.bitrix24forms' => [
					'data-b24form-design' => '{"theme":"classic-light","style":"","border":{"bottom":false,"top":false,"left":false,"right":false},"color":{"primary":"#000000ff","primaryText":"#ffffffff","text":"#000000ff","background":"#ffffffff","fieldBorder":"#00000019","fieldBackground":"#00000019","fieldFocusBackground":"#00000000","popupBackground":"#ffffffff"},"dark":false,"font":{"uri":"https://fonts.googleapis.com/css?family=Roboto","family":"Roboto","public":"https://fonts.google.com/specimen/Roboto"},"shadow":false,"compact":false,"backgroundImage":null}',
				],
			],
			'callbacks' => [
				'afterAdd' => [
					'method' => '\Bitrix\Landing\Subtype\Form::setSpecialFormToBlock',
					'params' => ['crm_preset_store_v3']
				]
			],
		],
	],
];