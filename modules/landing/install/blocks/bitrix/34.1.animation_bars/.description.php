<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
//			'name' => Loc::getMessage('LANDING_BLOCK_34.1.ANIMATION_BARS_NAME'),
		),
	'cards' =>
		array(
			'.landing-block-node-card' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_34.1.ANIMATION_BARS_NODES_LANDINGBLOCKNODECARD'),
					'label' => array('.landing-block-node-card-title')
				),
		),
	'nodes' =>
		array(
			'.landing-block-node-subtitle' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_34.1.ANIMATION_BARS_NODES_LANDINGBLOCKNODESUBTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_34.1.ANIMATION_BARS_NODES_LANDINGBLOCKNODETITLE'),
					'type' => 'text',
				),
			'.landing-block-node-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_34.1.ANIMATION_BARS_NODES_LANDINGBLOCKNODETEXT'),
					'type' => 'text',
				),
			'.landing-block-node-card-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_34.1.ANIMATION_BARS_NODES_LANDINGBLOCKNODECARD_CAPTION'),
					'type' => 'text',
				),
		),
	'style' =>
		array(
			'.landing-block-node-subtitle' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_34.1.ANIMATION_BARS_NODES_LANDINGBLOCKNODESUBTITLE'),
					'type' => 'typo',
				),
			'.landing-block-node-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_34.1.ANIMATION_BARS_NODES_LANDINGBLOCKNODETITLE'),
					'type' => 'typo',
				),
			'.landing-block-node-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_34.1.ANIMATION_BARS_NODES_LANDINGBLOCKNODETEXT'),
					'type' => 'typo',
				),
			'.landing-block-node-card-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_34.1.ANIMATION_BARS_NODES_LANDINGBLOCKNODECARD_CAPTION'),
					'type' => 'typo',
				),
		),
	'attrs' => array(
		'.landing-block-card-block-circle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_34.2.ANIMATION_CIRCLES_STYLE_LANDINGBLOCKNODEBLOCK_CIRCLE'),
			'attribute' => 'data-circles-value',
//			dbg: need input with max
			'items' => array(
				array('name' => 'desat', 'value' => 10),
				array('name' => 'semdesatvosem', 'value' => 78),
			)
		),
	),
	'assets' => array(
		'ext' => 'landing_bars'
	)
);