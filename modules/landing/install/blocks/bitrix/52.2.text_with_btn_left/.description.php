<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_LEFT-NAME'),
		'section' => ['tiles'],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_LEFT-TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_LEFT-BTN'),
			'type' => 'link',
		],
	],
	'style' => [
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_LEFT-TEXT'),
			'type' => ['typo', 'animation'],
		],
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_LEFT-BTN'),
			'type' => ['button'],
		],
		'.landing-block-node-button-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_52_2_BTN_AREA'),
			'type' => ['text-align', 'animation'],
		],
		'.landing-block-node-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_52_2_CONTAINER'),
			'type' => ['container'],
		],
	],
];