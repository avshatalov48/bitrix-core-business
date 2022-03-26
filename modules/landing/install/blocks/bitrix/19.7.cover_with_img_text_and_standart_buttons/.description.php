<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_19_9_NAME'),
		'section' => ['tiles'],
		'dynamic' => false,
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19_9_CARD'),
			'label' => ['.landing-block-node-card-button'],
		],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19_9_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19_9_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19_9_IMG'),
			'type' => 'img',
			'dimensions' => ['width' => 540],
			'create2xByDefault' => false,
		],
		'.landing-block-node-card-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19_9_BUTTON'),
			'type' => 'link',
		],
	],
	'style' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19_9_TITLE'),
			'type' => ['typo', 'heading'],
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19_9_TEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19_9_IMG'),
			'type' => ['animation', 'padding-top', 'padding-bottom'],
		],
		'.landing-block-node-card-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19_9_BUTTON'),
			'type' => 'button',
		],
	],
];