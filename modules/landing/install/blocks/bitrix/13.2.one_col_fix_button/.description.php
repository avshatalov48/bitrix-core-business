<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_6_ONE_COL_FIX_BUTTON_NAME'),
		'section' => array('tiles'),
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_ONE_COL_FIX_BUTTON_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => 'link',
		),
	),
	'style' => array(
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_6_ONE_COL_FIX_BUTTON_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => ['button'],
		),
	),
);