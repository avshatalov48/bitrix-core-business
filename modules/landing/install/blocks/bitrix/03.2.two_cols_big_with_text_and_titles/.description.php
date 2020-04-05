<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_2_TWO_COLS_BIG_WITH_TEXT_AND_TITLES_NAME'),
		'section' => array('columns', 'text'),
	),
	'cards' => array(
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_TWO_COLS_BIG_WITH_TEXT_AND_TITLES_NODES_LANDINGBLOCK_CARD'),
			'label' => array(
				'.landing-block-node-subtitle',
				'.landing-block-node-title',
			),
		),
	),
	'nodes' => array(
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_TWO_COLS_BIG_WITH_TEXT_AND_TITLES_NODES_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_TWO_COLS_BIG_WITH_TEXT_AND_TITLES_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_TWO_COLS_BIG_WITH_TEXT_AND_TITLES_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-inner-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_TWO_COLS_BIG_WITH_TEXT_AND_TITLES_NODES_LANDINGBLOCK_COLS'),
			'type' => array('row-align'),
		),
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_TWO_COLS_BIG_WITH_TEXT_AND_TITLES_NODES_LANDINGBLOCK_CARD'),
			'type' => array('columns', 'animation'),
		),
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_TWO_COLS_BIG_WITH_TEXT_AND_TITLES_STYLE_LANDINGBLOCKNODESUBTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_TWO_COLS_BIG_WITH_TEXT_AND_TITLES_STYLE_LANDINGBLOCKNODETITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_TWO_COLS_BIG_WITH_TEXT_AND_TITLES_STYLE_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-card-header' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_2_TWO_COLS_BIG_WITH_TEXT_AND_TITLES_STYLE_LANDINGBLOCKNODECARDHEADER'),
			'type' => 'border-color',
		),
	),
);