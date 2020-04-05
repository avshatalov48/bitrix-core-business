<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_24.3.IMAGE_GALLERY_6_COLS_FIX_3_NAME'),
			'section' => array('partners'),
		),
	'cards' =>
		array(
			'.landing-block-node-card' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_24.3.IMAGE_GALLERY_6_COLS_FIX_3_CARDS_LANDINGBLOCKNODECARD'),
					'label' => array('.landing-block-node-img')
				),
		),
	'nodes' =>
		array(
			'.landing-block-node-img' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_24.3.IMAGE_GALLERY_6_COLS_FIX_3_NODES_LANDINGBLOCKNODEIMG'),
					'type' => 'img',
					'group' => 'logo',
					'dimensions' => array('width' => 120, 'height' => 74),
				),
			'.landing-block-card-logo-link' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_24.3.IMAGE_GALLERY_6_COLS_FIX_3_NODES_LANDINGBLOCKCARDLOGOLINK'),
					'type' => 'link',
					'group' => 'logo',
				),
		),
	'style' =>
		array(
			'block' => array(
				'type' => array('block-default', 'animation'),
			),
			'nodes' => array(
				'.landing-block-node-card' => array(
					'name' => Loc::getMessage('LANDING_BLOCK_24.3.IMAGE_GALLERY_6_COLS_FIX_3_NODES_LANDINGBLOCKNODEIMG'),
					'type' => array('columns', 'align-items')
				),
			),
		),
);