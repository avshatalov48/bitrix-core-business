<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_40_6_NAME'),
		'section' => ['text_image', 'image'],
	],
	'nodes' => [
		'.landing-block-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_IMG'),
			'type' => 'img',
			'dimensions' => ['width' => 1110],
			'create2xByDefault' => false,
		],
		'.landing-block-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_TITLE'),
			'type' => 'text',
		],
		'.landing-block-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_TEXT'),
			'type' => 'text',
		],
		'.landing-block-img-2' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_IMG'),
			'type' => 'img',
			'dimensions' => ['width' => 1110],
			'create2xByDefault' => false,
		],
		'.landing-block-card-title-2' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_TITLE'),
			'type' => 'text',
		],
		'.landing-block-card-text-2' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_TEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_CARD'),
			'type' => ['columns', 'animation', 'padding-top', 'margin-bottom', 'align-self'],
		],
		'.landing-block-text-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_CONTAINER'),
			'type' => ['padding-left', 'padding-right', 'padding-bottom', 'padding-top'],
		],
		'.landing-block-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_IMG'),
			'type' => ['background-overlay', 'height-vh']
		],
		'.landing-block-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_TITLE'),
			'type' => ['typo', 'border-width', 'heading'],
		],
		'.landing-block-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_TEXT'),
			'type' => 'typo',
		],
		'.landing-block-img-2' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_IMG'),
			'type' => ['background-overlay', 'height-vh']
		],
		'.landing-block-card-title-2' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_TITLE'),
			'type' => ['typo', 'border-width', 'heading'],
		],
		'.landing-block-card-text-2' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_TEXT'),
			'type' => 'typo',
		],
		'.landing-block-inner' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_CARD'),
			'type' => 'row-align',
		],
		'.landing-block-node-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_CONTAINER'),
			'type' => ['container', 'padding-top', 'padding-bottom'],
		],
	],
];