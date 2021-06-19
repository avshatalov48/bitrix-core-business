<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NAME'),
		'section' => ['text'],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-number' => [
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-number-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_TEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'.landing-block-node-text-block' => [
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_TEXT_BLOCK'),
			'type' => ['animation', 'padding-left', 'padding-right', 'padding-top', 'padding-bottom', 'margin-bottom'],
		],
		'.landing-block-node-number-block' => [
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_TEXT_BLOCK'),
			'type' => ['animation', 'align-items'],
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_TITLE'),
			'type' => ['typo', 'heading'],
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_TEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-number' => [
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_TEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-number-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_TEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_ELEMENT'),
			'type' => ['container'],
		],
	],
];