<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_8_6_NAME'),
		'section' => ['text_image', 'news'],
	],
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_8_6_CARD'),
			'label' => array('.landing-block-title'),
		],
	],
	'nodes' => [
		'.landing-block-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_8_6_TITLE'),
			'type' => 'text',
		],
		'.landing-block-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_8_6_TEXT'),
			'type' => 'text',
		],
		'.landing-block-author' => [
			'name' => Loc::getMessage('LANDING_BLOCK_8_6_AUTHOR'),
			'type' => 'text',
		],
		'.landing-block-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_8_6_IMG'),
			'type' => 'img',
			'dimensions' => array('width' => 50, 'height' => 50),
		],
		'.landing-block-bottom' => [
			'name' => Loc::getMessage('LANDING_BLOCK_8_6_BLOCK_BOTTOM'),
			'type' => 'text',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default', 'animation'],
		],
		'nodes' => [
			'.landing-block-card' => [
				'name' => Loc::getMessage('LANDING_BLOCK_8_6_CARD'),
				'type' => array('animation', 'margin-bottom'),
			],
			'.landing-block-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_8_6_TITLE'),
				'type' => ['typo', 'heading'],
			],
			'.landing-block-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_8_6_TEXT'),
				'type' => ['typo'],
			],
			'.landing-block-bottom' => [
				'name' => Loc::getMessage('LANDING_BLOCK_8_6_BLOCK_BOTTOM'),
				'type' => ['typo'],
			],
			'.landing-block-author' => [
				'name' => Loc::getMessage('LANDING_BLOCK_8_6_AUTHOR'),
				'type' => ['color', 'font-size', 'font-family',
					'text-decoration', 'text-transform', 'line-height', 'letter-spacing', 'text-shadow', 'margin-bottom'],
			],
			'.landing-block-bottom-border' => [
				'name' => Loc::getMessage('LANDING_BLOCK_8_6_BORDER'),
				'type' => ['border-colors', 'border-width', 'padding-bottom'],
			],
			'.landing-block-img' => [
				'name' => Loc::getMessage('LANDING_BLOCK_8_6_IMG'),
				'type' => 'border-radius',
			],
			'.landing-block-node-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_8_6_ELEMENT'),
				'type' => ['container'],
			],
		],
	],
];