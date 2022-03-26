<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_27_4_ONE_COL_FIX_TEXT_NAME_NEW'),
		'section' => array('text', 'recommended'),
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_27_4_ONE_COL_FIX_TEXT_NODES_LANDINGBLOCKNODE_TEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default', 'animation', 'block-border'),
		),
		'nodes' => array(
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_27_4_ONE_COL_FIX_TEXT_NODES_LANDINGBLOCKNODE_TEXT'),
				'type' => ['typo', 'container', 'animation'],
			),
		),
	),
);