<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NAME'),
		'section' => array('feedback'),
		'type' => ['page', 'store', 'smn'],
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_CARDS_LANDINGBLOCKNODECARD'),
			'label' => array('.landing-block-node-card-photo', '.landing-block-node-card-name'),
		),
	),
	'nodes' => array(
		'.landing-block-node-card-photo' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDPHOTO'),
			'type' => 'img',
			'dimensions' => array('width' => 100, 'height' => 100),
			'create2xByDefault' => false,
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-date' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARD_DATE'),
			'type' => 'text',
		),
		'.landing-block-node-card-name' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDNAME'),
			'type' => 'link',
		),
		'.landing-block-node-bgimg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODEBGIMG'),
			'type' => 'img',
			'editInStyle' => true,
			'allowInlineEdit' => false,
			'dimensions' => array('width' => 1920, 'height' => 1080),
			'create2xByDefault' => false,
			'isWrapper' => true,
		),
	),
	'style' => array(
		'block' => array(
			'type' => [
				'display',
				'background',
				'padding-top',
				'padding-bottom',
				'padding-left',
				'padding-right',
				'margin-top',
			],
			'additional' => [
				'name' => Loc::getMessage('LANDING_BLOCK_43_5_COVER_WITH_FEEDBACK_NODES_SLIDER'),
				'attrsType' => ['autoplay', 'autoplay-speed', 'animation', 'pause-hover', 'slides-show', 'dots'],
			]
		),
		'nodes' => array(
			'.landing-block-node-card-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDTEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-card-date' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARD_DATE'),
				'type' => 'typo',
			),
			'.landing-block-node-card-name' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDNAME'),
				'type' => array('typo-link', 'color-hover'),
			),
			'.landing-block-node-card-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_CARDS_LANDINGBLOCKNODECARD'),
				'type' => 'animation',
			),
			'.landing-block-node-card-photo' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDPHOTO'),
				'type' => 'border-radius',
			),
			'.landing-block-node-card' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_43.5.COVER_WITH_FEEDBACK_NODES_CARD'),
				'type' => 'align-self',
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);