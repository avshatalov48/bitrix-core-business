<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_67_4_NAME'),
		'section' => ['text_image'],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_67_4_TEXT_2'),
			'type' => 'text',
		],
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_67_4_IMG'),
			'type' => 'img',
			'dimensions' => ['width' => 540],
		],
	],
	'style' => [
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_67_4_TEXT_2'),
			'type' => ['typo'],
		],
		'.landing-block-node-block' => [
			'name' => Loc::getMessage('LANDING_BLOCK_67_4_BLOCK'),
			'type' => 'align-items',
		],
		'.landing-block-node-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_67_4_ELEMENTS'),
			'type' => ['container', 'padding-top', 'padding-bottom'],
		],
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_67_4_IMG'),
			'type' => 'animation',
		],
	],
];