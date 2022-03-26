<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_40_8_NAME'),
		'section' => ['tiles'],
	],
	'nodes' => [
		'.landing-block-card-title-left' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_8_TITLE'),
			'type' => 'text',
		],
		'.landing-block-card-text-left' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_8_TEXT'),
			'type' => 'text',
		],
		'.landing-block-card-title-right' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_8_TITLE'),
			'type' => 'text',
		],
		'.landing-block-card-text-right' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_8_TEXT'),
			'type' => 'text',
		],
		'.landing-block-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_8_IMG'),
			'type' => 'img',
			'dimensions' => ['width' => 540],
			'create2xByDefault' => false,
		],
		'.landing-block-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_7_LINK'),
			'type' => 'link',
		],
	],
	'style' => [
		'.landing-block-card-title-left' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_8_TITLE'),
			'type' => ['typo', 'heading'],
		],
		'.landing-block-card-title-right' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_8_TITLE'),
			'type' => ['typo', 'heading'],
		],
		'.landing-block-card-text-left' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_8_TEXT'),
			'type' => 'typo',
		],
		'.landing-block-card-text-right' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_8_TEXT'),
			'type' => 'typo',
		],
		'.landing-block-text-container-right' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_8_CONTAINER'),
			'type' => ['animation', 'align-self'],
		],
		'.landing-block-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_8_CONTAINER'),
			'type' => ['animation', 'align-self'],
		],
		'.landing-block-text-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_8_CONTAINER'),
			'type' => ['align-self'],
		],
		'.landing-block-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_8_IMG'),
			'type' => ['background-overlay', 'height-vh'],
		],
		'.landing-block-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_8_LINK'),
			'type' => ['typo-link'],
		],
		'.landing-block-link-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_40_8_CONTAINER'),
			'type' => ['text-align'],
		],
	],
];