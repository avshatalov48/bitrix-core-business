<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_2_BIG_WITH_TEXT_AND_TITLE_NAME'),
		'section' => ['columns', 'text'],
	],
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_BIG_WITH_TEXT_AND_TITLE_STYLE_LANDINGBLOCKNODE_CARD'),
			'label' => [
				'.landing-block-node-subtitle',
				'.landing-block-node-title',
			],
		],
	],
	'nodes' => [
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_BIG_WITH_TEXT_AND_TITLE_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_BIG_WITH_TEXT_AND_TITLE_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_BIG_WITH_TEXT_AND_TITLE_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'.landing-block-inner-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_BIG_WITH_TEXT_AND_TITLE_STYLE_LANDINGBLOCKNODE_COLS'),
			'type' => ['row-align'],
		],
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_BIG_WITH_TEXT_AND_TITLE_STYLE_LANDINGBLOCKNODE_CARD'),
			'type' => ['text-align', 'columns', 'animation'],
		],
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_BIG_WITH_TEXT_AND_TITLE_STYLE_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_BIG_WITH_TEXT_AND_TITLE_STYLE_LANDINGBLOCKNODETITLE'),
			'type' => 'typo',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_BIG_WITH_TEXT_AND_TITLE_STYLE_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-card-header' => [
			'name' => Loc::getMessage('LANDING_BLOCK_2_BIG_WITH_TEXT_AND_TITLE_STYLE_LANDINGBLOCKNODECARDHEADER'),
			'type' => ['heading'],
		],
	],
];