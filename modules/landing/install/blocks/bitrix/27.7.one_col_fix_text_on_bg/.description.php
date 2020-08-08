<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_27_7_ONE_COL_FIX_TEXT_BG_NAME_NEW'),
		'section' => ['text'],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_27_7_ONE_COL_FIX_TEXT_BG_NODES_LANDINGBLOCKNODE_TEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default', 'animation'],
		],
		'nodes' => [
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_27_7_ONE_COL_FIX_TEXT_BG_NODES_LANDINGBLOCKNODE_TEXT'),
				'type' => ['typo', 'background-color', 'background-gradient', 'container', 'padding-top', 'padding-bottom'],
			],
		],
	],
];