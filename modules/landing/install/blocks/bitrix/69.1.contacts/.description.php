<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
			'name' => Loc::getMessage('LANDING_BLOCK_69_1_NAME'),
			'section' => ['other'],
	],
	'cards' => [],
	'nodes' => [
		'bitrix:landing.blocks.crm_requisites' => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					'REQUISITE' => [],
					'HIDE_CONTACTS_DATA' => [],
					'PRIMARY_ICON' => [],
				]
			]
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default', 'container', 'block-border'],
		],
		'nodes' => [
			'.landing-block-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_69_1_ELEMENT'),
				'type' => ['container', 'color'],
			],
		],
	],
	'attrs' => [],
	'assets' => [
		'ext' => ['landing_icon_fonts'],
	],
];
