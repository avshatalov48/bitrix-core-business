<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_17.2.COPYRIGHT_WITH_SOCIAL_NAME_NEW'),
		'section' => array('footer'),
		'dynamic' => false,
	),
	'cards' => array(
		'.landing-block-card-social' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_17.2.COPYRIGHT_WITH_SOCIAL_CARDS_LANDINGBLOCKCARDSOCIAL'),
			'label' => array('.landing-block-card-social-icon'),
			'presets' => include __DIR__ . '/presets_social.php',
		),
	),
	'nodes' => array(
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_17.2.COPYRIGHT_WITH_SOCIAL_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-card-social-icon-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_17.2.COPYRIGHT_WITH_SOCIAL_NODES_LANDINGBLOCKNODESOCIALLINK'),
			'type' => 'link',
		),
		'.landing-block-card-social-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_17.2.COPYRIGHT_WITH_SOCIAL_NODES_LANDINGBLOCKNODESOCIALIMG'),
			'type' => 'icon',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default', 'animation'),
		),
		'nodes' => array(
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_17.2.COPYRIGHT_WITH_SOCIAL_STYLE_LANDINGBLOCKNODETEXT'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-card-social-icon-link' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_17.2.COPYRIGHT_WITH_SOCIAL_NODES_LANDINGBLOCKNODESOCIALLINK'),
				'type' => array('color', 'color-hover', 'background-color', 'background-hover'),
			),
		),
	
	),
);