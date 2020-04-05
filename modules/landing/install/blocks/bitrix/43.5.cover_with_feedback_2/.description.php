<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NAME'),
			'section' => array('feedback'),
		),
	'cards' =>
		array(
			'.landing-block-node-card' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_CARDS_LANDINGBLOCKNODECARD'),
					'label' => array('.landing-block-node-card-photo', '.landing-block-node-card-name')
				),
		),
	'nodes' =>
		array(
			'.landing-block-node-bgimg' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODEBGIMG'),
					'type' => 'img',
					'allowInlineEdit' => false,
					'dimensions' => array('width' => 1920, 'height' => 1280),
				),
			'.landing-block-node-card-photo' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDPHOTO'),
					'type' => 'img',
					'dimensions' => array('width' => 100, 'height' => 100),
				),
			'.landing-block-node-card-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDTEXT'),
					'type' => 'text',
				),
			'.landing-block-node-card-date' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARD_DATE'),
					'type' => 'text',
				),
			'.landing-block-node-card-name' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDNAME'),
					'type' => 'link',
				),
		),
	'style' =>
		array(
			'block' => array(
				'type' => array('block-default-background-overlay'),
			),
			'nodes' => array(
				'.landing-block-node-card-text' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDTEXT'),
						'type' => 'typo',
					),
				'.landing-block-node-card-date' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARD_DATE'),
						'type' => 'typo',
					),
				'.landing-block-node-card-name' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDNAME'),
						'type' => array('typo', 'color-hover'),
					),
				'.landing-block-node-bgimg' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODEBGIMG'),
						'type' => 'background-overlay',
					),
				'.landing-block-node-card-container' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_CARDS_LANDINGBLOCKNODECARD'),
						'type' => 'animation',
					),
			),
		),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);