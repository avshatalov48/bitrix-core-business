<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_45.3.GALLERY_6COLS_2ROW_NAME'),
			'section' => array('image'),
		),
	'cards' =>
		array(
			'.landing-block-node-card' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_45.3.GALLERY_6COLS_2ROW_CARDS_LANDINGBLOCKNODECARD'),
					'label' => array('.landing-block-node-card-img'),
				),
		),
	'nodes' =>
		array(
			'.landing-block-node-card-img' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_45.3.GALLERY_6COLS_2ROW_NODES_LANDINGBLOCKNODECARDIMG'),
					'type' => 'img',
					'allowInlineEdit' => false,
					'dimensions' => array('width' => 600, 'height' => 600),
				),
		),
	'style' =>
		array(
			'.landing-block-node-card' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_45.3.GALLERY_6COLS_2ROW_CARDS_LANDINGBLOCKNODECARD'),
				'type' => 'animation',
			),
		),
	'assets' => array(
		'ext' => array('landing_gallery_cards', 'landing_carousel'),
	),
);