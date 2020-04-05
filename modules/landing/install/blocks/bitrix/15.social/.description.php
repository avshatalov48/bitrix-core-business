<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
//		'name' => Loc::getMessage('LANDING_BLOCK_15.SOCIAL_NAME'),
		'section' => array('other'),
	),
	'cards' => array(
		'.landing-block-node-list-item' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_15.SOCIAL_NODES_LANDINGBLOCKNODELIST'),
			'label' => array('.landing-block-node-list-icon'),
			'presets' => include __DIR__ . '/presets.php',
		),
	),
	'nodes' => array(
		'.landing-block-node-list-item-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_15.SOCIAL_NODES_LANDINGBLOCKNODELIST_ITEM_LINK'),
			'type' => 'link',
		),
		'.landing-block-node-list-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_15.SOCIAL_NODES_LANDINGBLOCKNODELISTIMG'),
			'type' => 'icon',
		),
	),
	'style' => array(),
);