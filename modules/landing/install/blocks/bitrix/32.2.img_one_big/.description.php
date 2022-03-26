<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_32.2.IMG_ONE_BIG_NAME2'),
		'section' => array('image', 'cover', 'recommended'),
		'dynamic' => false,
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32.2.IMG_ONE_BIG_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1110),
			'create2xByDefault' => false,
		),
	),
	'style' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32.2.IMG_ONE_BIG_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'animation',
		),
	),
);