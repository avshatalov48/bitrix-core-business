<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_NAME'),
		'section' => ['text', 'columns'],
	],
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_NODES_LANDINGBLOCKNODE_CARD'),
			'label' => ['.landing-block-node-title', '.landing-block-node-subtitle'],
		],
	],
	'nodes' => [
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_NODES_LANDINGBLOCKNODE_CARD'),
			'type' => ['columns', 'animation'],
		],
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_STYLE_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_STYLE_LANDINGBLOCKNODETITLE'),
			'type' => 'typo',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_STYLE_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		],
		'.landing-block-card-header' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_STYLE_LANDINGBLOCKCARDHEADER'),
			'type' => ['text-align', 'heading'],
		],
		'.landing-block-inner' => [
			'name' => Loc::getMessage('LANDING_BLOCK_5_THREE_COLS_FIX_TITLE_AND_TEXT_NODES_LANDINGBLOCKNODE_INNER'),
			'type' => 'row-align',
		],
	],
];