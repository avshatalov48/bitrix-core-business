<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_FAQ_6'),
		'section' => ['about', 'text_image'],
	],
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_6_CARD'),
			'label' => ['.landing-block-node-card-title'],
		],
	],
	'nodes' => [
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_6_IMG'),
			'type' => 'img',
			'dimensions' => ['width' => 445],
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_6_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_6_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-card-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_6_ICON'),
			'type' => 'icon',
		],
		'.landing-block-node-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_6_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_6_TEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_6_TITLE'),
			'type' => ['typo', 'heading'],
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_6_TEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-card-img-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_6_ICON'),
			'type' => 'color',
		],
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_6_CARD'),
			'type' => ['bg', 'paddings', 'animation'],
		],
		'.landing-block-card--link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_6_TITLE'),
			'type' => 'border-colors',
		],
		'.landing-block-node-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_6_TITLE'),
			'type' => ['typo', 'color-hover'],
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_6_TEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-img-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_6_IMG'),
			'type' => ['text-align', 'margin-bottom']
		],
		'.landing-block-faq-icon-1' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_6_ICON'),
			'type' => 'color',
		],
	],
	'assets' => [
		'ext' => ['landing_faq'],
	],
];