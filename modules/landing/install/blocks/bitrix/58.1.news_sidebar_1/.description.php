<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_58_1_NAME'),
		'section' => array('sidebar'),
	],
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_58_1_CARD'),
			'label' => ['.landing-block-node-title'],
		],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_58_1_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_58_1_SUBTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_58_1_IMG'),
			'type' => 'img',
			'dimensions' => array('width' => 120, 'height' => 120),
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default', 'block-border']
		],
		'nodes' => [
			'.landing-block-card' => [
				'name' => Loc::getMessage('LANDING_BLOCK_58_1_CARD'),
				'type' => ['animation', 'margin-bottom'],
			],
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_58_1_TITLE'),
				'type' => 'typo',
			],
			'.landing-block-node-subtitle' => [
				'name' => Loc::getMessage('LANDING_BLOCK_58_1_SUBTITLE'),
				'type' => 'typo',
			],
			'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_58_1_IMG'),
			'type' => 'border-radius',
			],
		],
	],
];