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
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_CARD'),
			'label' => ['.landing-block-card-title'],
		],
	],
	'nodes' => [
		'.landing-block-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_TITLE'),
			'type' => 'text',
		],
		'.landing-block-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_TEXT'),
			'type' => 'text',
		],
		'.landing-block-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_IMG'),
			'type' => 'img',
			'dimensions' => ['width' => 1110],
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
		'.landing-block-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_TITLE'),
			'type' => ['typo', 'border-color', 'border-width'],
		],
		'.landing-block-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_TEXT'),
			'type' => 'typo',
		],
		'.landing-block-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_IMG'),
			'type' => ['background-overlay', 'height-vh']
		],
		'.landing-block-inner' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_6_CARD'),
			'type' => 'row-align',
		],
	],
];