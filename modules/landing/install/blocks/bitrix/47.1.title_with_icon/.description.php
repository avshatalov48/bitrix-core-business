<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_47.1.TITLE_WITH_ICON_NAME'),
		'section' => ['title'],
	],
	'cards' => [
		'.landing-block-node-icon-element' => [
			'name' => Loc::getMessage('LANDING_BLOCK_47.1.TITLE_WITH_ICON_NODES_LANDINGBLOCKNODEICON'),
			'label' => ['.landing-block-node-icon']
		],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_47.1.TITLE_WITH_ICON_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
		'.landing-block-node-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_47.1.TITLE_WITH_ICON_NODES_LANDINGBLOCKNODEICON'),
			'type' => 'icon',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_47.1.TITLE_WITH_ICON_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default', 'animation'],
		],
		'nodes' => [
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_47.1.TITLE_WITH_ICON_NODES_LANDINGBLOCKNODETITLE'),
				'type' => ['typo', 'animation', 'heading'],
			],
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_47.1.TITLE_WITH_ICON_NODES_LANDINGBLOCKNODETEXT'),
				'type' => ['typo', 'animation'],
			],
			'.landing-block-node-icon-element' => [
				'name' => Loc::getMessage('LANDING_BLOCK_47.1.TITLE_WITH_ICON_NODES_LANDINGBLOCKNODEICON'),
				'type' => ['font-size','color'],
			],
			'.landing-block-node-header' => [
				'name' => Loc::getMessage('LANDING_BLOCK_47.1.TITLE_WITH_ICON_NODES_LANDINGBLOCKNODETITLE'),
				'type' => ['margin-bottom'],
			],
			'.landing-block-node-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_47.1.TITLE_WITH_ICON_NODES_LANDINGBLOCKNODEELEMENT'),
				'type' => ['text-align', 'container', 'animation'],
			],
		],
	],
];