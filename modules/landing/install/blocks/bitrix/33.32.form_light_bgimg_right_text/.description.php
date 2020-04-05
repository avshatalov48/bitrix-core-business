<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_33.32.FORM_LIGHT_BGIMG_RIGHT_TEXT_NAME'),
			'section' => array('forms'),
			'subtype' => 'form',
		),
	'cards' =>
		array(),
	'nodes' =>
		array(
			'.landing-block-node-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_33.32.FORM_LIGHT_BGIMG_RIGHT_TEXT_NODES_LANDINGBLOCKNODETITLE'),
					'type' => 'text',
				),
			'.landing-block-node-subtitle' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_33.32.FORM_LIGHT_BGIMG_RIGHT_TEXT_NODES_LANDINGBLOCKNODESUBTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_33.32.FORM_LIGHT_BGIMG_RIGHT_TEXT_NODES_LANDINGBLOCKNODETEXT'),
					'type' => 'text',
				),
			'.landing-block-node-button' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_33.32.FORM_LIGHT_BGIMG_RIGHT_TEXT_NODES_LANDINGBLOCKNODE_BUTTON'),
					'type' => 'link',
				),
			'.landing-block-node-bgimg' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_33.32.FORM_LIGHT_BGIMG_RIGHT_TEXT_NODES_LANDINGBLOCKNODE_BGIMG'),
					'type' => 'img',
					'dimensions' => array('width' => 1920, 'height' => 1440),
					'allowInlineEdit' => false,
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
						'name' => Loc::getMessage('LANDING_BLOCK_33.32.FORM_LIGHT_BGIMG_RIGHT_TEXT_NODES_LANDINGBLOCKNODETITLE'),
						'type' => array('typo','animation'),
					),
				'.landing-block-node-subtitle' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_33.32.FORM_LIGHT_BGIMG_RIGHT_TEXT_NODES_LANDINGBLOCKNODESUBTITLE'),
						'type' => 'typo',
					),
				'.landing-block-node-text' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_33.32.FORM_LIGHT_BGIMG_RIGHT_TEXT_NODES_LANDINGBLOCKNODETEXT'),
						'type' => array('typo','animation'),
					),
				'.landing-block-node-button' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_33.32.FORM_LIGHT_BGIMG_RIGHT_TEXT_NODES_LANDINGBLOCKNODE_BUTTON'),
						'type' => array('button','animation'),
					),
				'.landing-block-node-bgimg' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_33.32.FORM_LIGHT_BGIMG_RIGHT_TEXT_NODES_LANDINGBLOCKNODE_BGIMG'),
						'type' => array('background-overlay', 'background-attachment')
					),
				'.landing-block-node-form' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_33.32.FORM_LIGHT_BGIMG_RIGHT_TEXT_NODES_LANDINGBLOCKNODE_FORM'),
						'type' => 'animation',
					),
			),
		),
	'assets' => array(
		'ext' => array('landing_form'),
	),
);