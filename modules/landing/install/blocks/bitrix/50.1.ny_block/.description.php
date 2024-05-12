<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_50.1.NEW_YEAR_NAME'),
		'section' => ['cover'],
		'dynamic' => false,
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-card-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_50.1.NEW_YEAR_NODES_LANDINGBLOCKNODECARD_IMG'),
			'type' => 'img',
			'editInStyle' => true,
			'allowInlineEdit' => false,
			'dimensions' => ['width' => 1920, 'height' => 1080],
			'create2xByDefault' => false,
		],
		'.landing-block-node-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_50.1.NEW_YEAR_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_50.1.NEW_YEAR_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		],
		'.landing-block-node-card-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_50.1.NEW_YEAR_STYLE_LANDINGBLOCKNODECARDBUTTON'),
			'type' => 'link',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default-wo-background'],
		],
		'nodes' => [
			'.landing-block-node-card-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_50.1.NEW_YEAR_STYLE_LANDINGBLOCKNODECARDTITLE'),
				'type' => ['typo', 'animation', 'heading'],
			],
			'.landing-block-node-card-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_50.1.NEW_YEAR_STYLE_LANDINGBLOCKNODECARDTEXT'),
				'type' => ['typo', 'animation'],
			],
			'.landing-block-node-card-img' => [
				'name' => Loc::getMessage('LANDING_BLOCK_50.1.NEW_YEAR_NODES_LANDINGBLOCKNODECARD_IMG'),
				'type' => ['background', 'height-vh', 'paddings'],
			],
			'.landing-block-node-card-button' => [
				'name' => Loc::getMessage('LANDING_BLOCK_50.1.NEW_YEAR_STYLE_LANDINGBLOCKNODECARDBUTTON'),
				'type' => ['background-color', 'animation'],
			],
			'.landing-block-node-card-button-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_50.1.NEW_YEAR_STYLE_LANDINGBLOCKNODECARDBUTTON'),
				'type' => ['text-align'],
			],
		],
	],
];