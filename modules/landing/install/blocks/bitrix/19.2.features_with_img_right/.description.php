<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_RIGHT_NAME'),
		'section' => ['text_image', 'about'],
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_RIGHT_NODES_LANDINGBLOCKNODE_CARD'),
			'label' => ['.landing-block-node-card-icon', '.landing-block-node-card-title'],
		],
	],
	'nodes' => [
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_RIGHT_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => ['width' => 445],
			'create2xByDefault' => false,
		],
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_RIGHT_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_RIGHT_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_RIGHT_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],
		'.landing-block-node-card-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_RIGHT_NODES_LANDINGBLOCKNODECARD_ICON'),
			'type' => 'icon',
		],
		'.landing-block-node-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_RIGHT_NODES_LANDINGBLOCKNODECARD_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_RIGHT_NODES_LANDINGBLOCKNODECARD_TEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_RIGHT_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_RIGHT_NODES_LANDINGBLOCKNODETITLE'),
			'type' => ['typo', 'heading'],
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_RIGHT_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_RIGHT_NODES_LANDINGBLOCKNODECARD_TITLE'),
			'type' => 'typo',
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_RIGHT_NODES_LANDINGBLOCKNODECARD_TEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-card-icon-border' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_RIGHT_NODES_LANDINGBLOCKNODECARD_ICON'),
			'type' => ['border-color', 'color'],
		],
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_RIGHT_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'animation',
		],
		'.landing-block-node-text-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_19.2.FEATURES_WITH_IMG_RIGHT_NODES_LANDINGBLOCKNODETEXTCONTAINER'),
			'type' => 'align-items',
		],
	],
];