<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_27_3_ONE_COL_FULL_TITLE_AND_TEXT_2_NAME_NEW'),
		'section' => ['title'],
		'type' => 'null',
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_27_3_ONE_COL_FULL_TITLE_AND_TEXT_2_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default', 'animation', 'block-border'],
		],
		'nodes' => [
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_27_3_ONE_COL_FULL_TITLE_AND_TEXT_2_STYLE_LANDINGBLOCKNODETITLE'),
				'type' => ['typo', 'container'],
			],
		],
	],
];