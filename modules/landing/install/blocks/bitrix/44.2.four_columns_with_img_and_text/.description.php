<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NAME'),
		'section' => ['columns', 'text_image'],
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_CARDS_LANDINGBLOCKNODECARD'),
			'label' => ['.landing-block-node-card-img', '.landing-block-node-card-title'],
		],
	],
	'nodes' => [
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],
		'.landing-block-node-card-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDIMG2'),
			'type' => 'img',
			'dimensions' => ['width' => 570],
		],
		'.landing-block-node-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDTITLE2'),
			'type' => 'text',
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDTEXT2'),
			'type' => 'text',
		],
	],
	'style' => [
		'.landing-block-node-subtitle' => [
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODETITLE'),
			'type' => ['typo', 'animation', 'heading', 'border-color', 'heading-v2', 'margin-bottom'],
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODETEXT'),
			'type' => ['typo', 'animation'],
		],
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_CARDS_LANDINGBLOCKNODECARD'),
			'type' => ['columns', 'background-color', 'animation'],
		],
		'.landing-block-node-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDTITLE2'),
			'type' => ['typo', 'heading'],
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDTEXT2'),
			'type' => 'typo',
		],
		'.landing-block-inner' => [
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_CARDS_LANDINGBLOCK_INNER'),
			'type' => 'row-align',
		],
		'.landing-block-node-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_44.2.FOUR_COLUMNS_WITH_IMG_AND_TEXT_NODES_LANDINGBLOCKNODECARDELEMENT'),
			'type' => ['container'],
		],
	],
];