<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_15.3.SOCIAL_NAME_2'),
		'section' => array('social'),
		'dynamic' => false,
	),
	'cards' => array(
		'.landing-block-node-list-item' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_15.3.SOCIAL_NODES_LANDINGBLOCKNODELIST_ITEM'),
			'label' => array('.landing-block-node-list-icon'),
			'presets' => include __DIR__ . '/presets.php',
		),
	),
	'nodes' => array(
		'.landing-block-node-list-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_15.3.SOCIAL_NODES_LANDINGBLOCKNODELIST_LINK'),
			'type' => 'link',
		),
		'.landing-block-node-list-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_15.3.SOCIAL_NODES_LANDINGBLOCKNODELISTIMG'),
			'type' => 'icon',
		),
	),
	'style' => array(
		'block' => [
			'type' => ['block-default', 'block-border']
		],
		'nodes' => [
			'.landing-block-node-list' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_15.3.SOCIAL_NODES_LANDINGBLOCKNODELIST'),
				'type' => array('row-align'),
			),
			'.landing-block-node-icon' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_15.3.SOCIAL_NODES_LANDINGBLOCKNODELIST'),
				'type' => ['color', 'background-color'],
			),
		],
	),
);