<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_RIGHT-NAME'),
		'section' => ['tiles'],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_RIGHT-TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_RIGHT-BTN'),
			'type' => 'link',
		],
	],
	'style' => [
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_RIGHT-TEXT'),
			'type' => ['typo', 'animation'],
		],
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_RIGHT-BTN'),
			'type' => ['button'],
		],
		'.landing-block-node-button-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_52_1_BTN_AREA'),
			'type' => ['text-align', 'animation'],
		],
		'.landing-block-node-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_RIGHT_CONTAINER'),
			'type' => ['container'],
		],
	],
];