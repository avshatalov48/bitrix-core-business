<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_58_2_NAME'),
		'section' => array('sidebar'),
	],
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_58_2_CARD'),
			'label' => ['.landing-block-node-subtitle'],
		],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_58_2_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_58_2_SUBTITLE'),
			'type' => 'text',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default', 'block-border'],
		],
		'nodes' => [
			'.landing-block-card' => [
				'name' => Loc::getMessage('LANDING_BLOCK_58_2_CARD'),
				'type' => ['animation', 'border-colors', 'border-width', 'margin-bottom'],
			],
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_58_2_TITLE'),
				'type' => ['typo', 'padding-left', 'padding-top'],
			],
			'.landing-block-node-subtitle' => [
				'name' => Loc::getMessage('LANDING_BLOCK_58_2_SUBTITLE'),
				'type' => ['typo', 'padding-left', 'padding-right'],
			],
		],
	],
];