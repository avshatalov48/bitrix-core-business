<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'code' => 'store_v3/sidebar',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_SIDEBAR_NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 3,
	'fields' => [
		'RULE' => NULL,
		'ADDITIONAL_FIELDS' => [
			'BACKGROUND_USE' => 'Y',
			'BACKGROUND_COLOR' => '#ffffff',
		],
	],
	'layout' => [],
	'items' => [
		0 => [
			'code' => 'store.menu_sidebar',
			'nodes' => [
				'.landing-block-node-menu-link' => [
					0 => [
						'text' => Loc::getMessage("LANDING_DEMO_STORE_SIDEBAR_TEXT_1"),
						'href' => '#landing@landing[store_v3/mainpage]',
						'target' => '_self',
					],
				],
				'.landing-block-node-menu-link-custom' => [
					0 => [
						'text' => Loc::getMessage("LANDING_DEMO_STORE_SIDEBAR_TEXT_2"),
						'href' => '#landing@landing[store_v3/contacts]',
						'target' => '_self',
					],
					1 => [
						'text' => Loc::getMessage("LANDING_DEMO_STORE_SIDEBAR_TEXT_3"),
						'href' => '#landing@landing[store_v3/payinfo]',
						'target' => '_self',
					],
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block container landing-semantic-background-color g-bg-white l-d-xs-none l-d-md-none landing-block-node-navbar g-pt-6',
				],
			],
		]
	],
];