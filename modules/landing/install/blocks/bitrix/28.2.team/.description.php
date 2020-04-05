<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._NAME'),
			'section' => array('team'),
		),
	'cards' =>
		array(
			'.landing-block-card-employee' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._CARDS_LANDINGBLOCKCARDEMPLOYEE'),
					'label' => array('.landing-block-node-employee-photo', '.landing-block-node-employee-name')
				),
		),
	'nodes' =>
		array(
			'.landing-block-node-bgimg' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._NODES_LANDINGBLOCKNODE-BGIMG'),
					'type' => 'img',
					'dimensions' => array('width' => 1400, 'height' => 585),
				),
			'.landing-block-node-subtitle' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._NODES_LANDINGBLOCKNODESUBTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._NODES_LANDINGBLOCKNODETITLE'),
					'type' => 'text',
				),
			'.landing-block-node-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._NODES_LANDINGBLOCKNODETEXT'),
					'type' => 'text',
				),
			'.landing-block-node-employee-photo' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._NODES_LANDINGBLOCKNODEEMPLOYEEPHOTO'),
					'type' => 'img',
					'dimensions' => array('width' => 270, 'height' => 450),
				),
			'.landing-block-node-employee-quote' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._NODES_LANDINGBLOCKNODEEMPLOYEEQUOTE'),
					'type' => 'text',
				),
			'.landing-block-node-employee-post' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._NODES_LANDINGBLOCKNODEEMPLOYEEPOST'),
					'type' => 'text',
				),
			'.landing-block-node-employee-name' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._NODES_LANDINGBLOCKNODEEMPLOYEENAME'),
					'type' => 'text',
				),
			'.landing-block-node-employee-subtitle' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._NODES_LANDINGBLOCKNODEEMPLOYEESUBTITLE'),
					'type' => 'text',
				),
		),
	'style' =>
		array(
			'block' => array(
				'type' => array('block-default-wo-background'),
			),
			'nodes' => array(
				'.landing-block-card-employee' => array (
					'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._CARDS_LANDINGBLOCKCARDEMPLOYEE'),
					'type' => array('columns','animation'),
				),
				'.landing-block-node-subtitle' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._STYLE_LANDINGBLOCKNODESUBTITLE'),
						'type' => 'typo',
					),
				'.landing-block-node-title' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._STYLE_LANDINGBLOCKNODETITLE'),
						'type' => 'typo',
					),
				'.landing-block-node-text' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._STYLE_LANDINGBLOCKNODETEXT'),
						'type' => 'typo',
					),
				'.landing-block-node-employee-post' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._STYLE_LANDINGBLOCKNODEEMPLOYEEPOST'),
						'type' => 'typo',
					),
				'.landing-block-node-employee-name' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._STYLE_LANDINGBLOCKNODEEMPLOYEENAME'),
						'type' => 'typo',
					),
				'.landing-block-node-employee-subtitle' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._STYLE_LANDINGBLOCKNODEEMPLOYEESUBTITLE'),
						'type' => 'typo',
					),
				'.landing-block-node-employee-quote' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._STYLE_LANDINGBLOCKNODEEMPLOYEEQUOTE'),
						'type' => 'typo',
					),
				'.landing-block-node-bgimg' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._NODES_LANDINGBLOCKNODE-BGIMG'),
						'type' => 'background-overlay',
					),
				'.landing-block-node-header' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._STYLE_LANDINGBLOCKNODEHEADER'),
						'type' => 'border-color',
					),
			),
		),
);