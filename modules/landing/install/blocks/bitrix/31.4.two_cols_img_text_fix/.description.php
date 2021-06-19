<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_31.4.TWO_COLS_TEXT_IMG_FIX_NAME'),
		'section' => ['text_image', 'recommended'],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31.4.TWO_COLS_TEXT_IMG_FIX_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31.4.TWO_COLS_TEXT_IMG_FIX_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31.4.TWO_COLS_TEXT_IMG_FIX_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => ['width' => 540],
		],
	],
	'style' => [
		'.landing-block-node-text-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31.4.TWO_COLS_TEXT_IMG_FIX_NODES_LANDINGBLOCKNODETEXT'),
			'type' => ['animation'],
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31.4.TWO_COLS_TEXT_IMG_FIX_NODES_LANDINGBLOCKNODETITLE'),
			'type' => ['typo', 'heading'],
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31.4.TWO_COLS_TEXT_IMG_FIX_NODES_LANDINGBLOCKNODETEXT'),
			'type' => ['typo'],
		],
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31.4.TWO_COLS_TEXT_IMG_FIX_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'animation',
		],
		'.landing-block-node-block' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31.4.TWO_COLS_TEXT_IMG_FIX_NODES_LANDINGBLOCKNODEBLOCK'),
			'type' => 'align-items',
		],
		'.landing-block-node-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_31.4.TWO_COLS_TEXT_IMG_FIX_NODES_LANDINGBLOCKNODE_ELEMENT'),
			'type' => ['container', 'padding-top', 'padding-bottom'],
		],
	],
];