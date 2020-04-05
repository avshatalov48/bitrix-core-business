<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_40_5_NAME'),
		'section' => ['text_image', 'image', 'recommended'],
	],
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_5_BLOCK'),
			'label' => ['.landing-block-card-img', '.landing-block-node-card-title'],
		],
	],
	'nodes' => [
		'.landing-block-card-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_5_IMG'),
			'type' => 'img',
			'dimensions' => ['width' => 1110],
		],
		'.landing-block-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_5_TEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'.landing-block-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_5_TEXT'),
			'type' => ['text-align', 'color', 'font-size', 'font-family', 'text-decoration', 'text-transform', 'line-height', 'letter-spacing', 'text-shadow', 'margin-top', 'margin-bottom'],
		],
		'.landing-block-border' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_5_BORDER'),
			'type' => ['border-color', 'border-width', 'margin-top', 'margin-bottom'],
		],
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_5_BLOCK'),
			'type' => ['align-self', 'animation'],
		],
		'.landing-block-card-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_5_IMG'),
			'type' => ['background-size'],
		],
	],
	'assets' => [
		'ext' => ['landing_carousel'],
	],
];