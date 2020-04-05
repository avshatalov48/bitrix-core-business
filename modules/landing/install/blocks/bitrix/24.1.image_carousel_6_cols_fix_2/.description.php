<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_IMAGE_CAROUSEL_6_COLS_FIX_2_NAME'),
			'section' => array('partners'),
		),
	'cards' =>
		array(
			'.landing-block-card-carousel-element' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_9_IMAGE_CAROUSEL_6_COLS_FIX_2_CARDS_LANDINGBLOCKCARDCAROUSELELEMENT'),
					'label' => array('.landing-block-node-img')
				),
		),
	'nodes' =>
		array(
			'.landing-block-node-img' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_9_IMAGE_CAROUSEL_6_COLS_FIX_2_NODES_LANDINGBLOCKNODEIMG'),
					'type' => 'img',
					'group' => 'logo',
					'dimensions' => array('width' => 250, 'height' => 200),
				),
			'.landing-block-card-logo-link' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_9_IMAGE_CAROUSEL_6_COLS_FIX_2_NODES_LANDINGBLOCKCARDLOGOLINK'),
					'type' => 'link',
					'group' => 'logo',
				),
		),
	'style' =>
		array(
			'block' => array(
				'type' => array('block-default-background-overlay', 'animation'),
			),
			'nodes' => array(),
		),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
	'groups' => array(
		'logo' => Loc::getMessage('LANDING_BLOCK_9_IMAGE_CAROUSEL_6_COLS_FIX_2_NODES_LANDINGBLOCKNODEIMG')
	)
);