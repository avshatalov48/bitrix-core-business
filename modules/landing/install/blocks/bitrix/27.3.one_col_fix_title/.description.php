<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_27_3_ONE_COL_FIX_TITLE_AND_TEXT_2_NAME_NEW'),
			'section' => array('title'),
		),
	'cards' =>
		array(),
	'nodes' =>
		array(
			'.landing-block-node-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_27_3_ONE_COL_FIX_TITLE_AND_TEXT_2_NODES_LANDINGBLOCKNODETITLE'),
					'type' => 'text',
				),
		),
	'style' =>
		array(
			'block' => array(
				'type' => array('block-default', 'animation'),
			),
			'nodes' => array(
				'.landing-block-node-title' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_27_3_ONE_COL_FIX_TITLE_AND_TEXT_2_STYLE_LANDINGBLOCKNODETITLE'),
						'type' => 'typo',
					),
			),
		),
);