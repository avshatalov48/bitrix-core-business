<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
//			'name' => Loc::getMessage('LANDING_BLOCK_34.2.ANIMATION_CIRCLES_NAME'),
			'section' => array('other')
		),
	'cards' =>
		array(
			'.landing-block-card-block' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_34.2.ANIMATION_CIRCLES_CARDS_LANDINGBLOCKCARDBLOCK'),
					'label' => array('.landing-block-node-block-title')
				),
		),
	'nodes' =>
		array(
			'.landing-block-node-subtitle' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_34.2.ANIMATION_CIRCLES_NODES_LANDINGBLOCKNODESUBTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_34.2.ANIMATION_CIRCLES_NODES_LANDINGBLOCKNODETITLE'),
					'type' => 'text',
				),
			'.landing-block-node-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_34.2.ANIMATION_CIRCLES_NODES_LANDINGBLOCKNODETEXT'),
					'type' => 'text',
				),
			'.landing-block-node-block-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_34.2.ANIMATION_CIRCLES_NODES_LANDINGBLOCKNODEBLOCKTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-block-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_34.2.ANIMATION_CIRCLES_NODES_LANDINGBLOCKNODEBLOCKTEXT'),
					'type' => 'text',
				),
		),
	'style' =>
		array(
			'.landing-block-node-subtitle' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_34.2.ANIMATION_CIRCLES_STYLE_LANDINGBLOCKNODESUBTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_34.2.ANIMATION_CIRCLES_STYLE_LANDINGBLOCKNODETITLE'),
					'type' => 'text',
				),
			'.landing-block-card-block' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_34.2.ANIMATION_CIRCLES_CARDS_LANDINGBLOCKCARDBLOCK'),
				'type' => 'columns'
			),
			'.landing-block-node-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_34.2.ANIMATION_CIRCLES_STYLE_LANDINGBLOCKNODETEXT'),
					'type' => 'text',
				),
			'.landing-block-node-block-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_34.2.ANIMATION_CIRCLES_STYLE_LANDINGBLOCKNODEBLOCKTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-block-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_34.2.ANIMATION_CIRCLES_STYLE_LANDINGBLOCKNODEBLOCKTEXT'),
					'type' => 'text',
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
		'ext' => 'landing_chart'
	)
);