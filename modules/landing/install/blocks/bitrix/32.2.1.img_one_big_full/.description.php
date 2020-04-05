<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_32.2.1.IMG_ONE_BIG_FULL__NAME'),
		'section' => array('image'),
		'dynamic' => false,
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32.2.1.IMG_ONE_BIG_FULL__NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1920),
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default-wo-background-vh-animation'),
		),
		'nodes' => array(
			'.landing-block-node-img' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_32.2.1.IMG_ONE_BIG_FULL__NODES_LANDINGBLOCKNODEIMG'),
				'type' => 'background-attachment',
			),
		),
	),
);