<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_8_7_NAME'),
		'section' => ['text'],
	],
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_8_7_CARD'),
			'label' => ['.landing-block-title'],
		],
	],
	'nodes' => [
		'.landing-block-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_8_7_TITLE'),
			'type' => 'text',
		],
		'.landing-block-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_8_7_TEXT'),
			'type' => 'text',
		],
		'.landing-block-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_8_7_ICON'),
			'type' => 'icon',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default'],
		],
		'nodes' => [
			'.landing-block-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_8_7_CARD'),
				'type' => ['container'],
			],
			'.landing-block-card' => [
				'name' => Loc::getMessage('LANDING_BLOCK_8_7_CARD'),
				'type' => ['animation', 'margin-bottom', 'container'],
			],
			'.landing-block-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_8_7_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_8_7_TEXT'),
				'type' => ['typo'],
			],
			'.landing-block-icon-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_8_7_ICON'),
				'type' => ['text-align', 'color'],
			],
		],
	],
];