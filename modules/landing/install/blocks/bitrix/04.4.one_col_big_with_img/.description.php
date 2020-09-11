<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_3_ONE_COL_BIG_WITH_IMG_NAME'),
		'section' => array('title'),
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_3_ONE_COL_BIG_WITH_IMG_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_3_ONE_COL_BIG_WITH_IMG_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-mainimg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_3_ONE_COL_BIG_WITH_IMG_NODES_LANDINGBLOCKNODEMAINIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1920),
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default-background-overlay', 'animation'),
		),
		'nodes' => array(
			'.landing-block-node-subtitle' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_3_ONE_COL_BIG_WITH_IMG_STYLE_LANDINGBLOCKNODESUBTITLE'),
				'type' => ['typo'],
			),
			'.landing-block-node-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_3_ONE_COL_BIG_WITH_IMG_STYLE_LANDINGBLOCKNODETITLE'),
				'type' => ['typo'],
			),
			'.landing-block-node-inner' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_3_ONE_COL_BIG_WITH_IMG_STYLE_LANDINGBLOCKNODEINNER'),
				'type' => 'border-color',
			),
		),
	),
);