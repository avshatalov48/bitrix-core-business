<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_31_6-NAME'),
		'section' => array('tiles', 'news'),
	],
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_6-CARD'),
			'label' => ['.landing-block-node-img', '.landing-block-node-title'],
		],
	],
	'nodes' => [
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_6-SUBTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_6-TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_6-TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_6-LINK'),
			'type' => 'link',
		],
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_6-IMAGE'),
			'type' => 'img',
			'dimensions' => ['width' => 578],
		],
	],
	'style' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_6-CARD'),
			'type' => ['bg', 'align-items', 'margin-bottom'],
		],
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_6-COLUMN'),
			'type' => 'animation',
		],
		'.landing-block-node-col-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_6-COLUMN'),
			'type' => 'animation',
		],
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_6-SUBTITLE'),
			'type' => 'typo',
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_6-TITLE'),
			'type' => ['typo', 'heading'],
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_6-TEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31_6-LINK'),
			'type' => 'typo-link',
		],
	],
];