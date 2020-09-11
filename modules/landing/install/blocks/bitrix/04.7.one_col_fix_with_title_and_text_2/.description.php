<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_4_ONE_COL_FIX_WITH_TITLE_AND_TEXT_2_NAME'),
		'section' => array('title'),
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_ONE_COL_FIX_WITH_TITLE_AND_TEXT_2_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_ONE_COL_FIX_WITH_TITLE_AND_TEXT_2_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_4_ONE_COL_FIX_WITH_TITLE_AND_TEXT_2_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default', 'animation'),
		),
		'nodes' => array(
			'.landing-block-node-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_4_CONTAINER'),
				'type' => 'container',
			),
			'.landing-block-node-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_4_ONE_COL_FIX_WITH_TITLE_AND_TEXT_2_NODES_LANDINGBLOCKNODETITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-subtitle' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_4_ONE_COL_FIX_WITH_TITLE_AND_TEXT_2_STYLE_LANDINGBLOCKNODESUBTITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_4_ONE_COL_FIX_WITH_TITLE_AND_TEXT_2_STYLE_LANDINGBLOCKNODETITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-inner' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_4_ONE_COL_FIX_WITH_TITLE_AND_TEXT_2_STYLE_LANDINGBLOCKNODEINNER'),
				'type' => 'border-color',
			),
		),
	),
);