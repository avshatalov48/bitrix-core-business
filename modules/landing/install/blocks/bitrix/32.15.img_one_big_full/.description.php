<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_32_15_NAME'),
		'section' => ['tiles', 'image'],
	],
	'nodes' => [
		'.landing-block-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_32_15_IMG'),
			'type' => 'img',
			'dimensions' => ['width' => 1110],
		],
		'.landing-block-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_32_15_TITLE'),
			'type' => 'text',
		],
		'.landing-block-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_32_15_LINK'),
			'type' => 'link',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default-wo-background', 'animation', 'background-color', 'background-gradient'],
		],
		'nodes' => [
			'.landing-block-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_32_15_BLOCK'),
				'type' => ['container']
			],
			'.landing-block-img' => [
				'name' => Loc::getMessage('LANDING_BLOCK_32_15_IMG'),
				'type' => ['background-overlay', 'height-vh']
			],
			'.landing-block-text-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_32_15_ELEMENTS'),
				'type' => ['padding-left', 'padding-right', 'padding-bottom', 'padding-top'],
			],
			'.landing-block-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_32_15_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-link' => [
				'name' => Loc::getMessage('LANDING_BLOCK_32_15_LINK'),
				'type' => ['typo-link']
			],
			'.landing-block-link-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_32_15_ELEMENTS'),
				'type' => ['text-align']
			],
		],
	],
];