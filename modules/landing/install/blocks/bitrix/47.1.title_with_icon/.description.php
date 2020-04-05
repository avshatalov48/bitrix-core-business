<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_47.1.TITLE_WITH_ICON_NAME'),
			'section' => array('title'),
		),
	'cards' =>
		array(),
	'nodes' =>
		array(
			'.landing-block-node-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_47.1.TITLE_WITH_ICON_NODES_LANDINGBLOCKNODETITLE'),
					'type' => 'text',
				),
			'.landing-block-node-icon' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_47.1.TITLE_WITH_ICON_NODES_LANDINGBLOCKNODEICON'),
					'type' => 'icon',
				),
			'.landing-block-node-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_47.1.TITLE_WITH_ICON_NODES_LANDINGBLOCKNODETEXT'),
					'type' => 'text',
				),
		),
	'style' =>
		array(
			'.landing-block-node-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_47.1.TITLE_WITH_ICON_NODES_LANDINGBLOCKNODETITLE'),
					'type' => array('typo', 'animation'),
				),
			'.landing-block-node-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_47.1.TITLE_WITH_ICON_NODES_LANDINGBLOCKNODETEXT'),
					'type' => array('typo', 'animation'),
				),
			'.landing-block-node-icon' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_47.1.TITLE_WITH_ICON_NODES_LANDINGBLOCKNODEICON'),
					'type' => 'color',
				),
		),
);