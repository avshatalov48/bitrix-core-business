<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_15.2.SOCIAL_NAME'),
		'section' => array('social'),
	),
	'cards' => array(
		'.landing-block-node-list-item' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_15.2.SOCIAL_NODES_LANDINGBLOCKNODELIST_ITEM'),
			'label' => array('.landing-block-node-list-icon'),
			'presets' => include __DIR__ . '/presets.php',
		),
	),
	'nodes' => array(
		'.landing-block-node-list-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_15.2.SOCIAL_NODES_LANDINGBLOCKNODELIST_LINK'),
			'type' => 'link',
		),
		'.landing-block-node-list-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_15.2.SOCIAL_NODES_LANDINGBLOCKNODELISTIMG'),
			'type' => 'icon',
		),
	),
	'style' => array(
		'.landing-block-node-list' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_15.2.SOCIAL_NODES_LANDINGBLOCKNODELIST'),
			'type' => array('row-align'),
		),
	),
);