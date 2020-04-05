<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_32.2.2.IMG_ONE_BIG_FULL__NAME2'),
		'section' => array('image', 'cover'),
		'dynamic' => false,
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32.2.2.IMG_ONE_BIG_FULL__NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1920),
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('display', 'bg', 'animation'),
		),
		'nodes' => array(
		),
	),
);