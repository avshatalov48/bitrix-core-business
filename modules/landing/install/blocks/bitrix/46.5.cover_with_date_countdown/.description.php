<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NAME'),
		'section' => ['cover'],
		'dynamic' => false,
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],
		'.landing-block-node-date-value' => [
			'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODEDATEVALUE'),
			'type' => 'text',
		],
		'.landing-block-node-date-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODEDATETEXT'),
			'type' => 'text',
		],
		'.landing-block-node-bgimg' => [
			'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODEBGIMG'),
			'type' => 'img',
			'editInStyle' => true,
			'allowInlineEdit' => false,
			'dimensions' => ['width' => 1920, 'height' => 1080],
			'create2xByDefault' => false,
			'isWrapper' => true,
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default-background'],
		],
		'nodes' => [
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODETITLE'),
				'type' => ['typo', 'animation', 'heading'],
			],
			'.landing-block-node-subtitle-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODE_CONTAINER'),
				'type' => ['animation'],
			],
			'.landing-block-node-date-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODE_CONTAINER'),
				'type' => ['animation'],
			],
			'.landing-block-node-subtitle' => [
				'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODESUBTITLE'),
				'type' => 'typo',
			],
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODETEXT'),
				'type' => 'typo',
			],
			'.landing-block-node-date-value' => [
				'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODEDATEVALUE'),
				'type' => 'typo',
			],
			'.landing-block-node-date-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODEDATETEXT'),
				'type' => 'typo',
			],
		],

	],
];