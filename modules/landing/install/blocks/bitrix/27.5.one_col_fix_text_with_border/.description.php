<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_27_5_ONE_COL_FIX_TEXT_BRD_NAME_NEW'),
			'section' => array('text'),
		),
	'cards' =>
		array(),
	'nodes' =>
		array(
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_27_5_ONE_COL_FIX_TEXT_BRD_NODES_LANDINGBLOCKNODE_TEXT'),
				'type' => 'text',
			),
		),
	'style' => array(
		'block' => array(
			'type' => array('block-default', 'animation'),
		),
		'nodes' => array(
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_27_5_ONE_COL_FIX_TEXT_BRD_NODES_LANDINGBLOCKNODE_TEXT'),
				'type' => array('typo', 'border-color', 'border-width'),
			),
		),
	),
);