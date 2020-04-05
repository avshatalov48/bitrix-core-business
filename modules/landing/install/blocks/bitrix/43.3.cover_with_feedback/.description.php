<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NAME'),
			'section' => array('feedback'),
		),
	'cards' =>
		array(
			'.landing-block-node-card' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_CARDS_LANDINGBLOCKNODECARD'),
					'label' => array('.landing-block-node-card-photo', '.landing-block-node-card-name')
				),
		),
	'nodes' =>
		array(
			'.landing-block-node-bgimg' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODEBGIMG'),
					'type' => 'img',
					'allowInlineEdit' => false,
					'dimensions' => array('width' => 1920, 'height' => 1280),
				),
			'.landing-block-node-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODETITLE'),
					'type' => 'text',
				),
			'.landing-block-node-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODETEXT'),
					'type' => 'text',
				),
			'.landing-block-node-card-photo' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDPHOTO'),
					'type' => 'img',
					'dimensions' => array('width' => 120, 'height' => 120),
				),
			'.landing-block-node-card-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDTEXT'),
					'type' => 'text',
				),
			'.landing-block-node-card-name' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDNAME'),
					'type' => 'text',
				),
		),
	'style' =>
		array(
			'block' => array(
				'type' => array('block-default-wo-background'),
			),
			'nodes' => array(
				'.landing-block-node-title' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODETITLE'),
						'type' => 'typo',
					),
				'.landing-block-node-text' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODETEXT'),
						'type' => 'typo',
					),
				'.landing-block-node-card-text' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDTEXT'),
						'type' => 'typo',
					),
				'.landing-block-node-card-name' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDNAME'),
						'type' => 'typo',
					),
				'.landing-block-node-bgimg' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODEBGIMG'),
						'type' => 'background-overlay',
					),
				'.landing-block-node-header' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODEHEADER'),
						'type' => array('border-color','animation'),
					),
				'.landing-block-node-card-container' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_CARDS_LANDINGBLOCKNODECARD'),
						'type' => 'animation',
					),
			),
		),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);