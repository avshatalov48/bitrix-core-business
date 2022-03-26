<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_28.PERSONAL_SLIDER_NAME'),
		'section' => array('team'),
	),
	'cards' => array(
		'.landing-block-card-person' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28.PERSONAL_SLIDER_CARDS_LANDINGBLOCKCARDPERSON'),
			'label' => array('.landing-block-node-person-photo', '.landing-block-node-person-name'),
		),
	),
	'nodes' => array(
		'.landing-block-node-person-name' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28.PERSONAL_SLIDER_NODES_LANDINGBLOCKNODEPERSONNAME'),
			'type' => 'text',
		),
		'.landing-block-node-person-photo' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28.PERSONAL_SLIDER_NODES_LANDINGBLOCKNODEPERSONPHOTO'),
			'type' => 'img',
			'dimensions' => array('width' => 370),
			'create2xByDefault' => false,
		),
		'.landing-block-node-person-post' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28.PERSONAL_SLIDER_NODES_LANDINGBLOCKNODEPERSONPOST'),
			'type' => 'text',
		),
		'.landing-block-node-person-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28.PERSONAL_SLIDER_NODES_LANDINGBLOCKNODEPERSONTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-person-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28.PERSONAL_SLIDER_NODES_LANDINGBLOCKNODEPERSONLINK'),
			'type' => 'link',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default', 'animation'),
		),
		'nodes' => array(
			'.landing-block-node-person-name' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_28.PERSONAL_SLIDER_STYLE_LANDINGBLOCKNODEPERSONNAME'),
				'type' => ['typo', 'heading'],
			),
			'.landing-block-node-person-post' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_28.PERSONAL_SLIDER_STYLE_LANDINGBLOCKNODEPERSONPOST'),
				'type' => 'typo',
			),
			'.landing-block-node-person-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_28.PERSONAL_SLIDER_STYLE_LANDINGBLOCKNODEPERSONTEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-person-link' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_28.PERSONAL_SLIDER_STYLE_LANDINGBLOCKNODEPERSONLINK'),
				'type' => 'typo-link',
			),
			'.landing-block-card-person' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_28.PERSONAL_SLIDER_CARDS_LANDINGBLOCKCARDPERSON'),
				'type' => array('align-self', 'padding-top', 'padding-bottom'),
			),
			'.landing-block-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_28.PERSONAL_SLIDER_CARDS_LANDINGBLOCKCARDPERSON'),
				'type' => 'align-items',
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);