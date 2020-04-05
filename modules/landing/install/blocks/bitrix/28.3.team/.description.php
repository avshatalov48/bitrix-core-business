<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._NAME'),
		'section' => array('team'),
	),
	'cards' => array(
		'.landing-block-card-employee' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._CARDS_LANDINGBLOCKCARDEMPLOYEE'),
			'label' => array('.landing-block-node-employee-photo', '.landing-block-node-employee-name'),
		),
	),
	'nodes' => array(
		'.landing-block-node-employee-photo' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._NODES_LANDINGBLOCKNODEEMPLOYEEPHOTO'),
			'type' => 'img',
			'dimensions' => array('width' => 540),
		),
		'.landing-block-node-employee-quote' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._NODES_LANDINGBLOCKNODEEMPLOYEEQUOTE'),
			'type' => 'text',
		),
		'.landing-block-node-employee-post' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._NODES_LANDINGBLOCKNODEEMPLOYEEPOST'),
			'type' => 'text',
		),
		'.landing-block-node-employee-name' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._NODES_LANDINGBLOCKNODEEMPLOYEENAME'),
			'type' => 'text',
		),
		'.landing-block-node-employee-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._NODES_LANDINGBLOCKNODEEMPLOYEESUBTITLE'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-card-employee' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._CARDS_LANDINGBLOCKCARDEMPLOYEE'),
			'type' => array('columns', 'animation'),
		),
		'.landing-block-node-employee-post' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._STYLE_LANDINGBLOCKNODEEMPLOYEEPOST'),
			'type' => 'typo',
		),
		'.landing-block-node-employee-name' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._STYLE_LANDINGBLOCKNODEEMPLOYEENAME'),
			'type' => 'typo',
		),
		'.landing-block-node-employee-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._STYLE_LANDINGBLOCKNODEEMPLOYEESUBTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-employee-quote' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._STYLE_LANDINGBLOCKNODEEMPLOYEEQUOTE'),
			'type' => 'typo',
		),
		'.landing-block-inner' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_TEAM002._CARDS_LANDINGBLOCKNODEINNER'),
			'type' => 'row-align',
		),
	),
);