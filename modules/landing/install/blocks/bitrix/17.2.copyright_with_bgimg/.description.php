<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_17.2.COPYRIGHT_WITH_BGIMG_NAME'),
  			'section' => array('footer'),
  ),
  'cards' => 
  array(
	  '.landing-block-card-social' => array(
		  'name' => Loc::getMessage('LANDING_BLOCK_17.2.COPYRIGHT_WITH_SOCIAL_CARDS_LANDINGBLOCKCARDSOCIAL'),
		  'label' => array('.landing-block-card-social-icon'),
		  'presets' => include __DIR__ . '/presets_social.php',
	  ),
  ),
  'nodes' => 
  array(
	  '.landing-block-node-copy' =>
		  array(
			  'name' => Loc::getMessage('LANDING_BLOCK_17.2.COPYRIGHT_WITH_BGIMG_NODES_LANDINGBLOCKNODECOPY'),
			  'type' => 'text',
		  ),
	  '.landing-block-node-phone-subtitle' =>
		  array(
			  'name' => Loc::getMessage('LANDING_BLOCK_17.2.COPYRIGHT_WITH_BGIMG_NODES_LANDINGBLOCKNODEPHONESUBTITLE'),
			  'type' => 'text',
		  ),
	  '.landing-block-node-phone-link' =>
		  array(
			  'name' => Loc::getMessage('LANDING_BLOCK_17.2.COPYRIGHT_WITH_BGIMG_STYLE_LANDINGBLOCKNODEPHONELINK'),
			  'type' => 'link',
		  ),
	  '.landing-block-node-bgimg' =>
		  array(
			  'name' => Loc::getMessage('LANDING_BLOCK_17.2.COPYRIGHT_WITH_BGIMG_NODES_LANDINGBLOCKNODEBGIMG'),
			  'type' => 'img',
			  'dimensions' => array('width' => 1920, 'height' => 1280),
		  ),
	  '.landing-block-card-social-icon-link' =>
		  array(
			  'name' => Loc::getMessage('LANDING_BLOCK_17.2.COPYRIGHT_WITH_SOCIAL_NODES_LANDINGBLOCKNODESOCIALLINK'),
			  'type' => 'link',
		  ),
	  '.landing-block-card-social-icon' =>
		  array(
			  'name' => Loc::getMessage('LANDING_BLOCK_17.2.COPYRIGHT_WITH_SOCIAL_NODES_LANDINGBLOCKNODESOCIALIMG'),
			  'type' => 'icon',
		  ),
  ),
  'style' => 
  array(
  	'block' => array(
  		'type' => array('block-default-wo-background', 'animation'),
	),
	'nodes' => array(
		'.landing-block-node-copy' =>
			array(
				'name' => Loc::getMessage('LANDING_BLOCK_17.2.COPYRIGHT_WITH_BGIMG_STYLE_LANDINGBLOCKNODECOPY'),
				'type' => array('typo','animation'),
			),
		'.landing-block-node-phone-subtitle' =>
			array(
				'name' => Loc::getMessage('LANDING_BLOCK_17.2.COPYRIGHT_WITH_BGIMG_STYLE_LANDINGBLOCKNODEPHONESUBTITLE'),
				'type' => array('typo','animation'),
			),
		'.landing-block-node-bgimg' =>
			array(
				'name' => Loc::getMessage('LANDING_BLOCK_17.2.COPYRIGHT_WITH_BGIMG_NODES_LANDINGBLOCKNODEBGIMG'),
				'type' => 'background-overlay',
			),
		'.landing-block-node-phone-link' =>
			array(
				'name' => Loc::getMessage('LANDING_BLOCK_17.2.COPYRIGHT_WITH_BGIMG_STYLE_LANDINGBLOCKNODEPHONELINK'),
				'type' => array('typo','animation'),
			),
		'.landing-block-card-social-icon-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_17.2.COPYRIGHT_WITH_SOCIAL_NODES_LANDINGBLOCKNODESOCIALLINK'),
			'type' => array('color', 'color-hover', 'background-color', 'background-hover')
		),
	),
  ),
);