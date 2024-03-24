<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Block;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

$canUseSearch = Loader::includeModule('search') || Block::checkComponentExists('bitrix:search.title');

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NAME_NEW'),
		'section' => ['menu'],
		'dynamic' => false,
		'type' => $canUseSearch ? 'store' : 'null',
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NODES_CARD'),
			'label' => ['.landing-block-node-card-icon', '.landing-block-node-card-title'],
			'presets' => include __DIR__ . '/presets.php',
		],
	],
	'nodes' => [
		'.landing-block-node-card-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NODES_ICON'),
			'type' => 'icon',
		],
		'.landing-block-node-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NODES_CARD_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NODES_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-card-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NODES_LINK'),
			'type' => 'link',
		],
		'bitrix:search.title' => [
			'type' => 'component',
		],
	],
	'style' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NODES_CARD'),
			'type' => 'border-colors',
		],
		'.landing-block-node-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NODES_CARD_TITLE'),
			'type' => 'typo',
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NODES_TEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-card-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NODES_LINK'),
			'type' => 'typo-link',
		],
		'.landing-block-node-card-icon-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NODES_ICON'),
			'type' => 'color',
		],
	],
];