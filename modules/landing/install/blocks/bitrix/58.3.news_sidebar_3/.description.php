<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_58_3-NAME'),
		'section' => ['sidebar'],
		// 'dynamic' => false,
	],
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_58_3-CARD'),
			'label' => ['.landing-block-node-title'],
		],
	],
	'nodes' => [
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_58_3-IMG'),
			'type' => 'img',
			'dimensions' => array('width' => 80, 'height' => 80),
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_58_3-TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_58_3-TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_58_3-BUTTON'),
			'type' => 'link',
		],
	],
	
	'style' => [
		'block' => [
			'type' => ['block-default', 'block-border']
		],
		'nodes' => [
			'.landing-block-card' => [
				'name' => Loc::getMessage('LANDING_BLOCK_58_3-CARD'),
				'type' => ['animation', 'margin-bottom'],
			],
			'.landing-block-node-img' => [
				'name' => Loc::getMessage('LANDING_BLOCK_58_3-IMG'),
				'type' => 'border-radius',
			],
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_58_3-TITLE'),
				'type' => 'typo',
			],
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_58_3-TEXT'),
				'type' => 'typo',
			],
			'.landing-block-node-button' => [
				'name' => Loc::getMessage('LANDING_BLOCK_58_3-BUTTON'),
				'type' => 'button',
			],
		],
	
	],
];