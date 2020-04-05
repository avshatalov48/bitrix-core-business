<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NAME_NEW'),
		'section' => array('menu'),
		'type' => 'store',
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NODES_CARD'),
			'label' => array('.landing-block-node-card-icon', '.landing-block-node-card-title'),
			'presets' => include __DIR__ . '/presets.php',
		),
	),
	'nodes' => array(
		'.landing-block-node-card-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NODES_ICON'),
			'type' => 'icon',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NODES_CARD_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NODES_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NODES_LINK'),
			'type' => 'link',
		),
		'bitrix:search.title' => array(
			'type' => 'component',
		),
	),
	'style' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NODES_CARD'),
			'type' => 'border-color',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NODES_CARD_TITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NODES_TEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-card-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NODES_LINK'),
			'type' => 'typo-link',
		),
		'.landing-block-node-card-icon-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35_6_HEADER--NODES_ICON'),
			'type' => 'color',
		),
	),
	'assets' => array(
		'js' => array(
			'/bitrix/components/bitrix/search.title/script.js',
		),
		'css' => array(
			'/bitrix/components/bitrix/search.title/templates/bootstrap_v4/style.css',
		),
	),
);