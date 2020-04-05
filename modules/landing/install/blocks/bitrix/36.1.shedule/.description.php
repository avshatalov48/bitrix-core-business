<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_36.1.SHEDULE_NAME'),
		'section' => array('schedule'),
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.1.SHEDULE_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-img', '.landing-block-node-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-card-time' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.1.SHEDULE_NODES_LANDINGBLOCKNODECARD_TIME'),
			'type' => 'text',
		),
		'.landing-block-node-card-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.1.SHEDULE_NODES_LANDINGBLOCKNODECARDIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 170, 'height' => 170),
			'resize_type' => BX_RESIZE_IMAGE_EXACT,
		),
		'.landing-block-node-card-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.1.SHEDULE_NODES_LANDINGBLOCKNODECARDSUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.1.SHEDULE_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.1.SHEDULE_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		),
	
	),
	'style' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.1.SHEDULE_CARDS_LANDINGBLOCKNODECARD'),
			'type' => ['bg', 'paddings', 'animation'],
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.1.SHEDULE_STYLE_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-card-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.1.SHEDULE_STYLE_LANDINGBLOCKNODECARDSUBTITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.1.SHEDULE_STYLE_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-card-time' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.1.SHEDULE_NODES_LANDINGBLOCKNODECARD_TIME'),
			'type' => ['typo', 'background-color'],
		),
		'.landing-block-node-card-time-dot' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.1.SHEDULE_NODES_LANDINGBLOCKNODECARD_TIME_DOT'),
			'type' => ['border-color', 'background-color'],
		),
		'.landing-block-node-timeline-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.1.SHEDULE_NODES_LANDINGBLOCKNODECARD_TIME_VERTICAL_LINE'),
			'type' => ['background-color-before'],
		),
		'.landing-block-node-card-time-line' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.1.SHEDULE_NODES_LANDINGBLOCKNODECARD_TIME'),
			'type' => 'border-color',
		),
		'.landing-block-node-card-img-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.1.SHEDULE_STYLE_LANDINGBLOCKNODECARDIMGCONTAINER'),
			'type' => 'background-overlay',
		),
		'.landing-block-node-card-inner' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_36.1.SHEDULE_STYLE_LANDINGBLOCKNODECARDIMGCONTAINER'),
			'type' => 'align-items',
		),
	),
);