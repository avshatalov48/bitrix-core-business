<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'code' => 'store_v3/contacts',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CONTACTS_NAME'),
	'description' => null,
	'type' => 'store',
	'version' => 3,
	'fields' => [
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/store_v3/contacts/preview.jpg',
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
					0 => Loc::getMessage('LANDING_DEMO_STORE_CONTACTS_TEXT7'),
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
					0 => 'landing-block-node-title landing-semantic-title-medium g-my-0 container g-pl-0 g-pr-0 text-left g-font-size-30 g-font-weight-500',
				],
			],
		],
		2 => [
			'code' => '52.3.mini_text_titile_with_btn_right',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_CONTACTS_TEXT4'),
				],
				'.landing-block-node-button' => [
					0 => [
						'text' => Loc::getMessage('LANDING_DEMO_STORE_CONTACTS_TEXT3'),
						'href' => 'tel:#crmPhone1',
						'target' => '_self',
						'attrs' => [
							'data-embed' => NULL,
							'data-url' => NULL,],
					],
				],
				'.landing-block-node-text' => [
					0 => [
						'text' => '<a href="tel:#crmPhone1">#crmPhoneTitle1</a>',
					],
				],
			],
			'style' => [
				'.landing-block-node-container' => [
					0 => 'landing-block-node-container row g-flex-start align-items-center',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title h6 g-color-gray-dark-v4 g-mb-5 text-left g-font-size-16 font-weight-normal',
				],
				'.landing-block-node-text-container' => [
					0 => 'landing-block-node-text-container text-left js-animation animation-none g-px-0 g-pr-20',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-color-gray-dark-v1 g-font-size-18',
				],
				'.landing-block-node-button-container' => [
					0 => 'landing-block-node-button-container text-right js-animation animation-none d-flex justify-content-end',
				],
				'.landing-block-node-button' => [
					0 => 'landing-block-node-button btn g-btn-type-solid g-btn-size-md g-btn-px-m g-text-transform-none g-mb-0 g-btn-black g-rounded-20 g-color-white',
				],
				'#wrapper' => [
					0 => 'landing-block g-pt-10 g-pb-20 g-bg-white container',
				],
			],
		],
		3 => [
			'code' => '27.one_col_fix_title_and_text_2',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_CONTACTS_TEXT1'),
				],
				'.landing-block-node-text' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_CONTACTS_TEXT2'),
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title h2 g-color-gray-dark-v4 g-font-size-16 text-left g-mb-5 font-weight-normal',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 g-color-gray-dark-v1 g-font-size-18 text-left',
				],
				'.landing-block-node-text-container' => [
					0 => 'landing-block-node-text-container landing-semantic-text-width container g-max-width-container',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation animation-none g-pt-10 g-pb-10 u-block-border-none g-bg-white',
				],
			],
		],
		4 => [
			'code' => '16.1.google_map',
			'access' => 'X',
			'style' => [
				'#wrapper' => [
					0 => 'landing_block g-height-1 g-min-height-50vh u-block-border-none g-bg-white g-pt-10 g-pb-10 g-pl-15 g-pr-15',
				],
			],
		],
		5 => [
			'code' => '27.one_col_fix_title_and_text_2',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_CONTACTS_TEXT5'),
				],
				'.landing-block-node-text' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_CONTACTS_TEXT6'),
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title h2 g-color-gray-dark-v4 g-font-size-16 text-left g-mb-5 font-weight-normal',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-pb-1 g-color-gray-dark-v1 g-font-size-18 text-left',
				],
				'.landing-block-node-text-container' => [
					0 => 'landing-block-node-text-container landing-semantic-text-width container g-max-width-container',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation animation-none g-pt-10 g-pb-0 u-block-border-none g-bg-white',
				],
			],
		],
		6 => [
			'code' => '33.14.form_2_light_no_text_simple',
			'cards' => [],
			'nodes' => [],
			'style' => [
				'#wrapper' => [
					0 => 'g-bg-white g-pos-rel landing-block g-color-gray-dark-v1 g-pt-10 g-pl-10 g-pb-40',
				],
				'.landing-block-node-form-container' => [
					0 => 'landing-block-node-form-container row justify-content-start',
				],
				'.bitrix24forms' => [
					0 => 'bitrix24forms u-form-alert-v4 g-brd-1 g-brd-gray-light-v4 g-brd-style-solid',
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