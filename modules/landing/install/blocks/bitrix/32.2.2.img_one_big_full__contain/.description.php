<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_32.2.2.IMG_ONE_BIG_FULL__NAME'),
		'section' => array('image'),
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32.2.2.IMG_ONE_BIG_FULL__NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => array('maxWidth' => 1920, 'maxHeight' => 1280),
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('display', 'bg'),
		),
		'nodes' => array(
			'.landing-block-node-img' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_32.2.2.IMG_ONE_BIG_FULL__NODES_LANDINGBLOCKNODEIMG'),
				'type' => 'background-attachment',
			),
		),
	),
);