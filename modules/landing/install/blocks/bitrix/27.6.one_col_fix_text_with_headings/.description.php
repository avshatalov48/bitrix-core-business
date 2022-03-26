<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_27_6_ONE_COL_FIX_TEXT_HEADINGS_NAME_NEW'),
		'section' => ['text'],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_27_6_ONE_COL_FIX_TEXT_HEADINGS_NODES_LANDINGBLOCKNODE_TEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default', 'animation'],
		],
		'nodes' => [
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_27_6_ONE_COL_FIX_TEXT_HEADINGS_NODES_LANDINGBLOCKNODE_TEXT'),
				'type' => ['container', 'typo', 'padding-left', 'padding-right', 'animation'],
			],
			'.landing-block-node-text-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_27_6_ONE_COL_FIX_TEXT_HEADINGS_NODES_LANDINGBLOCKNODE_TEXT'),
				'type' => ['container', 'text-align', 'heading'],
			],
		],
	],
];