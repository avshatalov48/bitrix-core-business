<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_69_2_NAME'),
		'section' => ['other'],
	],
	'cards' => [],
	'nodes' =>
	[
		'bitrix:landing.blocks.crm_requisites' => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					'REQUISITE' => [],
					'HIDE_REQUISITES_DATA' => [],
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
				'name' => Loc::getMessage('LANDING_BLOCK_69_2_ELEMENT'),
				'type' => ['container', 'color'],
			],
		],
	],
	'attrs' => [],
];
